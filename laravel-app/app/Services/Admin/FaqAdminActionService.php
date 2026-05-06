<?php

namespace App\Services\Admin;

use App\Models\PpFaq;

class FaqAdminActionService
{
    public function list(array $input, string $brandId, string $brandTimezone): array
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

        $query = PpFaq::query()->where('brand_id', $brandId);

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
                $q->where('title', 'like', "%{$searchInput}%")
                    ->orWhere('description', 'like', "%{$searchInput}%");
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

        $response = $rows->map(function (PpFaq $row) use ($timezone): array {
            return [
                'id' => (int) $row->id,
                'title' => (string) $row->title,
                'description' => (string) $row->description,
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

    public function create(array $input, string $brandId): array
    {
        $faqTitle = trim((string) ($input['faq_title'] ?? ''));
        $faqDescription = trim((string) ($input['faq_description'] ?? ''));
        $faqStatus = trim((string) ($input['faq_status'] ?? ''));

        if ($faqTitle === '' || $faqDescription === '' || $faqStatus === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        PpFaq::query()->create([
            'brand_id' => $brandId,
            'title' => $faqTitle,
            'description' => $faqDescription,
            'status' => $faqStatus,
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'FAQ Created',
            'message' => 'The faq has been created successfully.',
        ];
    }

    public function infoById(int $itemId, string $brandId): array
    {
        $row = PpFaq::query()
            ->where('id', $itemId)
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
            'title' => (string) $row->title,
            'description' => (string) $row->description,
            'fstatus' => (string) $row->status,
        ];
    }

    public function edit(array $input, string $brandId): array
    {
        $faqId = (int) ($input['faq_id'] ?? 0);
        $faqTitle = trim((string) ($input['faq_title'] ?? ''));
        $faqDescription = trim((string) ($input['faq_description'] ?? ''));
        $faqStatus = trim((string) ($input['faq_status'] ?? ''));

        if ($faqId <= 0 || $faqTitle === '' || $faqDescription === '' || $faqStatus === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $row = PpFaq::query()
            ->where('id', $faqId)
            ->where('brand_id', $brandId)
            ->first();

        if ($row === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $row->update([
            'title' => $faqTitle,
            'description' => $faqDescription,
            'status' => $faqStatus,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'FAQ Updated',
            'message' => 'The faq has been updated successfully.',
        ];
    }

    public function bulkAction(string $actionId, array $selectedIds, string $brandId): array
    {
        if ($selectedIds === []) {
            return [
                'status' => 'false',
                'title' => 'FAQ Failed',
                'message' => 'No faqs selected.',
            ];
        }

        $ids = array_values(array_filter(array_map(static fn ($id) => (int) $id, $selectedIds), static fn ($id) => $id > 0));
        if ($ids === []) {
            return [
                'status' => 'false',
                'title' => 'FAQ Failed',
                'message' => 'No faqs selected.',
            ];
        }

        $query = PpFaq::query()->where('brand_id', $brandId)->whereIn('id', $ids);

        if ($actionId === 'deleted') {
            $query->delete();
        } elseif ($actionId === 'activated') {
            $query->update([
                'status' => 'active',
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);
        } elseif ($actionId === 'inactivated') {
            $query->update([
                'status' => 'inactive',
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        return [
            'status' => 'true',
            'title' => 'FAQ ' . $actionId,
            'message' => 'The selected faqs have been ' . $actionId . ' successfully.',
        ];
    }

    public function delete(int $itemId, string $brandId): array
    {
        if ($itemId > 0) {
            PpFaq::query()
                ->where('id', $itemId)
                ->where('brand_id', $brandId)
                ->delete();
        }

        return [
            'status' => 'true',
            'title' => 'FAQ Deleted',
            'message' => 'The selected faq have been deleted successfully.',
        ];
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
