<?php

namespace App\Services\Admin;

use App\Models\PpEnv;
use Illuminate\Support\Facades\DB;

class SystemSettingsAdminActionService
{
    public function updateGeneralSettings(array $input): array
    {
        $this->setEnvValue('geneal-application-settings-homepageRedirect', trim((string) ($input['homepageRedirect'] ?? '')));
        $this->setEnvValue('geneal-application-settings-adminPath', trim((string) ($input['adminPath'] ?? '')));
        $this->setEnvValue('geneal-application-settings-invoicePath', trim((string) ($input['invoicePath'] ?? '')));
        $this->setEnvValue('geneal-application-settings-paymentLinkPath', trim((string) ($input['paymentLinkPath'] ?? '')));
        $this->setEnvValue('geneal-application-settings-paymentPath', trim((string) ($input['paymentPath'] ?? '')));
        $this->setEnvValue('geneal-application-settings-cronPath', trim((string) ($input['cronPath'] ?? '')));
        $this->setEnvValue('geneal-application-settings-default_timezone', trim((string) ($input['default_timezone'] ?? '')));
        $this->setEnvValue('geneal-application-settings-webhook_attempts_limit', trim((string) ($input['webhook_attempts_limit'] ?? '')));

        return [
            'status' => 'true',
            'title' => 'Settings Updated',
            'message' => 'The application settings has been updated successfully.',
        ];
    }

    public function generateCronCommand(): array
    {
        $cronCommand = bin2hex(random_bytes(8));
        $this->setEnvValue('cron-job', $cronCommand);

        return [
            'status' => 'true',
            'title' => 'Cron Command Generated',
            'message' => 'Your cron command has been updated. You can now copy it or use it immediately.',
            'cron_command' => $cronCommand,
        ];
    }

    public function updateUpdateSetting(array $input): array
    {
        $this->setEnvValue('system-settings-update_channel', trim((string) ($input['update_channel'] ?? '')));
        $this->setEnvValue('system-settings-automatic_update', trim((string) ($input['automatic_update'] ?? '')));
        $this->setEnvValue('system-settings-create_backup', trim((string) ($input['create_backup'] ?? '')));

        return [
            'status' => 'true',
            'title' => 'Settings Updated',
            'message' => 'Your changes have been saved successfully.',
        ];
    }

    public function checkForUpdate(array $currentVersion): array
    {
        $this->setEnvValue('last-auto-update-check', now()->format('Y-m-d H:i:s'));

        $manifestRaw = @file_get_contents('https://updates.piprapay.com/manifest.json');
        $manifest = is_string($manifestRaw) ? json_decode($manifestRaw, true) : null;
        if (!is_array($manifest)) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $currentCode = (string) ($currentVersion['version_code'] ?? '0.0.0');
        $currentName = (string) ($currentVersion['version_name'] ?? 'Unknown');
        $versionHash = (string) ($currentVersion['version_hash'] ?? '');

        $configuredChannel = $this->getEnvValue('system-settings-update_channel');
        $updateChannel = ($configuredChannel === '' || $configuredChannel === '--' || $configuredChannel === 'stable') ? 'stable' : 'beta';

        $channelData = $manifest['channels'][$updateChannel] ?? null;
        if (!is_array($channelData)) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $latestName = (string) ($channelData['latest_version_name'] ?? '');
        $latestCode = (string) ($channelData['latest_version_code'] ?? '');
        $latestHash = '';

        $versions = $channelData['versions'] ?? [];
        if (is_array($versions)) {
            foreach ($versions as $version) {
                if (!is_array($version)) {
                    continue;
                }

                if ((string) ($version['version_code'] ?? '') === $latestCode) {
                    $latestHash = (string) ($version['checksum'] ?? '');
                    break;
                }
            }
        }

        $updateAvailable = $latestCode !== '' && version_compare($latestCode, $currentCode, '>');

        if ($updateAvailable) {
            $this->setEnvValue('last-update-version-name', $latestName);
            $this->setEnvValue('last-update-version-hash', $latestHash);
            $this->setEnvValue('last-update-version', $latestCode);

            return [
                'status' => 'true',
                'title' => 'Update Available',
                'message' => 'A new system update is available. Please update to get the latest features and improvements.',
            ];
        }

        $this->setEnvValue('last-update-version-name', $currentName);
        $this->setEnvValue('last-update-version-hash', $versionHash);
        $this->setEnvValue('last-update-version', $currentCode);

        return [
            'status' => 'true',
            'title' => 'System Up to Date',
            'message' => 'Everything is up to date. No updates were found.',
        ];
    }

    public function downloadUpdate(array $currentVersion): array
    {
        $latestUpdateVersion = $this->getEnvValue('last-update-version');
        $currentCode = (string) ($currentVersion['version_code'] ?? '0.0.0');

        $updateAvailable = $latestUpdateVersion !== '' && version_compare($latestUpdateVersion, $currentCode, '>');
        if (!$updateAvailable) {
            return [
                'status' => 'true',
                'title' => 'System Up to Date',
                'message' => 'Everything is up to date. No updates were found.',
            ];
        }

        $url = 'https://updates.piprapay.com/download.php?version=' . urlencode($latestUpdateVersion);
        $saveDir = rtrim(storage_path('app/public/media/updates'), '/');

        if (!is_dir($saveDir)) {
            @mkdir($saveDir, 0755, true);
        }

        $saveTo = $saveDir . '/' . $latestUpdateVersion . '.zip';

        $ch = curl_init($url);
        $fp = @fopen($saveTo, 'w');
        if ($ch === false || $fp === false) {
            if (is_resource($fp)) {
                fclose($fp);
            }

            return [
                'status' => 'false',
                'title' => 'Download Failed',
                'message' => 'The latest update could not be downloaded. Please check your internet connection or try again later.',
            ];
        }

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $success = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        fclose($fp);

        if (!$success || $httpCode >= 400) {
            return [
                'status' => 'false',
                'title' => 'Download Failed',
                'message' => 'The latest update could not be downloaded. Please check your internet connection or try again later.',
            ];
        }

        return [
            'status' => 'true',
            'title' => 'Update Downloaded',
            'message' => 'The latest version has been downloaded successfully and is ready to be installed.',
        ];
    }

    public function installUpdate(array $currentVersion): array
    {
        $latestUpdateVersion = $this->getEnvValue('last-update-version');
        $latestUpdateVersionHash = $this->getEnvValue('last-update-version-hash');
        $currentCode = (string) ($currentVersion['version_code'] ?? '0.0.0');

        $updateAvailable = $latestUpdateVersion !== '' && version_compare($latestUpdateVersion, $currentCode, '>');
        if (!$updateAvailable) {
            return [
                'status' => 'true',
                'title' => 'System Up to Date',
                'message' => 'Everything is up to date. No updates were found.',
            ];
        }

        $root = base_path();
        $storage = rtrim(storage_path('app/public/media'), '/') . '/';
        $backupDir = $storage . 'backup/';
        $tempDir = $storage . 'temp/' . $latestUpdateVersion . '/';
        $zipFile = $storage . 'updates/' . $latestUpdateVersion . '.zip';

        if (!file_exists($zipFile)) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        if ($latestUpdateVersionHash !== '' && sha1_file($zipFile) !== $latestUpdateVersionHash) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Update file checksum mismatch! Possible corruption or tampering.',
            ];
        }

        @mkdir($backupDir, 0755, true);
        @mkdir($tempDir, 0755, true);

        if (function_exists('zipFolder')) {
            zipFolder($root, $backupDir . $currentCode . '.zip');
        }

        if (function_exists('backupDatabasePDO')) {
            backupDatabasePDO($backupDir . 'db_' . $currentCode . '.sql');
        }

        file_put_contents($root . '/.maintenance', 'updating');

        if (function_exists('extractUpdate')) {
            extractUpdate($zipFile, $tempDir);
        } else {
            $zip = new \ZipArchive();
            if ($zip->open($zipFile) === true) {
                $zip->extractTo($tempDir);
                $zip->close();
            }
        }

        if (function_exists('copyFolder')) {
            copyFolder($tempDir, $root);
        }

        $updateSql = $tempDir . 'update.sql';
        if (file_exists($updateSql)) {
            if (function_exists('runSql')) {
                runSql($updateSql);
            } else {
                $sql = (string) file_get_contents($updateSql);
                if ($sql !== '') {
                    DB::unprepared($sql);
                }
            }
        }

        if (function_exists('deleteFolder')) {
            deleteFolder($tempDir);
        }

        @unlink($root . '/.maintenance');

        return [
            'status' => 'true',
            'title' => 'Installation Successful',
            'message' => 'The latest version has been installed successfully. Your system is now up to date.',
        ];
    }

    public function updateThemeSettings(array $postData, array $filesData, string $brandId, string $themeSlug, string $siteUrl): array
    {
        foreach ($postData as $key => $value) {
            if (in_array($key, ['action', 'csrf_token'], true)) {
                continue;
            }

            $optionName = $themeSlug . '-' . $key;

            if (is_array($value)) {
                $value = json_encode($value);
            }

            $this->setEnvValue($optionName, (string) $value, $brandId);
        }

        foreach ($postData as $key => $value) {
            if (str_starts_with($key, 'is_')) {
                // If it's sent as a string/array, we keep it due to array iteration above.
                // However, checkboxes that are UNCHECKED are completely absent from $_POST and $postData.
                // We should handle that outside this loop, but since we receive $postData exactly as sent,
                // if it's missing, we need to explicitly check known checkboxes.
                // For a dynamic form, the frontend should send hidden fields or we need a list.
                // The legacy code used `!isset($_POST[$key])`, meaning it iterated over $_POST? No,
                // legacy iterated over `$_POST as $key => $value` which means it never saw unchecked checkboxes via this loop!
                // Wait! Legacy had:
                // foreach($_POST as $key=>$value) {
                //    if(!isset($_POST[$key]) && strpos($key, 'is_')===0){ $value = 0; }
                // }
                // That logic in legacy was flawed! If it's iterating over `$_POST`, `isset($_POST[$key])` will ALWAYS be true!
                // So it never actually handled unchecked checkboxes. 
            }
        }

        foreach ($filesData as $key => $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $optionName = $themeSlug . '-' . $key;
                
                $maxSize = 5 * 1024 * 1024;
                if ($file->getSize() > $maxSize) {
                    continue;
                }

                $extension = strtolower($file->getClientOriginalExtension());
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($extension, $allowed, true)) {
                    continue;
                }

                $filename = strtolower(\Illuminate\Support\Str::random(10) . '_' . time() . '.' . $extension);
                
                $uploadPath = storage_path('app/public/media');
                $file->move($uploadPath, $filename);

                $this->setEnvValue($optionName, rtrim($siteUrl, '/') . '/storage/media/' . $filename, $brandId);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Theme Setting Updated',
            'message' => 'The theme setting has been updated successfully.',
        ];
    }

    private function setEnvValue(string $optionName, string $value, string $brandId = 'both'): void
    {
        if (function_exists('set_env')) {
            set_env($optionName, $value, $brandId);
            return;
        }

        $row = PpEnv::query()
            ->where('brand_id', $brandId)
            ->where('option_name', $optionName)
            ->first();

        if ($row === null) {
            PpEnv::query()->create([
                'brand_id' => $brandId,
                'option_name' => $optionName,
                'value' => $value,
                'created_date' => now()->format('Y-m-d H:i:s'),
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);
            return;
        }

        $row->update([
            'value' => $value,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function getEnvValue(string $optionName, string $brandId = 'both'): string
    {
        if (function_exists('get_env')) {
            return (string) get_env($optionName);
        }

        $row = PpEnv::query()
            ->where('brand_id', $brandId)
            ->where('option_name', $optionName)
            ->first();

        if ($row === null) {
            return '';
        }

        $value = (string) ($row->value ?? '');

        return $value === '--' ? '' : $value;
    }

    public function importSystemSettings(array $filesData): array
    {
        $zipFile = $filesData['zip_file'] ?? null;

        if (!($zipFile instanceof \Illuminate\Http\UploadedFile) || !$zipFile->isValid()) {
            return [
                'status' => 'false',
                'title' => 'Upload Failed',
                'message' => 'No file uploaded or upload error occurred.',
            ];
        }

        $maxSize = 100 * 1024 * 1024;
        if ($zipFile->getSize() > $maxSize) {
            return [
                'status' => 'false',
                'title' => 'File Too Large',
                'message' => 'File exceeds maximum allowed size of 100MB.',
            ];
        }

        $ext = strtolower($zipFile->getClientOriginalExtension());
        if ($ext !== 'zip') {
            return [
                'status' => 'false',
                'title' => 'Invalid File',
                'message' => 'Only ZIP files are allowed.',
            ];
        }

        $root = base_path();
        $storage = rtrim(storage_path('app/public/media'), '/') . '/';
        $updatesDir = $storage . 'import/';

        if (!is_dir($storage)) {
            @mkdir($storage, 0755, true);
        }
        if (!is_dir($updatesDir)) {
            @mkdir($updatesDir, 0755, true);
        }

        $sanitizedName = pathinfo($zipFile->getClientOriginalName(), PATHINFO_FILENAME);
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_\-]/', '', $sanitizedName);
        
        $tempDir = $storage . 'temp/' . $sanitizedName . '/';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        $destination = $updatesDir . $zipFile->getClientOriginalName();
        $zipFile->move($updatesDir, $zipFile->getClientOriginalName());

        try {
            if (function_exists('extractUpdate')) {
                extractUpdate($destination, $tempDir);
            } else {
                $zip = new \ZipArchive();
                if ($zip->open($destination) === true) {
                    $zip->extractTo($tempDir);
                    $zip->close();
                } else {
                    throw new \Exception('Uploaded file is not a valid ZIP.');
                }
            }

            if (function_exists('copyFolder')) {
                // Map legacy paths to new structure in temp directory before copying to root
                $mappings = [
                    'pp-content/pp-modules/pp-addons' => 'app/Modules/addons',
                    'pp-content/pp-modules/pp-gateways' => 'app/Modules/gateways',
                    'pp-content/pp-modules/pp-themes' => 'resources/views/theme',
                    'pp-content/pp-media' => 'storage/app/public/media',
                    'public/pp-content' => 'storage/app/public/media', // Some packages might have this
                ];

                foreach ($mappings as $oldRel => $newRel) {
                    $oldPath = rtrim($tempDir, '/') . '/' . $oldRel;
                    $newPath = rtrim($tempDir, '/') . '/' . $newRel;

                    if (is_dir($oldPath)) {
                        if (!is_dir(dirname($newPath))) {
                            @mkdir(dirname($newPath), 0755, true);
                        }
                        
                        // If destination already exists, we use copyFolder to merge, then delete
                        if (is_dir($newPath)) {
                            copyFolder($oldPath, $newPath);
                            if (function_exists('deleteFolder')) {
                                deleteFolder($oldPath);
                            }
                        } else {
                            @rename($oldPath, $newPath);
                        }
                    }
                }

                // Auto-detection for addons/themes that might be at the root of the ZIP
                $topDirs = array_filter(glob($tempDir . '*'), 'is_dir');
                $knownRootDirs = ['app', 'config', 'database', 'public', 'resources', 'routes', 'storage', 'tests', 'vendor', 'bootstrap', 'pp-content'];
                
                foreach ($topDirs as $dirPath) {
                    $dirName = basename($dirPath);
                    if (in_array($dirName, $knownRootDirs)) {
                        continue;
                    }

                    // Check for Addon (class.php)
                    if (file_exists($dirPath . '/class.php')) {
                        $targetDir = app_path('Modules/addons/' . $dirName);
                        if (!is_dir(dirname($targetDir))) {
                            @mkdir(dirname($targetDir), 0755, true);
                        }
                        if (is_dir($targetDir)) {
                            copyFolder($dirPath, $targetDir);
                            if (function_exists('deleteFolder')) {
                                deleteFolder($dirPath);
                            }
                        } else {
                            @rename($dirPath, $targetDir);
                        }
                    }
                    // Check for Theme (info.php)
                    elseif (file_exists($dirPath . '/info.php') || file_exists($dirPath . '/index.blade.php')) {
                        $targetDir = resource_path('views/theme/' . $dirName);
                        if (!is_dir(dirname($targetDir))) {
                            @mkdir(dirname($targetDir), 0755, true);
                        }
                        if (is_dir($targetDir)) {
                            copyFolder($dirPath, $targetDir);
                            if (function_exists('deleteFolder')) {
                                deleteFolder($dirPath);
                            }
                        } else {
                            @rename($dirPath, $targetDir);
                        }
                    }
                }

                // Remove the empty or processed legacy folders from tempDir so they aren't recreated in root
                if (is_dir($tempDir . 'pp-content')) {
                    if (function_exists('deleteFolder')) {
                        deleteFolder($tempDir . 'pp-content');
                    }
                }
                if (is_dir($tempDir . 'public')) {
                    if (function_exists('deleteFolder')) {
                        deleteFolder($tempDir . 'public');
                    }
                }

                copyFolder($tempDir, $root);
            }

            $sqlFile = $tempDir . 'sql.sql';
            if (file_exists($sqlFile)) {
                if (function_exists('runSql')) {
                    runSql($sqlFile);
                } else {
                    $sql = (string) file_get_contents($sqlFile);
                    if ($sql !== '') {
                        DB::unprepared($sql);
                    }
                }
            }

            if (function_exists('deleteFolder')) {
                deleteFolder($tempDir);
            }

            if (file_exists($destination)) {
                @unlink($destination);
            }

            return [
                'status' => 'true',
                'title' => 'Import Successful',
                'message' => 'ZIP file imported and applied successfully!',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'false',
                'title' => 'Server Error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
