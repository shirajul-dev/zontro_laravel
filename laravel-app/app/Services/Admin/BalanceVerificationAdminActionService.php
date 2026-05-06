<?php

namespace App\Services\Admin;

use App\Models\PpBalanceVerification;
use App\Models\PpDevice;

class BalanceVerificationAdminActionService
{
    public function list(array $input, string $brandTimezone): array
    {
        $deviceId = trim((string) ($input['d_id'] ?? ''));
        $searchInput = trim((string) ($input['search_input'] ?? ''));
        $filterStatus = trim((string) ($input['filter_status'] ?? ''));
        $filterStart = trim((string) ($input['filter_start'] ?? ''));
        $filterEnd = trim((string) ($input['filter_end'] ?? ''));

        $page = max(1, (int) ($input['page'] ?? 1));
        $rawShowLimit = (string) ($input['show_limit'] ?? '');
        $showLimit = $rawShowLimit === '' ? 999999 : (int) $rawShowLimit;
        if ($showLimit <= 0) {
            $showLimit = 8;
        }

        $query = PpBalanceVerification::query()
            ->where('device_id', $deviceId)
            ->where('status', '!=', '--');

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
                    ->orWhere('type', 'like', "%{$searchInput}%")
                    ->orWhere('current_balance', 'like', "%{$searchInput}%");
            });
        }

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

        $timezone = $brandTimezone === '' || $brandTimezone === '--' ? 'Asia/Dhaka' : $brandTimezone;

        $response = $rows->map(function (PpBalanceVerification $row) use ($timezone): array {
            $provider = senderWhitelist(null, (string) $row->sender_key);
            $paymentMethod = $provider ? (string) ($provider['name'] ?? '') : '';

            return [
                'id' => (int) $row->id,
                'simslot' => (string) $row->simslot,
                'payment_method' => $paymentMethod,
                'payment_type' => (string) $row->type,
                'current_balance' => $this->roundMoney((string) $row->current_balance, 2),
                'status' => (string) $row->status,
                'created_date' => convertUTCtoUserTZ((string) $row->created_date, $timezone, 'M d, Y h:i A'),
                'updated_date' => convertUTCtoUserTZ((string) $row->updated_date, $timezone, 'M d, Y h:i A'),
            ];
        })->values()->all();

        // Keep legacy count behavior parity: count query does not filter by device_id.
        $countQuery = PpBalanceVerification::query()->where('status', '!=', '--');

        if ($filterStart !== '') {
            $countQuery->where('created_date', '>=', $filterStart . ' 00:00:00');
        }
        if ($filterEnd !== '') {
            $countQuery->where('created_date', '<=', $filterEnd . ' 23:59:59');
        }
        if ($filterStatus !== '') {
            $countQuery->where('status', $filterStatus);
        }
        if ($searchInput !== '') {
            $countQuery->where(function ($q) use ($searchInput): void {
                $q->where('sender_key', 'like', "%{$searchInput}%")
                    ->orWhere('type', 'like', "%{$searchInput}%")
                    ->orWhere('current_balance', 'like', "%{$searchInput}%");
            });
        }

        $totalRecords = $countQuery->count();
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

    public function bulkAction(string $actionId, array $selectedIds): array
    {
        if ($selectedIds === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No balance verifications selected.',
            ];
        }

        $ids = array_values(array_filter(array_map(static fn ($id) => (int) $id, $selectedIds), static fn ($id) => $id > 0));
        if ($ids === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No balance verifications selected.',
            ];
        }

        if ($actionId === 'deleted') {
            PpBalanceVerification::query()->whereIn('id', $ids)->delete();
        } elseif ($actionId === 'activated') {
            PpBalanceVerification::query()->whereIn('id', $ids)->update([
                'status' => 'active',
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);
        } elseif ($actionId === 'inactivated') {
            PpBalanceVerification::query()->whereIn('id', $ids)->update([
                'status' => 'inactive',
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        return [
            'status' => 'true',
            'title' => 'Balance verifications ' . $actionId,
            'message' => 'The selected balance verifications have been ' . $actionId . ' successfully.',
        ];
    }

    public function delete(int $itemId): array
    {
        if ($itemId > 0) {
            PpBalanceVerification::query()->where('id', $itemId)->delete();
        }

        return [
            'status' => 'true',
            'title' => 'Balance Verification Deleted',
            'message' => 'The selected balance verification have been deleted successfully.',
        ];
    }

    public function create(array $input): array
    {
        $deviceId = trim((string) ($input['d_id'] ?? ''));
        $senderKey = trim((string) ($input['sender_key'] ?? ''));
        $paymentType = trim((string) ($input['payment_type'] ?? ''));
        $simslot = trim((string) ($input['simslot'] ?? ''));
        $currentBalance = trim((string) ($input['current_balance'] ?? ''));
        $status = trim((string) ($input['balance_verification_status'] ?? ''));

        if ($senderKey === '' || $paymentType === '' || $simslot === '' || $currentBalance === '' || $status === '') {
            return $this->incomplete();
        }

        if (!in_array($status, ['active', 'inactive'], true)) {
            return $this->incomplete();
        }

        $device = PpDevice::query()->where('device_id', $deviceId)->where('status', 'used')->first();
        if ($device === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $duplicate = PpBalanceVerification::query()
            ->where('device_id', $deviceId)
            ->where('sender_key', $senderKey)
            ->where('type', $paymentType)
            ->exists();

        if ($duplicate) {
            return [
                'status' => 'false',
                'title' => 'Duplicate Entry',
                'message' => 'A record with this info already exists.',
            ];
        }

        PpBalanceVerification::query()->create([
            'device_id' => $deviceId,
            'sender_key' => $senderKey,
            'type' => $paymentType,
            'current_balance' => $this->sanitizeMoney($currentBalance),
            'simslot' => $simslot,
            'status' => $status,
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Balance Verification Created',
            'message' => 'The balance verification has been created successfully.',
        ];
    }

    public function incrementalUpdate(int $itemId, string $balance): array
    {
        $row = PpBalanceVerification::query()->find($itemId);
        if ($row !== null) {
            $row->update([
                'current_balance' => $this->sanitizeMoney($balance === '' ? '0' : $balance),
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        return [
            'status' => 'true',
            'title' => 'Balance Verification Updated',
            'message' => 'The selected balance verification have been updated successfully.',
        ];
    }

    public function infoById(int $itemId): array
    {
        $row = PpBalanceVerification::query()->find($itemId);
        if ($row === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        return [
            'status' => 'true',
            'sender_key' => (string) $row->sender_key,
            'type' => (string) $row->type,
            'current_balance' => $this->roundMoney((string) $row->current_balance, 2),
            'simslot' => (string) $row->simslot,
            'istatus' => (string) $row->status,
        ];
    }

    public function update(array $input): array
    {
        $itemId = (int) ($input['itemID'] ?? 0);
        $senderKey = trim((string) ($input['sender_key'] ?? ''));
        $paymentType = trim((string) ($input['payment_type'] ?? ''));
        $simslot = trim((string) ($input['simslot'] ?? ''));
        $currentBalance = trim((string) ($input['current_balance'] ?? ''));
        $status = trim((string) ($input['balance_verification_status'] ?? ''));

        if ($senderKey === '' || $paymentType === '' || $simslot === '' || $currentBalance === '' || $status === '') {
            return $this->incomplete();
        }

        if (!in_array($status, ['active', 'inactive'], true)) {
            return $this->incomplete();
        }

        $row = PpBalanceVerification::query()->find($itemId);
        if ($row === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $duplicate = PpBalanceVerification::query()
            ->where('device_id', (string) $row->device_id)
            ->where('sender_key', $senderKey)
            ->where('type', $paymentType)
            ->where('id', '!=', $itemId)
            ->exists();

        if ($duplicate) {
            return [
                'status' => 'false',
                'title' => 'Duplicate Entry',
                'message' => 'A record with this info already exists.',
            ];
        }

        $row->update([
            'sender_key' => $senderKey,
            'type' => $paymentType,
            'current_balance' => $this->sanitizeMoney($currentBalance),
            'simslot' => $simslot,
            'status' => $status,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Balance Verification Updated',
            'message' => 'The balance verification has been updated successfully.',
        ];
    }

    private function incomplete(): array
    {
        return [
            'status' => 'false',
            'title' => 'Incomplete Information',
            'message' => 'Please fill in all required fields before proceeding.',
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
