<?php

namespace App\Services\Admin;

use App\Models\PpCustomer;

class CustomerAdminActionService
{
    public function list(array $input, string $brandId, string $brandTimezone): array
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

        $query = PpCustomer::query()->where('brand_id', $brandId);

        if ($tabType !== '' && $tabType !== 'all') {
            $query->where('inserted_via', $tabType);
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
                $q->where('name', 'like', "%{$searchInput}%")
                    ->orWhere('email', 'like', "%{$searchInput}%")
                    ->orWhere('mobile', 'like', "%{$searchInput}%");
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

        $response = $rows->map(function (PpCustomer $row) use ($timezone): array {
            return [
                'id' => (string) $row->ref,
                'name' => (string) $row->name,
                'email' => (string) $row->email,
                'mobile' => (string) $row->mobile,
                'status' => (string) $row->status,
                'suspend_reason' => ((string) $row->suspend_reason === '--') ? '' : (string) $row->suspend_reason,
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

    public function create(array $input, string $brandId): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $mobile = trim((string) ($input['mobile'] ?? ''));
        $status = trim((string) ($input['status'] ?? ''));
        $suspendReason = (string) ($input['suspend_reason'] ?? '');

        if ($name === '' || $email === '' || $mobile === '' || $status === '') {
            return $this->incomplete();
        }

        if ($status !== 'active' && $status !== 'suspend') {
            return $this->incomplete();
        }

        if ($suspendReason === '') {
            // Keep legacy behavior parity (legacy code compares instead of assigning).
            $suspendReason = '';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => 'false',
                'title' => 'Invalid Email',
                'message' => 'Please enter a valid email address.',
            ];
        }

        $duplicate = PpCustomer::query()
            ->where('brand_id', $brandId)
            ->where('email', $email)
            ->exists();

        if ($duplicate) {
            return [
                'status' => 'false',
                'title' => 'Duplicate Customer',
                'message' => 'A customer with this email address already exists. Please choose a different email address.',
            ];
        }

        $ref = $this->generateItemId();

        PpCustomer::query()->create([
            'ref' => $ref,
            'brand_id' => $brandId,
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'status' => $status,
            'suspend_reason' => $suspendReason,
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Customer Created',
            'message' => 'The customer has been created successfully.',
        ];
    }

    public function bulkAction(string $actionId, array $selectedIds, string $brandId, bool $canDelete, bool $canEdit): array
    {
        if ($selectedIds === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No customers selected.',
            ];
        }

        $ids = array_values(array_filter(array_map(static fn ($id) => (string) $id, $selectedIds), static fn ($id) => $id !== ''));
        if ($ids === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No customers selected.',
            ];
        }

        foreach ($ids as $id) {
            $row = PpCustomer::query()
                ->where('ref', $id)
                ->where('brand_id', $brandId)
                ->first();

            if ($row === null) {
                continue;
            }

            if ($actionId === 'deleted' && $canDelete) {
                $row->delete();
                continue;
            }

            if ($actionId === 'activated' && $canEdit) {
                $row->update([
                    'status' => 'active',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
                continue;
            }

            if ($actionId === 'suspended' && $canEdit) {
                $row->update([
                    'status' => 'suspend',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Customers ' . $actionId,
            'message' => 'The selected customers have been ' . $actionId . ' successfully.',
        ];
    }

    public function delete(string $itemId, string $brandId): array
    {
        PpCustomer::query()
            ->where('ref', $itemId)
            ->where('brand_id', $brandId)
            ->delete();

        return [
            'status' => 'true',
            'title' => 'Customer Deleted',
            'message' => 'The selected customer have been deleted successfully.',
        ];
    }

    public function infoById(string $itemId, string $brandId): array
    {
        $row = PpCustomer::query()
            ->where('ref', $itemId)
            ->where('brand_id', $brandId)
            ->first();

        if ($row === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        return [
            'status' => 'true',
            'name' => (string) $row->name,
            'email' => (string) $row->email,
            'mobile' => (string) $row->mobile,
            'istatus' => (string) $row->status,
            'suspend_reason' => ((string) $row->suspend_reason === '--') ? '' : (string) $row->suspend_reason,
        ];
    }

    public function edit(array $input, string $brandId): array
    {
        $customerId = trim((string) ($input['customer_id'] ?? ''));
        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $mobile = trim((string) ($input['mobile'] ?? ''));
        $status = trim((string) ($input['status'] ?? ''));
        $suspendReason = trim((string) ($input['suspend_reason'] ?? ''));

        if ($suspendReason === '') {
            $suspendReason = '--';
        }

        if ($status !== 'active' && $status !== 'suspend') {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        if ($customerId === '' || $name === '' || $email === '' || $mobile === '') {
            return $this->incomplete();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => 'false',
                'title' => 'Invalid Email',
                'message' => 'Please enter a valid email address.',
            ];
        }

        $row = PpCustomer::query()
            ->where('brand_id', $brandId)
            ->where('ref', $customerId)
            ->first();

        if ($row === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid Customer ID',
            ];
        }

        if ((string) $row->email !== $email) {
            $duplicate = PpCustomer::query()
                ->where('brand_id', $brandId)
                ->where('email', $email)
                ->exists();

            if ($duplicate) {
                return [
                    'status' => 'false',
                    'title' => 'Duplicate Customer',
                    'message' => 'A customer with this email address already exists. Please choose a different email address.',
                ];
            }
        }

        $row->update([
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'status' => $status,
            'suspend_reason' => $suspendReason,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Customer Updated',
            'message' => 'The customer has been updated successfully.',
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

    private function generateItemId(): string
    {
        if (function_exists('generateItemID')) {
            return (string) generateItemID();
        }

        return 'CUST' . strtoupper(bin2hex(random_bytes(6)));
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
