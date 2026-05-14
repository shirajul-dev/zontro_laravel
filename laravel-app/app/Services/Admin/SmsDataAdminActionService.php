<?php

namespace App\Services\Admin;

use App\Models\PpDevice;
use App\Models\PpSmsData;

class SmsDataAdminActionService
{
    public function list(array $input, string $brandTimezone): array
    {
        $searchInput = trim((string) ($input['search_input'] ?? ''));
        $tabType = trim((string) ($input['tabType'] ?? ''));
        $filterStatus = trim((string) ($input['filter_status'] ?? ''));
        $filterStart = trim((string) ($input['filter_start'] ?? ''));
        $filterEnd = trim((string) ($input['filter_end'] ?? ''));

        $page = max(1, (int) ($input['page'] ?? 1));
        $rawShowLimit = (string) ($input['show_limit'] ?? '');
        $showLimit = $rawShowLimit === '' ? 999999 : (int) $rawShowLimit;
        if ($showLimit <= 0) {
            $showLimit = 8;
        }

        $query = PpSmsData::query()
            ->where('device_id', '!=', '00')
            ->where('status', '!=', 'error');

        if ($tabType !== '' && $tabType !== 'all') {
            $query->where('status', $tabType);
        }

        if ($filterStart !== '') {
            $query->where('created_date', '>=', $filterStart . ' 00:00:00');
        }

        if ($filterEnd !== '') {
            $query->where('created_date', '<=', $filterEnd . ' 23:59:59');
        }

        if ($filterStatus !== '') {
            $query->where('status', $filterStatus);
        }

        if ($searchInput !== '') {
            $query->where(function ($q) use ($searchInput): void {
                $q->where('sender_key', 'like', "%{$searchInput}%")
                    ->orWhere('amount', 'like', "%{$searchInput}%")
                    ->orWhere('currency', 'like', "%{$searchInput}%")
                    ->orWhere('trx_id', 'like', "%{$searchInput}%")
                    ->orWhere('message', 'like', "%{$searchInput}%");
            });
        }

        $totalRecords = (clone $query)->count();
        $offset = ($page - 1) * $showLimit;

        $rows = (clone $query)
            ->orderByDesc('id')
            ->offset($offset)
            ->limit($showLimit)
            ->get();

        if ($rows->isEmpty()) {
            return [
                'status' => 'false',
                'title' => 'Nothing Here Yet',
                'message' => 'No data is available at the moment.',
            ];
        }

        $deviceMap = PpDevice::query()
            ->whereIn('device_id', $rows->pluck('device_id')->filter()->unique()->values())
            ->get(['device_id', 'name'])
            ->keyBy('device_id');

        $timezone = $brandTimezone === '' || $brandTimezone === '--' ? 'Asia/Dhaka' : $brandTimezone;

        $response = $rows->map(function (PpSmsData $row) use ($timezone, $deviceMap): array {
            $provider = senderWhitelist(null, (string) $row->sender_key);
            $deviceName = (string) ($deviceMap->get((string) $row->device_id)?->name ?? '');

            return [
                'id' => (int) $row->id,
                'device' => $deviceName,
                'payment_method' => $provider ? (string) ($provider['name'] ?? '') : '',
                'type' => ((string) $row->type === '--') ? '' : (string) $row->type,
                'mobileNumber' => ((string) $row->number === '--') ? '' : (string) $row->number,
                'transaction_id' => ((string) $row->trx_id === '--') ? '' : (string) $row->trx_id,
                'amount' => ((string) $row->currency === '--') ? '' : (string) $row->currency . ' ' . $this->roundMoney((string) $row->amount, 2),
                'balance' => ((string) $row->currency === '--') ? '' : (string) $row->currency . ' ' . $this->roundMoney((string) $row->balance, 2),
                'status' => (string) $row->status,
                'created_date' => convertUTCtoUserTZ((string) $row->created_date, $timezone, 'M d, Y h:i A'),
                'updated_date' => convertUTCtoUserTZ((string) $row->updated_date, $timezone, 'M d, Y h:i A'),
            ];
        })->values()->all();

        $totalPages = max(1, (int) ceil($totalRecords / $showLimit));
        $start = $offset + 1;
        $end = min($offset + $showLimit, $totalRecords);

        return [
            'status' => 'true',
            'response' => $response,
            'datatableInfo' => "Showing <strong>{$start} to {$end}</strong> of <strong>{$totalRecords} entries</strong>",
            'pagination' => $this->buildPaginationHtml($page, $totalPages),
        ];
    }

    public function create(array $input): array
    {
        $deviceId = trim((string) ($input['device'] ?? ''));
        $entryType = trim((string) ($input['entry_type'] ?? ''));
        $senderKey = trim((string) ($input['sender_key'] ?? ''));
        $status = trim((string) ($input['status'] ?? ''));
        $message = trim((string) ($input['message'] ?? ''));
        $type = trim((string) ($input['type'] ?? ''));
        $amount = trim((string) ($input['amount'] ?? ''));
        $phoneNumber = trim((string) ($input['phone_number'] ?? ''));
        $transactionId = trim((string) ($input['transaction_id'] ?? ''));
        $currency = trim((string) ($input['currency'] ?? ''));

        if ($entryType === '' || $senderKey === '' || $status === '') {
            return $this->error('Incomplete Information', 'Please fill in all required fields before proceeding.');
        }

        if ($entryType === 'automatic') {
            if ($message === '') {
                return $this->error('Incomplete Information', 'Please fill in all required fields before proceeding.');
            }

            $this->ensureLegacySmsParserLoaded();
            $result = MFSMessageVerified($senderKey, $message);

            if ($result === false) {
                return $this->error('Invalid or unknown MFS message', 'Please fill in all required fields before proceeding.');
            }

            $type = trim((string) ($result['type'] ?? ''));
            $amount = trim((string) ($result['amount'] ?? ''));
            $balance = trim((string) ($result['balance'] ?? '0'));
            $phoneNumber = trim((string) ($result['sender'] ?? ''));
            $transactionId = trim((string) ($result['trxid'] ?? ''));

            if ($type === '' || $amount === '' || $phoneNumber === '' || $transactionId === '') {
                return $this->error('Invalid or unknown MFS message', 'Please fill in all required fields before proceeding.');
            }

            if ($this->hasDuplicateTransaction($senderKey, $transactionId)) {
                return $this->error('Duplicate Transaction', 'The provided Transaction ID already exists in our system.');
            }

            if ($deviceId === '') {
                $deviceId = '--';
            }

            PpSmsData::query()->create([
                'device_id' => $deviceId,
                'sender_key' => $senderKey,
                'number' => $phoneNumber,
                'amount' => $this->sanitizeMoney($amount),
                'currency' => $currency,
                'trx_id' => $transactionId,
                'balance' => $this->sanitizeMoney($balance),
                'type' => $type,
                'entry_type' => $entryType,
                'status' => $status,
                'message' => $message,
                'created_date' => now()->format('Y-m-d H:i:s'),
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);

            return [
                'status' => 'true',
                'title' => 'SMS Data Created',
                'message' => 'The sms data has been created successfully.' . $amount,
            ];
        }

        if ($type === '' || $amount === '' || $phoneNumber === '' || $transactionId === '') {
            return $this->error('Incomplete Information', 'Please fill in all required fields before proceeding.');
        }

        if ($this->hasDuplicateTransaction($senderKey, $transactionId)) {
            return $this->error('Duplicate Transaction', 'The provided Transaction ID already exists in our system.');
        }

        if ($deviceId === '') {
            $deviceId = '--';
        }

        PpSmsData::query()->create([
            'device_id' => $deviceId,
            'sender_key' => $senderKey,
            'number' => $phoneNumber,
            'amount' => $this->sanitizeMoney($amount),
            'currency' => $currency,
            'trx_id' => $transactionId,
            'balance' => $this->sanitizeMoney('0'),
            'type' => $type,
            'entry_type' => $entryType,
            'status' => $status,
            'message' => $message,
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'SMS Data Created',
            'message' => 'The sms data has been created successfully.',
        ];
    }

    public function infoById(int $id): array
    {
        $row = PpSmsData::query()->find($id);
        if ($row === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        return [
            'status' => 'true',
            'device_id' => (string) $row->device_id,
            'sender_key' => (string) $row->sender_key,
            'number' => (string) $row->number,
            'amount' => $this->roundMoney((string) $row->amount, 2),
            'currency' => (string) $row->currency,
            'trx_id' => (string) $row->trx_id,
            'balance' => $this->roundMoney((string) $row->balance, 2),
            'message' => (string) ($row->message ?? ''),
            'type' => (string) $row->type,
            'entry_type' => (string) $row->entry_type,
            'istatus' => (string) $row->status,
            'reason' => (string) ($row->reason ?? ''),
        ];
    }

    public function edit(array $input): array
    {
        $itemId = (int) ($input['itemid'] ?? 0);
        $row = PpSmsData::query()->find($itemId);

        if ($row === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $deviceId = trim((string) ($input['device'] ?? ''));
        $senderKey = trim((string) ($input['sender_key'] ?? ''));
        $status = trim((string) ($input['status'] ?? ''));
        $message = trim((string) ($input['message'] ?? ''));
        $type = trim((string) ($input['type'] ?? ''));
        $amount = trim((string) ($input['amount'] ?? ''));
        $phoneNumber = trim((string) ($input['phone_number'] ?? ''));
        $transactionId = trim((string) ($input['transaction_id'] ?? ''));
        $currency = trim((string) ($input['currency'] ?? ''));
        $entryType = (string) $row->entry_type;

        if ($entryType === '' || $senderKey === '' || $status === '') {
            return $this->error('Incomplete Information', 'Please fill in all required fields before proceeding.');
        }

        if ($entryType === 'automatic') {
            if ($message === '') {
                return $this->error('Incomplete Information', 'Please fill in all required fields before proceeding.');
            }

            $this->ensureLegacySmsParserLoaded();
            $result = MFSMessageVerified($senderKey, $message);

            if ($result === false) {
                return $this->error('Invalid or unknown MFS message', 'Please fill in all required fields before proceeding.');
            }

            $type = trim((string) ($result['type'] ?? ''));
            $amount = trim((string) ($result['amount'] ?? ''));
            $balance = trim((string) ($result['balance'] ?? '0'));
            $phoneNumber = trim((string) ($result['sender'] ?? ''));
            $transactionId = trim((string) ($result['trxid'] ?? ''));

            if ($type === '' || $amount === '' || $phoneNumber === '' || $transactionId === '') {
                return $this->error('Invalid or unknown MFS message', 'Please fill in all required fields before proceeding.');
            }

            if ($this->hasDuplicateTransaction($senderKey, $transactionId, $itemId)) {
                return $this->error('Duplicate Transaction', 'The provided Transaction ID already exists in our system.');
            }

            if ($deviceId === '') {
                $deviceId = '--';
            }

            $row->update([
                'device_id' => $deviceId,
                'sender_key' => $senderKey,
                'number' => $phoneNumber,
                'amount' => $this->sanitizeMoney($amount),
                'currency' => $currency,
                'trx_id' => $transactionId,
                'balance' => $this->sanitizeMoney($balance),
                'type' => $type,
                'entry_type' => $entryType,
                'status' => $status,
                'message' => $message,
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);

            return [
                'status' => 'true',
                'title' => 'SMS Data Updated',
                'message' => 'The sms data has been updated successfully.',
            ];
        }

        if ($type === '' || $amount === '' || $phoneNumber === '' || $transactionId === '') {
            return $this->error('Incomplete Information', 'Please fill in all required fields before proceeding.');
        }

        if ($this->hasDuplicateTransaction($senderKey, $transactionId, $itemId)) {
            return $this->error('Duplicate Transaction', 'The provided Transaction ID already exists in our system.');
        }

        if ($deviceId === '') {
            $deviceId = '--';
        }

        $row->update([
            'device_id' => $deviceId,
            'sender_key' => $senderKey,
            'number' => $phoneNumber,
            'amount' => $this->sanitizeMoney($amount),
            'currency' => $currency,
            'trx_id' => $transactionId,
            'balance' => $this->sanitizeMoney('0'),
            'type' => $type,
            'entry_type' => $entryType,
            'status' => $status,
            'message' => $message,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'SMS Data Updated',
            'message' => 'The sms data has been updated successfully.',
        ];
    }

    public function delete(int $itemId): array
    {
        if ($itemId > 0) {
            PpSmsData::query()->where('id', $itemId)->delete();
        }

        return [
            'status' => 'true',
            'title' => 'SMS Data Deleted',
            'message' => 'The selected sms data have been deleted successfully.',
        ];
    }

    public function bulkAction(string $actionId, array $selectedIds): array
    {
        if ($selectedIds === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No customers selected.',
            ];
        }

        $ids = array_values(array_filter(array_map(static fn ($id) => (int) $id, $selectedIds), static fn ($id) => $id > 0));

        if ($ids === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No customers selected.',
            ];
        }

        if ($actionId === 'deleted') {
            PpSmsData::query()->whereIn('id', $ids)->delete();
        } else {
            PpSmsData::query()->whereIn('id', $ids)->update([
                'status' => $actionId,
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        return [
            'status' => 'true',
            'title' => 'SMS Data ' . $actionId,
            'message' => 'The selected sms datas have been ' . $actionId . ' successfully.',
        ];
    }

    private function hasDuplicateTransaction(string $senderKey, string $transactionId, ?int $excludeId = null): bool
    {
        $query = PpSmsData::query()
            ->where('sender_key', $senderKey)
            ->where('trx_id', $transactionId);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function ensureLegacySmsParserLoaded(): void
    {
        if (function_exists('MFSMessageVerified')) {
            return;
        }

        $legacyFunctionsPath = base_path('app/Support/zp-functions.php');
        if (file_exists($legacyFunctionsPath)) {
            require_once $legacyFunctionsPath;
        }
    }

    private function error(string $title, string $message): array
    {
        return [
            'status' => 'false',
            'title' => $title,
            'message' => $message,
        ];
    }

    private function sanitizeMoney(string|int|float|null $value): string
    {
        return is_numeric($value) ? (string) $value : '0';
    }

    private function roundMoney(string|int|float|null $amount, int $decimals = 2): string
    {
        $value = is_numeric($amount) ? (float) $amount : 0.0;

        return number_format($value, $decimals, '.', '');
    }

    private function buildPaginationHtml(int $page, int $totalPages): string
    {
        $html = '<ul class="pagination m-0 ms-auto">';

        $html .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '">'
            . '<button class="page-link" ' . ($page > 1 ? 'data-page="' . ($page - 1) . '"' : '') . '>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">'
            . '<path d="M15 6l-6 6l6 6"></path>'
            . '</svg>'
            . '</button>'
            . '</li>';

        for ($i = 1; $i <= $totalPages; $i++) {
            $html .= '<li class="page-item' . ($i === $page ? ' active' : '') . '">'
                . '<button class="page-link" data-page="' . $i . '">' . $i . '</button>'
                . '</li>';
        }

        $html .= '<li class="page-item' . ($page >= $totalPages ? ' disabled' : '') . '">'
            . '<button class="page-link" ' . ($page < $totalPages ? 'data-page="' . ($page + 1) . '"' : '') . '>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">'
            . '<path d="M9 6l6 6l-6 6"></path>'
            . '</svg>'
            . '</button>'
            . '</li>';

        $html .= '</ul>';

        return $html;
    }
}
