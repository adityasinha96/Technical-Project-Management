<?php

namespace App\Services\Audit;

use App\Enums\AuditCategory;
use App\Enums\AuditSeverity;
use App\Models\AuditChainHead;
use App\Models\AuditLog;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AuditLogService
{
    public function __construct(
        private readonly AuditValueSanitizer $sanitizer
    ) {
    }

    public function record(
        string $eventType,
        AuditCategory $category =
            AuditCategory::System,
        AuditSeverity $severity =
            AuditSeverity::Info,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
        ?Model $actor = null,
        ?string $guard = null
    ): AuditLog {
        return DB::transaction(
            function () use (
                $eventType,
                $category,
                $severity,
                $auditable,
                $oldValues,
                $newValues,
                $metadata,
                $actor,
                $guard
            ): AuditLog {
                $head = AuditChainHead::query()
                    ->lockForUpdate()
                    ->findOrFail(1);

                [$resolvedActor, $resolvedGuard] =
                    $this->resolveActor(
                        $actor,
                        $guard
                    );

                $requestContext =
                    $this->requestContext();

                $sequence =
                    (int) $head->last_sequence + 1;

                $uuid =
                    (string) Str::uuid();

                /*
                 * The audit timestamp is deliberately normalised to a whole
                 * second. SQLite and MySQL configurations commonly persist
                 * timestamps without microseconds. Hashing a pre-insert value
                 * containing microseconds would therefore differ from the
                 * persisted value later reconstructed by the verifier.
                 */
                $occurredAt =
                    now()
                        ->utc()
                        ->startOfSecond();

                $sanitizedOld =
                    $this->sanitizer
                        ->sanitize(
                            $oldValues
                        );

                $sanitizedNew =
                    $this->sanitizer
                        ->sanitize(
                            $newValues
                        );

                $sanitizedMetadata =
                    $this->sanitizer
                        ->sanitize(
                            $metadata
                        );

                $payload = [
                    'audit_uuid' => $uuid,
                    'sequence' => $sequence,
                    'event_type' => $eventType,
                    'category' => $category->value,
                    'severity' => $severity->value,

                    'actor_type' =>
                        $resolvedActor
                            ?->getMorphClass(),

                    'actor_id' =>
                        $resolvedActor
                            ?->getKey(),

                    'auditable_type' =>
                        $auditable
                            ?->getMorphClass(),

                    'auditable_id' =>
                        $auditable
                            ?->getKey(),

                    'actor_name' =>
                        $resolvedActor?->name,

                    'actor_email' =>
                        $resolvedActor?->email,

                    'guard' =>
                        $resolvedGuard,

                    ...$requestContext,

                    'old_values' =>
                        $sanitizedOld,

                    'new_values' =>
                        $sanitizedNew,

                    'metadata' =>
                        $sanitizedMetadata,

                    'previous_hash' =>
                        (string) $head->last_hash,

                    'occurred_at' =>
                        $this->canonicalTimestamp(
                            $occurredAt
                        ),
                ];

                /*
                * Insert the entry first so the hash is calculated from
                * the exact values persisted by the database.
                */
                $auditLog =
                    AuditLog::query()->create([
                        ...$payload,

                        'occurred_at' =>
                            $occurredAt,

                        'entry_hash' =>
                            str_repeat('0', 64),
                    ]);

                $auditLog->refresh();

                $entryHash =
                    $this->hashPayload(
                        $this->payloadFromLog(
                            $auditLog
                        )
                    );

                /*
                * Raw query is intentional because AuditLog model
                * correctly prevents normal updates.
                */
                DB::table(
                    $auditLog->getTable()
                )
                    ->where(
                        'id',
                        $auditLog->getKey()
                    )
                    ->update([
                        'entry_hash' =>
                            $entryHash,
                    ]);

                $auditLog->forceFill([
                    'entry_hash' =>
                        $entryHash,
                ]);

                $auditLog->syncOriginalAttribute(
                    'entry_hash'
                );

                $head->update([
                    'last_sequence' =>
                        $sequence,

                    'last_hash' =>
                        $entryHash,
                ]);

                return $auditLog;
            },
            attempts: 5
        );
    }

    /**
     * Rebuild the exact canonical payload from a persisted audit entry.
     *
     * Both the writer and integrity verifier use the same field structure,
     * enum normalisation, timestamp representation and JSON canonicalisation.
     */
    public function payloadFromLog(
        AuditLog $log
    ): array {
        return [
            'audit_uuid' =>
                (string) $log->audit_uuid,

            'sequence' =>
                (int) $log->sequence,

            'event_type' =>
                (string) $log->event_type,

            'category' =>
                $this->enumValue(
                    $log->category
                ),

            'severity' =>
                $this->enumValue(
                    $log->severity
                ),

            'actor_type' =>
                $log->actor_type,

            'actor_id' =>
                $log->actor_id,

            'auditable_type' =>
                $log->auditable_type,

            'auditable_id' =>
                $log->auditable_id,

            'actor_name' =>
                $log->actor_name,

            'actor_email' =>
                $log->actor_email,

            'guard' =>
                $log->guard,

            'route_name' =>
                $log->route_name,

            'request_method' =>
                $log->request_method,

            'request_path' =>
                $log->request_path,

            'ip_address' =>
                $log->ip_address,

            'user_agent' =>
                $log->user_agent,

            'session_id_hash' =>
                $log->session_id_hash,

            'old_values' =>
                $log->old_values
                ?? [],

            'new_values' =>
                $log->new_values
                ?? [],

            'metadata' =>
                $log->metadata
                ?? [],

            'previous_hash' =>
                (string) $log->previous_hash,

            'occurred_at' =>
                $this->canonicalTimestamp(
                    $log->occurred_at
                ),
        ];
    }

    public function hashPayload(
        array $payload
    ): string {
        return hash_hmac(
            'sha256',
            $this->canonicalJson(
                $payload
            ),
            $this->hmacKey()
        );
    }

    public function canonicalJson(
        array $payload
    ): string {
        return json_encode(
            $this->sortRecursively(
                $payload
            ),
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_THROW_ON_ERROR
        );
    }

    public function canonicalTimestamp(
        CarbonInterface $timestamp
    ): string {
        return $timestamp
            ->copy()
            ->utc()
            ->format(
                'Y-m-d\TH:i:s.u\Z'
            );
    }

    public function hmacKey(): string
    {
        $key = (string) config(
            'security.audit.hmac_key'
        );

        if ($key === '') {
            throw new RuntimeException(
                'AUDIT_LOG_HMAC_KEY is not configured.'
            );
        }

        if (
            str_starts_with(
                $key,
                'base64:'
            )
        ) {
            $decoded = base64_decode(
                substr($key, 7),
                true
            );

            if ($decoded === false) {
                throw new RuntimeException(
                    'AUDIT_LOG_HMAC_KEY is invalid.'
                );
            }

            return $decoded;
        }

        return $key;
    }

    private function resolveActor(
        ?Model $actor,
        ?string $guard
    ): array {
        if ($actor) {
            return [$actor, $guard];
        }

        if (Auth::guard('web')->check()) {
            return [
                Auth::guard('web')->user(),
                'web',
            ];
        }

        if (Auth::guard('client')->check()) {
            return [
                Auth::guard('client')->user(),
                'client',
            ];
        }

        return [null, $guard];
    }

    private function requestContext(): array
    {
        if (
            app()->runningInConsole()
            || !app()->bound('request')
        ) {
            return [
                'route_name' => null,
                'request_method' => null,
                'request_path' => null,
                'ip_address' => null,
                'user_agent' => null,
                'session_id_hash' => null,
            ];
        }

        $request = request();

        $sessionHash = null;

        if ($request->hasSession()) {
            $sessionHash = hash(
                'sha256',
                $request
                    ->session()
                    ->getId()
            );
        }

        return [
            'route_name' =>
                $request->route()?->getName(),

            'request_method' =>
                $request->method(),

            'request_path' =>
                $request->path(),

            'ip_address' =>
                $request->ip(),

            'user_agent' =>
                str(
                    (string)
                    $request->userAgent()
                )
                    ->limit(2000)
                    ->toString(),

            'session_id_hash' =>
                $sessionHash,
        ];
    }

    private function enumValue(
        mixed $value
    ): mixed {
        return $value instanceof BackedEnum
            ? $value->value
            : $value;
    }

    private function sortRecursively(
        mixed $value
    ): mixed {
        if (!is_array($value)) {
            return $value;
        }

        if (
            array_is_list(
                $value
            )
        ) {
            return array_map(
                fn ($item) =>
                    $this->sortRecursively(
                        $item
                    ),
                $value
            );
        }

        ksort($value);

        foreach ($value as $key => $item) {
            $value[$key] =
                $this->sortRecursively(
                    $item
                );
        }

        return $value;
    }
}

