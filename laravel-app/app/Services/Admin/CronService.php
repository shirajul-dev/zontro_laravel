<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\PpTransaction;
use App\Models\PpWebhookLog;
use App\Models\PpBrand;
use App\Models\PpCurrency;
use App\Models\PpBalanceVerification;
use App\Models\PpSmsData;

class CronService
{
    public function handle(string $token): array
    {
        $dbPrefix = env('DB_PREFIX', 'pp_');
        $cronJobToken = $this->getEnvValue('cron-job');

        if (!app()->runningInConsole() && $token !== $cronJobToken) {
            return [
                'status' => 'false',
                'title' => 'Access Denied',
                'message' => 'Direct access not allowed',
                'code' => 403,
            ];
        }

        $lock = Cache::lock('cron-job-lock', 600);

        if (!$lock->get()) {
            return [
                'status' => 'true',
                'message' => 'Cron run executed (already running).',
                'code' => 200,
            ];
        }

        try {
            $this->setEnvValue('last-cron-invocation', now('UTC')->format('Y-m-d H:i:s'));

            $this->runSystemUpdateChecks();
            $this->verifyPendingAgainstSmsData($dbPrefix);
            $this->autoUpdateCurrency($dbPrefix);
            $this->processBalanceVerification($dbPrefix);
            $this->processWebhookPendingLogs($dbPrefix);
            $this->expireInvoices($dbPrefix);

            // Keep the do_action legacy wrapper for components still tightly coupled with the older system.
            if (function_exists('do_action')) {
                do_action('cron.run');
            }

            return [
                'status' => 'true',
                'message' => 'Cron run executed successfully.',
                'code' => 200,
            ];
        } finally {
            $lock->release();
        }
    }

    private function getEnvValue(string $optionName, string $brandId = 'both'): string
    {
        if (function_exists('get_env')) {
            return (string) get_env($optionName, $brandId);
        }

        $row = DB::table(env('DB_PREFIX', 'pp_') . 'env')
            ->where('brand_id', $brandId)
            ->where('option_name', $optionName)
            ->first();

        return (string) ($row->value ?? '');
    }

    private function setEnvValue(string $optionName, string $value, string $brandId = 'both'): void
    {
        if (function_exists('set_env')) {
            set_env($optionName, $value, $brandId);
            return;
        }

        DB::table(env('DB_PREFIX', 'pp_') . 'env')->updateOrInsert(
            ['brand_id' => $brandId, 'option_name' => $optionName],
            ['value' => $value]
        );
    }

    private function runSystemUpdateChecks(): void
    {
        $automaticUpdate = $this->getEnvValue('system-settings-automatic_update');
        $automaticUpdate = $automaticUpdate === '--' || $automaticUpdate === '' ? '' : $automaticUpdate;

        if ($automaticUpdate === 'yes') {
            $lastCheck = $this->getEnvValue('last-auto-update-check');
            $lastCheck = $lastCheck === '--' || $lastCheck === '' ? now('UTC')->format('Y-m-d H:i:s') : $lastCheck;

            if (strtotime(now('UTC')->format('Y-m-d H:i:s')) - strtotime($lastCheck) >= 10 * 3600) {
                $this->setEnvValue('last-auto-update-check', now('UTC')->format('Y-m-d H:i:s'));

                try {
                    $jsonContent = @file_get_contents('https://updates.piprapay.com/manifest.json');
                    if ($jsonContent) {
                        $manifest = json_decode($jsonContent, true);

                        $currentCode = (string) config('piprapay.version_code', '1.0.0');
                        $currentName = (string) config('piprapay.version_name', '1.0');

                        $updateChannel = $this->getEnvValue('system-settings-update_channel');
                        if ($updateChannel === '' || $updateChannel === '--' || $updateChannel === 'stable') {
                            $updateChannel = 'stable';
                        } else {
                            $updateChannel = 'beta';
                        }

                        $channelData = $manifest['channels'][$updateChannel] ?? null;

                        if ($channelData) {
                            $latestName = $channelData['latest_version_name'];
                            $latestCode = $channelData['latest_version_code'];

                            if (version_compare($latestCode, $currentCode, '>')) {
                                if (function_exists('do_action')) {
                                    do_action('system.update.available', [
                                        'current_version_name' => $currentName,
                                        'current_version_code' => $currentCode,
                                        'latest_version_name' => $latestName,
                                        'latest_version_code' => $latestCode,
                                    ]);
                                }

                                $this->setEnvValue('last-update-version-name', $latestName);
                                $this->setEnvValue('last-update-version', $latestCode);
                            } else {
                                $this->setEnvValue('last-update-version-name', $currentName);
                                $this->setEnvValue('last-update-version', $currentCode);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore errors during update checks
                }
            }
        }
    }

    private function verifyPendingAgainstSmsData(string $dbPrefix): void
    {
        $pendingTransactions = PpTransaction::query()
            ->where('status', 'pending')
            ->whereNotIn('sender_key', ['--', ''])
            ->orderByDesc('id')
            ->get();

        $allTransactions = [];

        foreach ($pendingTransactions as $transaction) {
            $smsData = PpSmsData::query()
                ->where('sender_key', $transaction->sender_key)
                ->where('type', $transaction->sender_type)
                ->where('trx_id', $transaction->trx_id)
                ->where('status', 'approved')
                ->first();

            if ($smsData !== null) {
                $brand = PpBrand::query()->where('brand_id', $transaction->brand_id)->first();
                if ($brand !== null) {
                    $verifyFunc = function_exists('verifyPaymentTolerance');
                    $isVerified = false;
                    
                    if ($verifyFunc) {
                        $isVerified = verifyPaymentTolerance($transaction->local_net_amount, $smsData->amount, $brand->payment_tolerance);
                    } else {
                        // Fallback logic for verifyPaymentTolerance: absolute difference
                        $tolerance = (float) $brand->payment_tolerance;
                        $tAmount = (float) $transaction->local_net_amount;
                        $sAmount = (float) $smsData->amount;
                        
                        if ($tAmount === $sAmount || abs($tAmount - $sAmount) <= $tolerance) {
                            $isVerified = true;
                        }
                    }

                    if ($isVerified) {
                        $smsData->update([
                            'status' => 'used',
                            'updated_date' => now('UTC')->format('Y-m-d H:i:s'),
                        ]);

                        $transaction->update([
                            'status' => 'completed',
                            'sender' => $smsData->number,
                            'updated_date' => now('UTC')->format('Y-m-d H:i:s'),
                        ]);

                        $allTransactions[] = $transaction->toArray();

                        if ($transaction->webhook_url !== '' && $transaction->webhook_url !== '--') {
                            $payload = json_encode($transaction->toArray(), JSON_UNESCAPED_UNICODE);
                            PpWebhookLog::query()->create([
                                'ref' => $transaction->ref,
                                'brand_id' => $transaction->brand_id,
                                'payload' => $payload,
                                'url' => $transaction->webhook_url,
                                'created_date' => now('UTC')->format('Y-m-d H:i:s'),
                                'updated_date' => now('UTC')->format('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                }
            }
        }

        if (!empty($allTransactions) && function_exists('do_action')) {
            do_action('transactions.updated', $allTransactions);
        }
    }

    private function autoUpdateCurrency(string $dbPrefix): void
    {
        $brands = PpBrand::query()->where('autoExchange', 'enabled')->get();

        $multiHandle = curl_multi_init();
        $curlHandles = [];
        $brandMap = [];

        foreach ($brands as $brand) {
            $lastExchange = $this->getEnvValue('last-auto-exchange', $brand->brand_id);
            if ($lastExchange === '--' || $lastExchange === '') {
                $this->setEnvValue('last-auto-exchange', now('UTC')->format('Y-m-d H:i:s'), $brand->brand_id);
            } elseif (strtotime(now('UTC')->format('Y-m-d H:i:s')) - strtotime($lastExchange) >= 5 * 3600) {
                $this->setEnvValue('last-auto-exchange', now('UTC')->format('Y-m-d H:i:s'), $brand->brand_id);

                $url = 'https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/' . strtolower($brand->currency_code) . '.json';

                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);

                curl_multi_add_handle($multiHandle, $ch);
                $curlHandles[] = $ch;
                $brandMap[(int)$ch] = $brand;
            }
        }

        if (empty($curlHandles)) {
            curl_multi_close($multiHandle);
            return;
        }

        $running = null;
        do {
            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle);
        } while ($running > 0);

        foreach ($curlHandles as $ch) {
            $brand = $brandMap[(int)$ch];
            $response = curl_multi_getcontent($ch);
            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);

            if (!$response) continue;

            $data = json_decode((string) $response, true);
            $currCodeLower = strtolower((string) $brand->currency_code);
            
            if (!isset($data[$currCodeLower])) continue;

            $rates = $data[$currCodeLower];

            foreach ($rates as $currency => $rate) {
                if ($currency === $currCodeLower) continue;
                if ($rate <= 0) continue;

                $converted = number_format(1 / $rate, 4, '.', '');
                
                PpCurrency::query()
                    ->where('brand_id', $brand->brand_id)
                    ->where('code', $currency)
                    ->update([
                        'rate' => $converted,
                        'updated_date' => now('UTC')->format('Y-m-d H:i:s'),
                    ]);
            }
        }

        curl_multi_close($multiHandle);
    }

    private function processBalanceVerification(string $dbPrefix): void
    {
        if (function_exists('reconcileByLongestChain')) {
            $verifications = PpBalanceVerification::query()->where('status', 'active')->get();
            foreach ($verifications as $row) {
                reconcileByLongestChain((string) $row->device_id, (string) $row->sender_key, (string) $row->type);
            }
        }
    }

    private function processWebhookPendingLogs(string $dbPrefix): void
    {
        $limitEnv = $this->getEnvValue('geneal-application-settings-webhook_attempts_limit');
        $limit = ($limitEnv === '' || $limitEnv === '--') ? 1 : (int)$limitEnv;

        $webhookLogs = PpWebhookLog::query()
            ->where('status', 'pending')
            ->where('attempts', '<', $limit)
            ->orderBy('id', 'asc')
            ->limit(15)
            ->get();

        if ($webhookLogs->isEmpty()) return;

        $jobs = [];
        foreach ($webhookLogs as $log) {
            $log->update([
                'attempts' => $log->attempts + 1,
                'updated_date' => now('UTC')->format('Y-m-d H:i:s'),
            ]);

            $payloadArray = json_decode((string) $log->payload, true) ?: [];
            unset($payloadArray['webhook_url']);

            $jobs[] = [
                'id' => $log->id,
                'url' => $log->url,
                'payload' => json_encode($payloadArray, JSON_UNESCAPED_UNICODE)
            ];
        }

        $multiHandle = curl_multi_init();
        $curlHandles = [];

        foreach ($jobs as $job) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $job['url'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $job['payload'],
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($job['payload']),
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            curl_multi_add_handle($multiHandle, $ch);
            // Cast resource to int for map keys backward compatible up to php 8
            $curlHandles[(int) $ch] = $job['id'];
        }

        $running = null;
        do {
            curl_multi_exec($multiHandle, $running);
            curl_multi_select($multiHandle);
        } while ($running > 0);

        foreach ($curlHandles as $key => $logId) {
            // Reconstruct resource handle from key if needed (simplified view via integer keys)
            // Just use the stored array if iterating actual objects if php > 8
        }
        
        // Proper way for PHP 8+ where curl is an object:
        foreach (array_keys($curlHandles) as $chId) {
            // This is just to iterate. To fetch actual handle objects properly:
        }

        // Refactored iteration logic
        $mh = curl_multi_init();
        $handles = [];
        foreach ($jobs as $job) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $job['url'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $job['payload'],
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($job['payload']),
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[] = ['handle' => $ch, 'id' => $job['id']];
        }

        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        foreach ($handles as $item) {
            $ch = $item['handle'];
            $logId = $item['id'];
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            $log = PpWebhookLog::query()->find($logId);
            if ($log) {
                if ($httpCode >= 200 && $httpCode < 300) {
                    $log->update(['status' => 'sent', 'updated_date' => now('UTC')->format('Y-m-d H:i:s')]);
                } else {
                    $logLimit = get_env('geneal-application-settings-webhook_attempts_limit');
                    $logLimit = ($logLimit === '' || $logLimit === '--') ? 1 : (int)$logLimit;
                    
                    if ($log->attempts >= $logLimit) {
                        $log->update(['status' => 'failed', 'updated_date' => now('UTC')->format('Y-m-d H:i:s')]);
                    }
                }
            }

            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }

        curl_multi_close($mh);
    }

    private function expireInvoices(string $dbPrefix): void
    {
        DB::table($dbPrefix . 'invoice')
            ->where('status', 'unpaid')
            ->where('due_date', '<', now('UTC')->format('Y-m-d H:i:s'))
            ->update([
                'status' => 'expired',
                'updated_date' => now('UTC')->format('Y-m-d H:i:s')
            ]);
    }
}
