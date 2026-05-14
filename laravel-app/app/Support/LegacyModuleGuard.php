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

        // Map old pp-content names to new Laravel-standard locations
        $base = match ($moduleTypeDir) {
            'pp-gateways' => app_path('Modules/gateways'),
            'pp-addons'   => app_path('Modules/addons'),
            'pp-themes'   => resource_path('views/theme'),
            default       => realpath($legacyRoot . '/pp-content/pp-modules/' . $moduleTypeDir),
        };

        if ($base === false || !is_dir($base)) {
            return null;
        }

        $candidate = $base . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR . 'class.php';
        $real = realpath($candidate);

        if ($real === false || !is_file($real)) {
            return null;
        }

        // Ensure the resolved file is still within the intended base directory
        if (str_starts_with($real, $base)) {
            return $real;
        }

        return null;
    }
}
