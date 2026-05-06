<?php

namespace App\Support;

class LegacyModuleGuard
{
    /**
     * Accept only simple slug names used by legacy modules.
     */
    public static function isSafeSlug(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9][a-z0-9-]*$/', $slug);
    }

    /**
     * Resolve and validate module class.php path under legacy module root.
     */
    public static function resolveModuleClassFile(string $legacyRoot, string $moduleTypeDir, string $slug): ?string
    {
        if (!self::isSafeSlug($slug)) {
            return null;
        }

        $base = realpath($legacyRoot . '/pp-content/pp-modules/' . $moduleTypeDir);
        if ($base === false) {
            return null;
        }

        $candidate = $base . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR . 'class.php';
        $real = realpath($candidate);

        if ($real === false || !is_file($real)) {
            return null;
        }

        if (str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
            return $real;
        }

        return null;
    }
}
