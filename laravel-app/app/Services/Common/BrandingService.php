<?php
declare(strict_types=1);

namespace App\Services\Common;

use App\Models\PpBrand;
use App\Models\PpEnv;
use Illuminate\Support\Facades\Cache;

/**
 * BrandingService
 * 
 * Handles brand resolution, theme selection, and branding assets.
 */
class BrandingService
{
    /**
     * Resolve brand by its unique brand_id string.
     */
    public function getBrand(string $brandId): ?PpBrand
    {
        return Cache::remember("brand_{$brandId}", 3600, function () use ($brandId) {
            return PpBrand::where('brand_id', $brandId)->first();
        });
    }

    /**
     * Get a specific setting (env) for a brand.
     */
    public function getSetting(string $key, string $brandId, $default = null)
    {
        $setting = PpEnv::where('brand_id', $brandId)
            ->where('option_name', $key)
            ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Resolve absolute URL for a branding asset.
     */
    public function getAssetUrl(?string $path, string $type = 'branding_media'): string
    {
        if (!$path || $path === '--') {
            return $this->getDefaultAsset($type);
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return asset("storage/media/{$type}/" . ltrim($path, '/'));
    }

    private function getDefaultAsset(string $type): string
    {
        return match ($type) {
            'logo' => 'https://help.piprapay.com/storage/branding_media/8a5c6ee4-8eba-401d-bffb-c43006d5f65d.png',
            'favicon' => 'https://help.piprapay.com/favicon/icon-144x144.png',
            default => '',
        };
    }
}
