<?php

namespace App\Services\Admin;

use App\Models\PpDevice;
use Carbon\Carbon;

class DeviceAdminActionService
{
    public function list(array $input, string $brandTimezone): array
    {
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

        $query = PpDevice::query()->where('status', 'used');

        if ($filterStart !== '') {
            $query->where('created_date', '>=', $filterStart . ' 00:00:00');
        }

        if ($filterEnd !== '') {
            $query->where('created_date', '<=', $filterEnd . ' 23:59:59');
        }

        if ($filterStatus === 'connected') {
            $query->where('updated_date', '>=', Carbon::now()->subMinutes(6)->format('Y-m-d H:i:s'));
        } elseif ($filterStatus === 'disconnected') {
            $query->where('updated_date', '<', Carbon::now()->subMinutes(6)->format('Y-m-d H:i:s'));
        }

        if ($searchInput !== '') {
            $query->where(function ($q) use ($searchInput): void {
                $q->where('name', 'like', "%{$searchInput}%")
                    ->orWhere('model', 'like', "%{$searchInput}%")
                    ->orWhere('android_level', 'like', "%{$searchInput}%");
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

        $timezone = $brandTimezone === '' || $brandTimezone === '--' ? 'Asia/Dhaka' : $brandTimezone;

        $response = $rows->map(function (PpDevice $row) use ($timezone): array {
            return [
                'id' => (string) $row->device_id,
                'name' => (string) ($row->name ?? ''),
                'model' => (string) ($row->model ?? ''),
                'android_level' => (string) ($row->android_level ?? ''),
                'status' => (string) $row->status,
                'created_date' => convertUTCtoUserTZ((string) $row->created_date, $timezone, 'M d, Y h:i A'),
                'updated_date' => convertUTCtoUserTZ((string) $row->updated_date, $timezone, 'M d, Y h:i A'),
                'last_sync' => ((string) $row->last_sync === '--' || (string) $row->last_sync === '')
                    ? ''
                    : convertUTCtoUserTZ((string) $row->last_sync, $timezone, 'M d, Y h:i A'),
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

    public function connectInfo(string $deviceOwnerId): array
    {
        $otp = $this->generateNumericId();

        $processing = PpDevice::query()
            ->where('status', 'processing')
            ->where('d_id', $deviceOwnerId)
            ->first();

        if ($processing !== null) {
            $processing->update([
                'otp' => $otp,
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);

            return ['status' => 'true', 'otp' => $otp];
        }

        PpDevice::query()->create([
            'd_id' => $deviceOwnerId,
            'device_id' => $this->generateNumericId(),
            'otp' => $otp,
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return ['status' => 'true', 'otp' => $otp];
    }

    public function delete(string $deviceId): void
    {
        if ($deviceId === '') {
            return;
        }

        PpDevice::query()->where('device_id', $deviceId)->delete();
    }

    public function bulkDelete(array $selectedIds): void
    {
        if ($selectedIds === []) {
            return;
        }

        $sanitized = array_values(array_filter(array_map(static fn ($id) => trim((string) $id), $selectedIds), static fn ($id) => $id !== ''));

        if ($sanitized === []) {
            return;
        }

        PpDevice::query()->whereIn('device_id', $sanitized)->delete();
    }

    private function generateNumericId(int $length = 10, int $maxLength = 10): string
    {
        $length = $length > $maxLength ? $maxLength : $length;

        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= (string) random_int(0, 9);
        }

        return $id;
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
