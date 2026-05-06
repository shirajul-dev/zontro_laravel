<?php

namespace App\Services\Admin;

// Ensure legacy money_sanitize and related functions are available
if (!function_exists('money_sanitize')) {
    require_once base_path('pp-content/pp-include/pp-functions.php');
}

use App\Models\PpCurrency;
use App\Models\PpInvoice;
use App\Models\PpInvoiceItem;

class InvoiceAdminActionService
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

        $query = PpInvoice::query()->where('brand_id', $brandId);

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
                $q->where('customer_info', 'like', "%{$searchInput}%")
                    ->orWhere('currency', 'like', "%{$searchInput}%");
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

        $currencyMap = PpCurrency::query()
            ->where('brand_id', $brandId)
            ->whereIn('code', $rows->pluck('currency')->filter()->unique()->values())
            ->get(['code', 'symbol'])
            ->keyBy('code');

        $invoiceRefs = $rows->pluck('ref')->filter()->values();
        $items = PpInvoiceItem::query()
            ->where('brand_id', $brandId)
            ->whereIn('invoice_id', $invoiceRefs)
            ->get()
            ->groupBy('invoice_id');

        $timezone = $brandTimezone === '' || $brandTimezone === '--' ? 'Asia/Dhaka' : $brandTimezone;

        $response = $rows->map(function (PpInvoice $row) use ($timezone, $currencyMap, $items): array {
            $customerInfo = json_decode((string) $row->customer_info, true);
            $customerInfo = is_array($customerInfo) ? $customerInfo : [];

            $invoiceItems = $items->get((string) $row->ref, collect());

            $total = '0';
            $itemsCount = 0;
            foreach ($invoiceItems as $item) {
                $itemsCount++;

                $itemCost = $this->moneyMul((string) $item->amount, (string) $item->quantity);
                $itemTotalCost = $this->moneySub($itemCost, (string) $item->discount);
                $vatAmount = $this->moneyDiv($this->moneyMul($itemTotalCost, (string) $item->vat), '100');
                $itemTotalCostWithVat = $this->moneyAdd($itemTotalCost, $vatAmount);
                $total = $this->moneyAdd($total, $itemTotalCostWithVat);
            }

            $total = $this->moneyAdd($total, (string) $row->shipping);
            $currencySymbol = (string) ($currencyMap->get((string) $row->currency)?->symbol ?? '');

            return [
                'id' => (string) $row->ref,
                'c_id' => (string) ($customerInfo['id'] ?? 'N/A'),
                'name' => (string) ($customerInfo['name'] ?? 'Unknown'),
                'email' => (string) ($customerInfo['email'] ?? ''),
                'mobile' => (string) ($customerInfo['mobile'] ?? ''),
                'status' => (string) $row->status,
                'items' => $itemsCount,
                'amount' => $currencySymbol . $this->moneyRound($total, 2),
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

    public function bulkAction(string $actionId, array $selectedIds, string $brandId, bool $canDelete): array
    {
        if ($selectedIds === []) {
            return [
                'status' => 'false',
                'title' => 'Invoices Failed',
                'message' => 'No invoices selected.',
            ];
        }

        $ids = array_values(array_filter(array_map(static fn($id) => (string) $id, $selectedIds), static fn($id) => $id !== ''));
        if ($ids === []) {
            return [
                'status' => 'false',
                'title' => 'Invoices Failed',
                'message' => 'No invoices selected.',
            ];
        }

        foreach ($ids as $itemId) {
            $row = PpInvoice::query()
                ->where('ref', $itemId)
                ->where('brand_id', $brandId)
                ->first();

            if ($row === null) {
                continue;
            }

            if ($actionId === 'deleted' && $canDelete) {
                PpInvoiceItem::query()->where('invoice_id', $itemId)->delete();
                PpInvoice::query()->where('ref', $itemId)->delete();
            }
        }

        return [
            'status' => 'true',
            'title' => 'Invoices ' . $actionId,
            'message' => 'The selected invoices have been ' . $actionId . ' successfully.',
        ];
    }

    public function delete(string $itemId, string $brandId): array
    {
        $row = PpInvoice::query()
            ->where('ref', $itemId)
            ->where('brand_id', $brandId)
            ->first();

        if ($row !== null) {
            PpInvoiceItem::query()->where('invoice_id', $itemId)->delete();
            PpInvoice::query()->where('ref', $itemId)->delete();
        }

        return [
            'status' => 'true',
            'title' => 'Invoice Deleted',
            'message' => 'The selected invoice have been deleted successfully.',
        ];
    }

    private function moneyAdd(string $a, string $b): string
    {
        if (function_exists('money_add')) {
            return (string) money_add($a, $b);
        }

        return (string) ((float) $a + (float) $b);
    }

    private function moneySub(string $a, string $b): string
    {
        if (function_exists('money_sub')) {
            return (string) money_sub($a, $b);
        }

        return (string) ((float) $a - (float) $b);
    }

    private function moneyMul(string $a, string $b): string
    {
        if (function_exists('money_mul')) {
            return (string) money_mul($a, $b);
        }

        return (string) ((float) $a * (float) $b);
    }

    private function moneyDiv(string $a, string $b): string
    {
        if (function_exists('money_div')) {
            return (string) money_div($a, $b);
        }

        if ((float) $b == 0.0) {
            return '0';
        }

        return (string) ((float) $a / (float) $b);
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

    public function createInvoice(array $input, string $brandId, string $brandTimezone): array
    {
        $customer = $input['customer'] ?? $input['customers'] ?? [];
        $currency = trim((string) ($input['currency'] ?? ''));
        $dueDate = trim((string) ($input['due-date'] ?? $input['due_date'] ?? ''));
        $shipping = trim((string) ($input['shipping'] ?? ''));
        $status = trim((string) ($input['status'] ?? ''));
        $note = trim((string) ($input['note'] ?? '--'));
        $privateNote = trim((string) ($input['private-note-content'] ?? '--'));

        $itemDescriptions = $input['item-description'] ?? [];
        $itemQuantities = $input['item-quantity'] ?? [];
        $itemAmounts = $input['item-amount'] ?? [];
        $itemDiscounts = $input['item-discount'] ?? [];
        $itemVats = $input['item-vat'] ?? [];

        if (!is_array($itemDescriptions)) {
            $itemDescriptions = (array) $itemDescriptions;
        }
        if (!is_array($itemQuantities)) {
            $itemQuantities = (array) $itemQuantities;
        }
        if (!is_array($itemAmounts)) {
            $itemAmounts = (array) $itemAmounts;
        }
        if (!is_array($itemDiscounts)) {
            $itemDiscounts = (array) $itemDiscounts;
        }
        if (!is_array($itemVats)) {
            $itemVats = (array) $itemVats;
        }
        if (!is_array($customer)) {
            $customer = (array) $customer;
        }

        if ($note === '') {
            $note = '--';
        }
        if ($privateNote === '') {
            $privateNote = '--';
        }

        if ($dueDate !== '') {
            $format = 'Y-m-d';
            $d = \DateTime::createFromFormat($format, $dueDate);
            if (!$d || $d->format($format) !== $dueDate) {
                return [
                    'status' => 'false',
                    'title' => 'Invalid due date format',
                    'message' => 'Please enter the due date in the correct format (DD/MM/YYYY).',
                ];
            }
        } else {
            $dueDate = '--';
        }

        if ($currency === '' || $status === '' || $shipping === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        if (count($itemDescriptions) === 0) {
            return [
                'status' => 'false',
                'title' => 'Add Item Required',
                'message' => 'Please add at least 1 item to create an invoice.',
            ];
        }

        $allInvoices = [];
        $insertResult = false;

        foreach ($customer as $customerId) {
            $custQuery = \Illuminate\Support\Facades\DB::table('pp_customer')
                ->where('brand_id', $brandId)
                ->where(function ($q) use ($customerId) {
                    $q->where('ref', $customerId)->orWhere('email', $customerId);
                })->first();

            if ($custQuery !== null) {
                $invoiceId = generateItemID(27, 27);
                $customerInfo = json_encode([
                    'id' => $custQuery->ref,
                    'name' => $custQuery->name,
                    'email' => $custQuery->email,
                    'mobile' => $custQuery->mobile
                ]);

                $invoiceItemsArray = [];
                for ($i = 0; $i < count($itemDescriptions); $i++) {
                    $desc = (string) ($itemDescriptions[$i] ?? '');
                    $qty = (string) ($itemQuantities[$i] ?? '0');
                    $amt = (string) ($itemAmounts[$i] ?? '0');
                    $disc = (string) ($itemDiscounts[$i] ?? '0');
                    $vat = (string) ($itemVats[$i] ?? '0');

                    PpInvoiceItem::query()->create([
                        'invoice_id' => $invoiceId,
                        'brand_id' => $brandId,
                        'description' => $desc,
                        'amount' => money_sanitize($amt),
                        'quantity' => money_sanitize($qty),
                        'discount' => money_sanitize($disc),
                        'vat' => money_sanitize($vat),
                        'created_date' => now()->format('Y-m-d H:i:s'),
                        'updated_date' => now()->format('Y-m-d H:i:s'),
                    ]);

                    $invoiceItemsArray[] = [
                        'description' => $desc,
                        'amount' => $this->moneyRound(money_sanitize($amt)),
                        'quantity' => $this->moneyRound(money_sanitize($qty)),
                        'discount' => $this->moneyRound(money_sanitize($disc)),
                        'vat' => $this->moneyRound(money_sanitize($vat)),
                    ];
                }

                PpInvoice::query()->create([
                    'ref' => $invoiceId,
                    'brand_id' => $brandId,
                    'customer_info' => $customerInfo,
                    'currency' => $currency,
                    'due_date' => $dueDate,
                    'shipping' => money_sanitize($shipping),
                    'status' => $status,
                    'note' => $note,
                    'private_note' => $privateNote,
                    'created_date' => now()->format('Y-m-d H:i:s'),
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);

                $timezone = $brandTimezone === '' || $brandTimezone === '--' ? 'Asia/Dhaka' : $brandTimezone;
                $allInvoices['invoice_' . $invoiceId] = [
                    'customer_info' => $customerInfo,
                    'invoice_info' => [
                        'invoice_id' => $invoiceId,
                        'brand_id' => $brandId,
                        'currency' => $currency,
                        'due_date' => $dueDate,
                        'shipping' => $this->moneyRound(money_sanitize($shipping)),
                        'status' => $status,
                        'note' => $note,
                        'private_note' => $privateNote,
                        'created_date' => convertUTCtoUserTZ(now()->format('Y-m-d H:i:s'), $timezone, "M d, Y h:i A"),
                        'updated_date' => convertUTCtoUserTZ(now()->format('Y-m-d H:i:s'), $timezone, "M d, Y h:i A"),
                    ],
                    'invoice_items' => $invoiceItemsArray
                ];

                $insertResult = true;
            }
        }

        if ($insertResult) {
            if (!empty($allInvoices) && function_exists('do_action')) {
                do_action('invoices.created', $allInvoices);
            }

            return [
                'status' => 'true',
                'title' => 'Invoice Created',
                'message' => 'The invoice has been created successfully.',
            ];
        }

        return [
            'status' => 'false',
            'title' => 'Incomplete Information',
            'message' => 'Please fill in all required fields before proceeding.',
        ];
    }

    public function editInvoice(array $input, string $brandId, string $brandTimezone): array
    {
        $invoiceId = trim((string) ($input['invoiceID'] ?? $input['invoice_id'] ?? ''));
        $currency = trim((string) ($input['currency'] ?? ''));
        $dueDate = trim((string) ($input['due-date'] ?? $input['due_date'] ?? ''));
        $shipping = trim((string) ($input['shipping'] ?? ''));
        $status = trim((string) ($input['status'] ?? ''));
        $note = trim((string) ($input['note'] ?? '--'));
        $privateNote = trim((string) ($input['private-note-content'] ?? '--'));

        $deletedItemsStr = trim((string) ($input['deleted_items'] ?? ''));
        $deletedItems = $deletedItemsStr === '' ? [] : explode(',', $deletedItemsStr);

        $itemDescriptions = $input['item-description'] ?? [];
        $itemQuantities = $input['item-quantity'] ?? [];
        $itemAmounts = $input['item-amount'] ?? [];
        $itemDiscounts = $input['item-discount'] ?? [];
        $itemVats = $input['item-vat'] ?? [];
        $itemIds = $input['item-id'] ?? [];

        if (!is_array($itemDescriptions)) {
            $itemDescriptions = (array) $itemDescriptions;
        }
        if (!is_array($itemQuantities)) {
            $itemQuantities = (array) $itemQuantities;
        }
        if (!is_array($itemAmounts)) {
            $itemAmounts = (array) $itemAmounts;
        }
        if (!is_array($itemDiscounts)) {
            $itemDiscounts = (array) $itemDiscounts;
        }
        if (!is_array($itemVats)) {
            $itemVats = (array) $itemVats;
        }
        if (!is_array($itemIds)) {
            $itemIds = (array) $itemIds;
        }

        if ($note === '') {
            $note = '--';
        }
        if ($privateNote === '') {
            $privateNote = '--';
        }

        if ($dueDate !== '') {
            $format = 'Y-m-d';
            $d = \DateTime::createFromFormat($format, $dueDate);
            if (!$d || $d->format($format) !== $dueDate) {
                return [
                    'status' => 'false',
                    'title' => 'Invalid due date format',
                    'message' => 'Please enter the due date in the correct format (DD/MM/YYYY).',
                ];
            }
        } else {
            $dueDate = '--';
        }

        if ($currency === '' || $status === '' || $shipping === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $invoice = PpInvoice::query()->where('ref', $invoiceId)->where('brand_id', $brandId)->first();
        if ($invoice === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $invoice->update([
            'currency' => $currency,
            'due_date' => $dueDate,
            'shipping' => money_sanitize($shipping),
            'status' => $status,
            'note' => $note,
            'private_note' => $privateNote,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        foreach ($deletedItems as $itemId) {
            if (trim($itemId) !== '') {
                PpInvoiceItem::query()->where('id', $itemId)->delete();
            }
        }

        $invoiceItemsArray = [];
        for ($i = 0; $i < count($itemDescriptions); $i++) {
            $desc = (string) ($itemDescriptions[$i] ?? '');
            $qty = (string) ($itemQuantities[$i] ?? '0');
            $amt = (string) ($itemAmounts[$i] ?? '0');
            $disc = (string) ($itemDiscounts[$i] ?? '0');
            $vat = (string) ($itemVats[$i] ?? '0');
            $itemId = (string) ($itemIds[$i] ?? '');

            if ($itemId !== '') {
                PpInvoiceItem::query()->where('id', $itemId)->update([
                    'description' => $desc,
                    'amount' => money_sanitize($amt),
                    'quantity' => money_sanitize($qty),
                    'discount' => money_sanitize($disc),
                    'vat' => money_sanitize($vat),
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            } else {
                PpInvoiceItem::query()->create([
                    'invoice_id' => $invoiceId,
                    'brand_id' => $brandId,
                    'description' => $desc,
                    'amount' => money_sanitize($amt),
                    'quantity' => money_sanitize($qty),
                    'discount' => money_sanitize($disc),
                    'vat' => money_sanitize($vat),
                    'created_date' => now()->format('Y-m-d H:i:s'),
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }

            $invoiceItemsArray[] = [
                'description' => $desc,
                'amount' => $this->moneyRound(money_sanitize($amt)),
                'quantity' => $this->moneyRound(money_sanitize($qty)),
                'discount' => $this->moneyRound(money_sanitize($disc)),
                'vat' => $this->moneyRound(money_sanitize($vat)),
            ];
        }

        $timezone = $brandTimezone === '' || $brandTimezone === '--' ? 'Asia/Dhaka' : $brandTimezone;
        $allInvoices = [
            'customer_info' => $invoice->customer_info,
            'invoice_info' => [
                'invoice_id' => $invoiceId,
                'brand_id' => $brandId,
                'currency' => $currency,
                'due_date' => $dueDate,
                'shipping' => $this->moneyRound(money_sanitize($shipping)),
                'status' => $status,
                'note' => $note,
                'private_note' => $privateNote,
                'created_date' => convertUTCtoUserTZ((string) $invoice->created_date, $timezone, "M d, Y h:i A"),
                'updated_date' => convertUTCtoUserTZ(now()->format('Y-m-d H:i:s'), $timezone, "M d, Y h:i A"),
            ],
            'invoice_items' => $invoiceItemsArray
        ];

        if (function_exists('do_action')) {
            do_action('invoices.updated', $allInvoices);
        }

        return [
            'status' => 'true',
            'title' => 'Invoice Updated',
            'message' => 'The invoice has been updated successfully.',
        ];
    }

    public function manageInvoiceStatus(array $input, string $brandId, string $brandTimezone): array
    {
        $invoiceId = trim((string) ($input['invoiceID'] ?? ''));
        $status = trim((string) ($input['status'] ?? ''));

        if ($status === '' || $invoiceId === '') {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $invoice = PpInvoice::query()->where('ref', $invoiceId)->where('brand_id', $brandId)->first();
        if ($invoice === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $invoice->update([
            'status' => $status,
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        $invoiceItemsArray = [];
        $items = PpInvoiceItem::query()->where('invoice_id', $invoiceId)->where('brand_id', $brandId)->get();
        foreach ($items as $item) {
            $invoiceItemsArray[] = [
                'description' => (string) $item->description,
                'amount' => $this->moneyRound((string) $item->amount),
                'quantity' => $this->moneyRound((string) $item->quantity),
                'discount' => $this->moneyRound((string) $item->discount),
                'vat' => $this->moneyRound((string) $item->vat),
            ];
        }

        $timezone = $brandTimezone === '' || $brandTimezone === '--' ? 'Asia/Dhaka' : $brandTimezone;
        $allInvoices = [
            'customer_info' => $invoice->customer_info,
            'invoice_info' => [
                'invoice_id' => $invoiceId,
                'brand_id' => $brandId,
                'currency' => (string) $invoice->currency,
                'due_date' => (string) $invoice->due_date,
                'shipping' => $this->moneyRound((string) $invoice->shipping),
                'status' => $status,
                'note' => (string) $invoice->note,
                'private_note' => (string) $invoice->private_note,
                'created_date' => convertUTCtoUserTZ((string) $invoice->created_date, $timezone, "M d, Y h:i A"),
                'updated_date' => convertUTCtoUserTZ(now()->format('Y-m-d H:i:s'), $timezone, "M d, Y h:i A"),
            ],
            'invoice_items' => $invoiceItemsArray
        ];

        if (function_exists('do_action')) {
            do_action('invoices.updated.status', $allInvoices);
        }

        return [
            'status' => 'true',
            'title' => 'Invoice Updated',
            'message' => 'The invoice has been updated successfully.',
        ];
    }
}
