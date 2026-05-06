<?php

namespace App\Http\Controllers;

use App\Support\LegacyModuleGuard;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ModuleAssetController extends Controller
{
    /**
     * Serve assets from various module types (themes, gateways, addons).
     */
    public function serve(string $type, string $module, string $path): BinaryFileResponse
    {
        if (config('piprapay.migration.strict_module_slug_validation', true) && !LegacyModuleGuard::isSafeSlug($module)) {
            abort(404, 'Invalid module slug');
        }

        // Prevent path traversal
        if (str_contains($path, '..')) {
            abort(403, 'Forbidden');
        }

        $basePath = realpath(base_path('pp-content/pp-modules'));

        // Map type to directory
        $typeMap = [
            'theme'   => 'pp-themes',
            'gateway' => 'pp-gateways',
            'addon'   => 'pp-addons',
        ];

        if (!isset($typeMap[$type])) {
            abort(404, 'Invalid module type');
        }

        $assetPath = $basePath . DIRECTORY_SEPARATOR . $typeMap[$type] . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $path;

        if (!file_exists($assetPath) || is_dir($assetPath)) {
            abort(404, 'Asset not found');
        }

        $extension = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'png'   => 'image/png',
            'gif'   => 'image/gif',
            'svg'   => 'image/svg+xml',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'otf'   => 'font/otf',
            'ico'   => 'image/x-icon',
        ];

        $contentType = $mimeTypes[$extension] ?? mime_content_type($assetPath) ?: 'application/octet-stream';

        return response()->file($assetPath, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
