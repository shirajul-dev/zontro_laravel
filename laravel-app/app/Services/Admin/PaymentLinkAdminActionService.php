<?php

namespace App\Services\Admin;

use App\Models\PpCurrency;
use App\Models\PpEnv;
use App\Models\PpPaymentLink;
use App\Models\PpPaymentLinkField;

class PaymentLinkAdminActionService
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

        $query = PpPaymentLink::query()->where('brand_id', $brandId);

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
            $query->where('product_info', 'like', "%{$searchInput}%");
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

        $currencyMap = PpCurrency::query()
            ->where('brand_id', $brandId)
            ->whereIn('code', $rows->pluck('currency')->filter()->unique()->values())
            ->get(['code', 'symbol'])
            ->keyBy('code');

        $timezone = $brandTimezone === '' || $brandTimezone === '--' ? 'Asia/Dhaka' : $brandTimezone;

        $response = $rows->map(function (PpPaymentLink $row) use ($timezone, $currencyMap): array {
            $productInfo = json_decode((string) $row->product_info, true);
            $productInfo = is_array($productInfo) ? $productInfo : [];

            $status = (string) $row->status;
            if ((string) $row->expired_date !== '--' && $this->isExpired((string) $row->expired_date)) {
                $status = 'expired';
            }

            $currency = (string) ($currencyMap->get((string) $row->currency)?->symbol ?? '');

            return [
                'id' => (string) $row->ref,
                'title' => (string) ($productInfo['title'] ?? 'N/A'),
                'description' => (string) ($productInfo['description'] ?? 'N/A'),
                'status' => $status,
                'quantity' => (string) $row->quantity,
                'amount' => $currency . $this->moneyRound((string) $row->amount, 2),
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

    public function bulkAction(string $actionId, array $selectedIds, string $brandId, bool $canDelete, bool $canEdit): array
    {
        if ($selectedIds === []) {
            return [
                'status' => 'false',
                'title' => 'Payment Links Failed',
                'message' => 'No payment links selected.',
            ];
        }

        $ids = array_values(array_filter(array_map(static fn ($id) => (string) $id, $selectedIds), static fn ($id) => $id !== ''));
        if ($ids === []) {
            return [
                'status' => 'false',
                'title' => 'Payment Links Failed',
                'message' => 'No payment links selected.',
            ];
        }

        foreach ($ids as $itemId) {
            $row = PpPaymentLink::query()
                ->where('ref', $itemId)
                ->where('brand_id', $brandId)
                ->first();

            if ($row === null) {
                continue;
            }

            if ($actionId === 'deleted' && $canDelete) {
                PpPaymentLinkField::query()->where('paymentLinkID', $itemId)->delete();
                PpPaymentLink::query()->where('ref', $itemId)->delete();
                continue;
            }

            if ($actionId === 'activated' && $canEdit) {
                $row->update([
                    'status' => 'active',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
                continue;
            }

            if ($actionId === 'inactivated' && $canEdit) {
                $row->update([
                    'status' => 'inactive',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Payment Links ' . $actionId,
            'message' => 'The selected payment links have been ' . $actionId . ' successfully.',
        ];
    }

    public function delete(string $itemId, string $brandId): array
    {
        $row = PpPaymentLink::query()
            ->where('ref', $itemId)
            ->where('brand_id', $brandId)
            ->first();

        if ($row !== null) {
            PpPaymentLinkField::query()->where('paymentLinkID', $itemId)->delete();
            PpPaymentLink::query()->where('ref', $itemId)->delete();
        }

        return [
            'status' => 'true',
            'title' => 'Payment Links Deleted',
            'message' => 'The selected payment link have been deleted successfully.',
        ];
    }

    public function updateDefaultCurrency(string $defaultCurrency, string $brandId): array
    {
        if (function_exists('set_env')) {
            set_env('payment-link-default-currency', $defaultCurrency, $brandId);
        } else {
            $row = PpEnv::query()
                ->where('brand_id', $brandId)
                ->where('option_name', 'payment-link-default-currency')
                ->first();

            if ($row === null) {
                PpEnv::query()->create([
                    'brand_id' => $brandId,
                    'option_name' => 'payment-link-default-currency',
                    'option_value' => $defaultCurrency,
                    'created_date' => now()->format('Y-m-d H:i:s'),
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            } else {
                $row->update([
                    'option_value' => $defaultCurrency,
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Default Currency Updated',
            'message' => 'The default payment link currency has been updated successfully.',
        ];
    }

    private function isExpired(string $date): bool
    {
        if (function_exists('isExpired')) {
            return (bool) isExpired($date);
        }

        $ts = strtotime($date);
        if ($ts === false) {
            return false;
        }

        return $ts < strtotime(date('Y-m-d'));
    }

    private function moneyRound(string $value, int $scale = 2): string
    {
        if (function_exists('money_round')) {
            return (string) money_round($value, $scale);
        }

        return number_format((float) $value, $scale, '.', '');
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

    public function createPaymentLink(array $input, string $brandId): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $quantity = trim((string) ($input['quantity'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $currency = trim((string) ($input['currency'] ?? ''));
        $amount = trim((string) ($input['amount'] ?? ''));
        $expiryDate = trim((string) ($input['expiry_date'] ?? ''));
        $status = trim((string) ($input['status'] ?? ''));

        $items = $input['items'] ?? [];

        if ($expiryDate !== '') {
            $format = 'Y-m-d';
            $d = \DateTime::createFromFormat($format, $expiryDate);
            if (!$d || $d->format($format) !== $expiryDate) {
                return [
                    'status' => 'false',
                    'title' => 'Invalid expiry date format',
                    'message' => 'Please enter the expiry date in the correct format (DD/MM/YYYY).',
                ];
            }
        } else {
            $expiryDate = '--';
        }

        if ($title === '' || $quantity === '' || $description === '' || $currency === '' || $amount === '' || $status === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $paymentLinkId = generateItemID(27, 27);
        $productInfo = json_encode([
            'title' => $title,
            'description' => $description
        ]);

        PpPaymentLink::query()->create([
            'ref' => $paymentLinkId,
            'brand_id' => $brandId,
            'product_info' => $productInfo,
            'amount' => money_sanitize($amount),
            'quantity' => money_sanitize($quantity),
            'currency' => $currency,
            'expired_date' => $expiryDate,
            'status' => $status,
            'created_date' => now()->format('Y-m-d H:i:s'),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        foreach ($items as $uniqueId => $item) {
            $formType = (string) ($item['formType'] ?? '');
            $fieldName = (string) ($item['fieldName'] ?? '');
            $required = (string) ($item['required'] ?? '');
            $fileExtensions = $item['fileExtensions'] ?? [];
            $addOptions = $item['addOptions'] ?? [];

            $value = '--';

            if ($formType === 'file' && is_array($fileExtensions)) {
                $value = implode(', ', $fileExtensions);
            }
            if (($formType === 'select' || $formType === 'checkbox' || $formType === 'radio') && is_array($addOptions)) {
                $value = implode(', ', $addOptions);
            }

            PpPaymentLinkField::query()->create([
                'paymentLinkID' => $paymentLinkId,
                'formType' => $formType,
                'fieldName' => $fieldName,
                'required' => $required,
                'value' => $value,
                'created_date' => now()->format('Y-m-d H:i:s'),
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        return [
            'status' => 'true',
            'title' => 'Payment Link Created',
            'message' => 'The payment link has been created successfully.',
        ];
    }

    public function editPaymentLink(array $input, string $brandId): array
    {
        $paymentLinkId = trim((string) ($input['paymentLinkID'] ?? ''));
        $title = trim((string) ($input['title'] ?? ''));
        $quantity = trim((string) ($input['quantity'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $currency = trim((string) ($input['currency'] ?? ''));
        $amount = trim((string) ($input['amount'] ?? ''));
        $expiryDate = trim((string) ($input['expiry_date'] ?? ''));
        $status = trim((string) ($input['status'] ?? ''));
        
        $deletedItemsStr = trim((string) ($input['deleted_items'] ?? ''));
        $deletedItems = $deletedItemsStr === '' ? [] : explode(',', $deletedItemsStr);

        $items = $input['items'] ?? [];

        if ($expiryDate !== '') {
            $format = 'Y-m-d';
            $d = \DateTime::createFromFormat($format, $expiryDate);
            if (!$d || $d->format($format) !== $expiryDate) {
                return [
                    'status' => 'false',
                    'title' => 'Invalid expiry date format',
                    'message' => 'Please enter the expiry date in the correct format (DD/MM/YYYY).',
                ];
            }
        } else {
            $expiryDate = '--';
        }

        if ($paymentLinkId === '' || $title === '' || $quantity === '' || $description === '' || $currency === '' || $amount === '' || $status === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $link = PpPaymentLink::query()->where('ref', $paymentLinkId)->where('brand_id', $brandId)->first();
        if ($link === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $productInfo = json_encode([
            'title' => $title,
            'description' => $description
        ]);

        $link->update([
            'product_info' => $productInfo,
            'amount' => money_sanitize($amount),
            'quantity' => money_sanitize($quantity),
            'currency' => $currency,
            'expired_date' => $expiryDate,
            'status' => $status,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        foreach ($deletedItems as $itemId) {
            if (trim($itemId) !== '') {
                PpPaymentLinkField::query()->where('id', $itemId)->delete();
            }
        }

        foreach ($items as $uniqueId => $item) {
            $fieldId = trim((string) ($item['fieldID'] ?? ''));
            $formType = (string) ($item['formType'] ?? '');
            $fieldName = (string) ($item['fieldName'] ?? '');
            $required = (string) ($item['required'] ?? '');
            $fileExtensions = $item['fileExtensions'] ?? [];
            $addOptions = $item['addOptions'] ?? [];

            $value = '--';

            if ($formType === 'file' && is_array($fileExtensions)) {
                $value = implode(', ', $fileExtensions);
            }
            if (($formType === 'select' || $formType === 'checkbox' || $formType === 'radio') && is_array($addOptions)) {
                $value = implode(', ', $addOptions);
            }

            if ($fieldId === '') {
                PpPaymentLinkField::query()->create([
                    'paymentLinkID' => $paymentLinkId,
                    'formType' => $formType,
                    'fieldName' => $fieldName,
                    'required' => $required,
                    'value' => $value,
                    'created_date' => now()->format('Y-m-d H:i:s'),
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            } else {
                PpPaymentLinkField::query()->where('id', $fieldId)->update([
                    'formType' => $formType,
                    'fieldName' => $fieldName,
                    'required' => $required,
                    'value' => $value,
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Payment Link Updated',
            'message' => 'The payment link has been updated successfully.',
        ];
    }
}
