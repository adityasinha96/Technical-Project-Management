<?php

namespace App\Traits;

use App\Enums\AuditCategory;
use App\Enums\AuditSeverity;
use App\Services\Audit\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

trait AuditsSystemChanges
{
    private array $systemAuditBefore = [];

    protected static function bootAuditsSystemChanges(): void
    {
        static::created(
            function (Model $model): void {
                app(
                    AuditLogService::class
                )->record(
                    eventType:
                        $model->auditEventName(
                            'created'
                        ),

                    category:
                        AuditCategory::DataChange,

                    severity:
                        $model
                            ->auditSeverityFor(
                                'created'
                            ),

                    auditable: $model,

                    newValues:
                        $model
                            ->auditAttributes(
                                $model
                                    ->getAttributes()
                            )
                );
            }
        );

        static::updating(
            function (Model $model): void {
                $dirtyKeys =
                    array_keys(
                        $model->getDirty()
                    );

                $model->systemAuditBefore =
                    collect($dirtyKeys)
                        ->mapWithKeys(
                            fn (string $key) => [
                                $key =>
                                    $model
                                        ->getOriginal(
                                            $key
                                        ),
                            ]
                        )
                        ->all();
            }
        );

        static::updated(
            function (Model $model): void {
                $changes =
                    $model->getChanges();

                if ($changes === []) {
                    return;
                }

                app(
                    AuditLogService::class
                )->record(
                    eventType:
                        $model->auditEventName(
                            'updated'
                        ),

                    category:
                        AuditCategory::DataChange,

                    severity:
                        $model
                            ->auditSeverityFor(
                                'updated'
                            ),

                    auditable: $model,

                    oldValues:
                        $model
                            ->auditAttributes(
                                $model
                                    ->systemAuditBefore
                            ),

                    newValues:
                        $model
                            ->auditAttributes(
                                $changes
                            )
                );

                $model->systemAuditBefore = [];
            }
        );

        static::deleted(
            function (Model $model): void {
                app(
                    AuditLogService::class
                )->record(
                    eventType:
                        $model->auditEventName(
                            'deleted'
                        ),

                    category:
                        AuditCategory::DataChange,

                    severity:
                        AuditSeverity::High,

                    auditable: $model,

                    oldValues:
                        $model
                            ->auditAttributes(
                                $model
                                    ->getAttributes()
                            )
                );
            }
        );

        if (
            in_array(
                SoftDeletes::class,
                class_uses_recursive(
                    static::class
                ),
                true
            )
        ) {
            static::restored(
                function (Model $model): void {
                    app(
                        AuditLogService::class
                    )->record(
                        eventType:
                            $model
                                ->auditEventName(
                                    'restored'
                                ),

                        category:
                            AuditCategory::DataChange,

                        severity:
                            AuditSeverity::High,

                        auditable:
                            $model,

                        newValues:
                            $model
                                ->auditAttributes(
                                    $model
                                        ->getAttributes()
                                )
                    );
                }
            );
        }
    }

    public function auditEventName(
        string $action
    ): string {
        return str(class_basename($this))
            ->snake()
            ->append(".{$action}")
            ->toString();
    }

    public function auditSeverityFor(
        string $action
    ): AuditSeverity {
        return match ($action) {
            'deleted',
            'restored' =>
                AuditSeverity::High,

            default =>
                AuditSeverity::Info,
        };
    }

    public function auditAttributes(
        array $attributes
    ): array {
        $include =
            property_exists(
                $this,
                'auditInclude'
            )
                ? $this->auditInclude
                : [];

        $exclude =
            property_exists(
                $this,
                'auditExclude'
            )
                ? $this->auditExclude
                : [];

        if ($include !== []) {
            $attributes =
                array_intersect_key(
                    $attributes,
                    array_flip(
                        $include
                    )
                );
        }

        if ($exclude !== []) {
            $attributes =
                array_diff_key(
                    $attributes,
                    array_flip(
                        $exclude
                    )
                );
        }

        return $attributes;
    }
}