<?php

namespace App\Services\Admin;

use App\Models\PpCurrency;
use App\Models\PpGateway;
use App\Models\PpTransaction;
use App\Models\PpWebhookLog;

class TransactionAdminActionService
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

        $query = PpTransaction::query()
            ->where('brand_id', $brandId)
            ->where('status', '!=', 'initiated');

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
                    ->orWhere('trx_id', 'like', "%{$searchInput}%")
                    ->orWhere('gateway_slug', 'like', "%{$searchInput}%")
                    ->orWhere('sender', 'like', "%{$searchInput}%");
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

        $gatewayMap = PpGateway::query()
            ->where('brand_id', $brandId)
            ->whereIn('gateway_id', $rows->pluck('gateway_id')->filter()->unique()->values())
            ->get(['gateway_id', 'name'])
            ->keyBy('gateway_id');

        $timezone = $brandTimezone === '' || $brandTimezone === '--' ? 'Asia/Dhaka' : $brandTimezone;

        $response = $rows->map(function (PpTransaction $row) use ($timezone, $currencyMap, $gatewayMap): array {
            $customerInfo = json_decode((string) $row->customer_info, true);
            $customerInfo = is_array($customerInfo) ? $customerInfo : [];

            $currencySymbol = (string) ($currencyMap->get((string) $row->currency)?->symbol ?? '');
            $gatewayName = (string) ($gatewayMap->get((string) $row->gateway_id)?->name ?? '');

            $amount = $this->sanitizeMoney($row->amount);
            $processingFee = $this->sanitizeMoney($row->processing_fee);
            $discount = $this->sanitizeMoney($row->discount_amount);
            $net = ($amount + $processingFee) - $discount;

            return [
                'id' => (string) $row->ref,
                'c_id' => (string) ($customerInfo['id'] ?? 'N/A'),
                'name' => (string) ($customerInfo['name'] ?? 'Unknown'),
                'email' => (string) ($customerInfo['email'] ?? ''),
                'mobile' => (string) ($customerInfo['mobile'] ?? ''),
                'status' => (string) $row->status,
                'gateway' => $gatewayName,
                'trx_id' => ((string) $row->trx_id === '--' || (string) $row->trx_id === '') ? '' : (string) $row->trx_id,
                'net_amount' => $currencySymbol . $this->roundMoney($net),
                'amount' => $currencySymbol . $this->roundMoney($amount),
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

    public function bulkAction(
        string $actionId,
        array $selectedIds,
        string $brandId,
        string $brandTimezone,
        bool $canDelete,
        bool $canApprove,
        bool $canRefund,
        bool $canCancel,
        bool $canSendIpn
    ): array {
        if ($selectedIds === []) {
            return [
                'status' => 'false',
                'title' => 'Transactions Failed',
                'message' => 'No transactions selected.',
            ];
        }

        $ids = array_values(array_filter(array_map(static fn ($id) => (string) $id, $selectedIds), static fn ($id) => $id !== ''));
        if ($ids === []) {
            return [
                'status' => 'false',
                'title' => 'Transactions Failed',
                'message' => 'No transactions selected.',
            ];
        }

        $actionsId = $actionId;
        $allTransactions = [];
        $jobs = [];

        foreach ($ids as $id) {
            $row = PpTransaction::query()
                ->where('ref', $id)
                ->where('brand_id', $brandId)
                ->first();

            if ($row === null) {
                continue;
            }

            if ($actionId === 'deleted' && $canDelete) {
                $row->delete();
            }

            if ($actionId === 'approved' && $canApprove) {
                $row->update([
                    'status' => 'completed',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }

            if ($actionId === 'refunded' && $canRefund) {
                $row->update([
                    'status' => 'refunded',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }

            if ($actionId === 'canceled' && $canCancel) {
                $row->update([
                    'status' => 'canceled',
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
            }

            if ($actionId === 'ipnsend' && $canSendIpn) {
                $actionsId = 'IPN Triggered';

                if ((string) $row->webhook_url !== '--' && (string) $row->webhook_url !== '') {
                    $jobs[] = [
                        'id' => random_int(1, 1000000000),
                        'url' => (string) $row->webhook_url,
                        'payload' => $this->buildIpnPayload($row, $brandId, $brandTimezone, true),
                    ];
                }
            }

            if (in_array($actionId, ['refunded', 'canceled', 'approved'], true)) {
                $allTransactions[] = $this->buildIpnPayload($row, $brandId, $brandTimezone, true);
            }
        }

        if ($jobs !== [] && function_exists('sendIPNMulti')) {
            $results = sendIPNMulti($jobs);
            if (!is_array($results)) {
                $results = [];
            }

            foreach ($jobs as $job) {
                $code = (int) ($results[$job['id']] ?? 0);
                if ($code !== 200) {
                    PpWebhookLog::query()->create([
                        'ref' => (string) random_int(1, 1000000000),
                        'brand_id' => $brandId,
                        'payload' => json_encode($job['payload']),
                        'url' => (string) $job['url'],
                        'created_date' => now()->format('Y-m-d H:i:s'),
                        'updated_date' => now()->format('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        if ($allTransactions !== [] && function_exists('do_action')) {
            do_action('transactions.updated', $allTransactions);
        }

        return [
            'status' => 'true',
            'title' => 'Transactions ' . $actionsId,
            'message' => 'The selected transactions have been ' . $actionsId . ' successfully.',
        ];
    }

    public function delete(string $itemId, string $brandId): array
    {
        PpTransaction::query()
            ->where('ref', $itemId)
            ->where('brand_id', $brandId)
            ->delete();

        return [
            'status' => 'true',
            'title' => 'Transaction Deleted',
            'message' => 'The selected Transaction have been deleted successfully.',
        ];
    }

    public function sendIpn(string $itemId, string $brandId, string $brandTimezone): array
    {
        $row = PpTransaction::query()
            ->where('ref', $itemId)
            ->where('brand_id', $brandId)
            ->first();

        if ($row !== null) {
            if ((string) $row->webhook_url !== '--' && (string) $row->webhook_url !== '' && function_exists('sendIPN')) {
                $payload = $this->buildIpnPayload($row, $brandId, $brandTimezone, false);
                sendIPN((string) $row->webhook_url, $payload);
            }
        }

        return [
            'status' => 'true',
            'title' => 'Transaction IPN Triggered',
            'message' => 'The IPN for the transaction has been sent successfully.',
        ];
    }

    private function buildIpnPayload(PpTransaction $row, string $brandId, string $brandTimezone, bool $useCustomerInfo): array
    {
        $metadata = json_decode((string) $row->metadata, true);
        $metadata = is_array($metadata) ? $metadata : [];

        $responseGateway = PpGateway::query()
            ->where('brand_id', $brandId)
            ->where('gateway_id', (string) $row->gateway_id)
            ->first();

        $gateway = (string) ($responseGateway?->name ?? '');

        $customerInfo = json_decode((string) $row->customer_info, true);
        $customerInfo = is_array($customerInfo) ? $customerInfo : [];

        $amount = $this->sanitizeMoney($row->amount);
        $processingFee = $this->sanitizeMoney($row->processing_fee);
        $discount = $this->sanitizeMoney($row->discount_amount);
        $net = ($amount + $processingFee) - $discount;

        $timezone = $brandTimezone === '' || $brandTimezone === '--' ? 'Asia/Dhaka' : $brandTimezone;

        if ($useCustomerInfo) {
            $fullName = (string) ($customerInfo['name'] ?? 'N/A');
            $emailAddress = (string) ($customerInfo['email'] ?? 'N/A');
            $mobileNumber = (string) ($customerInfo['mobile'] ?? 'N/A');
        } else {
            // Keep parity with legacy single-IPN action behavior.
            $fullName = (string) ($row->name ?? 'N/A');
            $emailAddress = (string) ($row->email ?? 'N/A');
            $mobileNumber = (string) ($row->mobile ?? 'N/A');
        }

        return [
            'pp_id' => (string) $row->ref,
            'full_name' => $fullName,
            'email_address' => $emailAddress,
            'mobile_number' => $mobileNumber,
            'gateway' => $gateway,
            'amount' => $this->roundMoney($amount),
            'fee' => $this->roundMoney($processingFee),
            'discount_amount' => $this->roundMoney($discount),
            'total' => $this->roundMoney($net),
            'local_net_amount' => $this->roundMoney($this->sanitizeMoney($row->local_net_amount)),
            'currency' => (string) $row->currency,
            'local_currency' => (string) $row->local_currency,
            'metadata' => $metadata,
            'sender' => (string) $row->sender,
            'transaction_id' => (string) $row->trx_id,
            'status' => (string) $row->status,
            'date' => convertUTCtoUserTZ((string) $row->created_date, $timezone, 'M d, Y h:i A'),
        ];
    }

    private function sanitizeMoney(string|int|float|null $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function roundMoney(float|int $amount, int $decimals = 2): string
    {
        return number_format((float) $amount, $decimals, '.', '');
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
