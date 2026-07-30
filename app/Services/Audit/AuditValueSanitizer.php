<?php

namespace App\Services\Audit;

use BackedEnum;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;

class AuditValueSanitizer
{
    public function sanitize(
        array $values
    ): array {
        $sensitiveFields = collect(
            config(
                'security.audit.sensitive_fields',
                []
            )
        )
            ->map(
                fn (string $field) =>
                    strtolower($field)
            )
            ->all();

        $ignoredFields = collect(
            config(
                'security.audit.ignored_fields',
                []
            )
        )
            ->map(
                fn (string $field) =>
                    strtolower($field)
            )
            ->all();

        $sanitized = [];

        foreach ($values as $key => $value) {
            $normalisedKey =
                strtolower((string) $key);

            if (
                in_array(
                    $normalisedKey,
                    $ignoredFields,
                    true
                )
            ) {
                continue;
            }

            if (
                in_array(
                    $normalisedKey,
                    $sensitiveFields,
                    true
                )
                || str_contains(
                    $normalisedKey,
                    'password'
                )
                || str_contains(
                    $normalisedKey,
                    'secret'
                )
                || str_contains(
                    $normalisedKey,
                    'token'
                )
            ) {
                $sanitized[$key] =
                    '[REDACTED]';

                continue;
            }

            $sanitized[$key] =
                $this->normaliseValue(
                    $value
                );
        }

        return $sanitized;
    }

    private function normaliseValue(
        mixed $value
    ): mixed {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(
                DATE_ATOM
            );
        }

        if ($value instanceof Arrayable) {
            return $this->sanitize(
                $value->toArray()
            );
        }

        if (is_array($value)) {
            return $this->sanitize(
                $value
            );
        }

        if (is_object($value)) {
            return method_exists(
                $value,
                '__toString'
            )
                ? (string) $value
                : $value::class;
        }

        if (is_string($value)) {
            return str($value)
                ->limit(10000)
                ->toString();
        }

        return $value;
    }
}