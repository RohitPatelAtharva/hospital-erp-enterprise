<?php

declare(strict_types=1);

namespace App\Audit;

/**
 * Guards against PHI/secrets being written to audit records
 * (docs/modules/master-data/13-Audit.md §19, 12-Security §7).
 *
 * Sensitive values are redacted rather than dropped entirely so the event shape
 * remains stable for downstream consumers.
 */
final class AuditSanitizer
{
    private const SENSITIVE_KEYS = [
        'password', 'passwd', 'secret', 'token', 'authorization',
        'ssn', 'national_id', 'identifier_value', 'medical', 'diagnosis',
        'date_of_birth', 'dob', 'contact_value', 'email', 'phone',
    ];

    public static function sanitize(array $context): array
    {
        return self::walk($context);
    }

    private static function walk(mixed $value): mixed
    {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $key => $item) {
                $out[$key] = self::isSensitiveKey((string) $key)
                    ? '[REDACTED]'
                    : self::walk($item);
            }

            return $out;
        }

        return $value;
    }

    private static function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower(str_replace(['-', ' '], '_', $key));

        foreach (self::SENSITIVE_KEYS as $sensitive) {
            if (str_contains($normalized, $sensitive)) {
                return true;
            }
        }

        return false;
    }
}
