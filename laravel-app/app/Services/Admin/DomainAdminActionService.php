<?php

namespace App\Services\Admin;

use App\Models\PpDomain;

class DomainAdminActionService
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

        $query = PpDomain::query()->where('status', '!=', '');

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
            $query->where('domain', 'like', "%{$searchInput}%");
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

        $response = $rows->map(function (PpDomain $row) use ($timezone): array {
            return [
                'id' => (int) $row->id,
                'domain' => (string) $row->domain,
                'status' => (string) $row->status,
                'created_date' => convertUTCtoUserTZ((string) $row->created_date, $timezone, 'M d, Y h:i A'),
                'updated_date' => convertUTCtoUserTZ((string) $row->updated_date, $timezone, 'M d, Y h:i A'),
            ];
        })->values()->all();

        $countQuery = PpDomain::query();
        if ($searchInput !== '') {
            $countQuery->where('domain', 'like', "%{$searchInput}%");
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

    public function infoById(int $itemId): array
    {
        $row = PpDomain::query()->find($itemId);
        if ($row === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        return [
            'status' => 'true',
            'domain' => (string) $row->domain,
            'istatus' => (string) $row->status,
        ];
    }

    public function create(array $input): array
    {
        $domainName = trim((string) ($input['domain_name'] ?? ''));
        $domainStatus = trim((string) ($input['domain_status'] ?? ''));

        if ($domainName === '' || $domainStatus === '') {
            return $this->incomplete();
        }

        $normalized = $this->normalizeDomain($domainName);
        if ($normalized === false) {
            return [
                'status' => 'false',
                'title' => 'Invalid Domain',
                'message' => 'Please enter a valid domain or domain URL.',
            ];
        }

        $duplicate = PpDomain::query()->where('domain', $normalized)->exists();
        if ($duplicate) {
            return [
                'status' => 'false',
                'title' => 'Duplicate Domain',
                'message' => 'A domain with this name already exists. Please choose a different name.',
            ];
        }

        PpDomain::query()->create([
            'domain' => $normalized,
            'status' => $domainStatus,
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Domain Whitelisted',
            'message' => 'The domain has been whitelisted successfully.',
        ];
    }

    public function edit(array $input): array
    {
        $domainId = (int) ($input['domain_id'] ?? 0);
        $domainName = trim((string) ($input['domain_name'] ?? ''));
        $domainStatus = trim((string) ($input['domain_status'] ?? ''));

        if ($domainId <= 0 || $domainName === '' || $domainStatus === '') {
            return $this->incomplete();
        }

        $normalized = $this->normalizeDomain($domainName);
        if ($normalized === false) {
            return [
                'status' => 'false',
                'title' => 'Invalid Domain',
                'message' => 'Please enter a valid domain or domain URL.',
            ];
        }

        $row = PpDomain::query()->find($domainId);
        if ($row === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $existing = PpDomain::query()->where('domain', $normalized)->first();
        if ($existing !== null && (int) $existing->id !== $domainId) {
            return [
                'status' => 'false',
                'title' => 'Duplicate Domain',
                'message' => 'A domain with this name already exists. Please choose a different name.',
            ];
        }

        $row->update([
            'domain' => $normalized,
            'status' => $domainStatus,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Domain Updated',
            'message' => 'The domain has been updated successfully.',
        ];
    }

    public function delete(int $itemId): array
    {
        if ($itemId > 0) {
            PpDomain::query()->where('id', $itemId)->delete();
        }

        return [
            'status' => 'true',
            'title' => 'Domain Deleted',
            'message' => 'The selected domain have been deleted successfully.',
        ];
    }

    public function bulkAction(string $actionId, array $selectedIds, bool $canDelete, bool $canEdit): array
    {
        if ($selectedIds === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No domains selected.',
            ];
        }

        $ids = array_values(array_filter(array_map(static fn ($id) => (int) $id, $selectedIds), static fn ($id) => $id > 0));
        if ($ids === []) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'No domains selected.',
            ];
        }

        foreach ($ids as $id) {
            $row = PpDomain::query()->find($id);
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

            if ($actionId === 'inactive' && $canEdit) {
                $row->update([
                    'status' => 'inactive',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Domains ' . $actionId,
            'message' => 'The selected domains have been ' . $actionId . ' successfully.',
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

    private function normalizeDomain(string $value): string|false
    {
        if (function_exists('getDomainValue')) {
            return getDomainValue($value);
        }

        $domain = trim($value);
        if ($domain === '') {
            return false;
        }

        if (str_contains($domain, '://')) {
            $host = parse_url($domain, PHP_URL_HOST);
            $domain = is_string($host) ? $host : '';
        }

        $domain = strtolower(trim($domain));
        $domain = preg_replace('/^www\./', '', $domain);
        if (!is_string($domain) || $domain === '') {
            return false;
        }

        return filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) ? $domain : false;
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
