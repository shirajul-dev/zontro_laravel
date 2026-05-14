<?php

namespace App\Services\Admin;

use App\Models\PpAddon;
use App\Models\PpAddonParameter;

class AddonAdminActionService
{
    public function updateAddonSetting(array $input): array
    {
        $addonId = trim((string) ($input['addon-id'] ?? ''));
        $status = trim((string) ($input['status'] ?? ''));

        if ($addonId === '' || $status === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $addon = PpAddon::query()->where('addon_id', $addonId)->first();
        if ($addon === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid Addon ID',
            ];
        }

        $addon->update([
            'status' => $status,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Addon Updated',
            'message' => 'The addon has been updated successfully.',
        ];
    }

    public function updateAddonConfiguration(array $postData, array $filesData, string $siteUrl): array
    {
        $addonId = trim((string) ($postData['addon-id'] ?? ''));
        if ($addonId === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $addon = PpAddon::query()->where('addon_id', $addonId)->first();
        if ($addon === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid Addon ID',
            ];
        }

        $configData = [];
        $skipFields = ['action', 'csrf_token', 'addon-id'];

        foreach ($postData as $key => $value) {
            if (in_array($key, $skipFields, true)) {
                continue;
            }
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $configData[$key] = $value;
        }

        foreach ($filesData as $key => $file) {
            if (!($file instanceof \Illuminate\Http\UploadedFile)) {
                continue;
            }
            $maxSize = 5 * 1024 * 1024;
            if ($file->getSize() <= $maxSize) {
                $ext = strtolower($file->getClientOriginalExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $filename = strtolower(\Illuminate\Support\Str::random(10) . '_' . time() . '.' . $ext);
                    $path = storage_path('app/public/media');
                    $file->move($path, $filename);
                    $configData[$key] = rtrim($siteUrl, '/') . '/storage/media/' . $filename;
                }
            }
        }

        foreach ($configData as $optName => $optVal) {
            $val = (string) $optVal;

            $param = PpAddonParameter::query()
                ->where('addon_id', $addonId)
                ->where('option_name', $optName)
                ->first();

            if ($param !== null) {
                $param->update([
                    'value' => $val === '' ? '--' : $val,
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            } else {
                PpAddonParameter::query()->create([
                    'addon_id' => $addonId,
                    'option_name' => $optName,
                    'value' => $val === '' ? '--' : $val,
                    'created_date' => now()->format('Y-m-d H:i:s'),
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Addon Configuration Updated',
            'message' => 'The addon configuration has been updated successfully.',
        ];
    }
}
