<?php

namespace App\Services\Admin;

use App\Models\PpCurrency;
use Illuminate\Support\Facades\Http;

class CurrencyAdminActionService
{
    public function list(array $input, string $brandId, string $brandTimezone, string $brandCurrencyCode): array
    {
        $searchInput = trim((string) ($input['search_input'] ?? ''));

        $page = max(1, (int) ($input['page'] ?? 1));
        $rawShowLimit = (string) ($input['show_limit'] ?? '');
        $showLimit = $rawShowLimit === '' ? 999999 : (int) $rawShowLimit;
        if ($showLimit <= 0) {
            $showLimit = 8;
        }

        $query = PpCurrency::query()->where('brand_id', $brandId);

        if ($searchInput !== '') {
            $query->where(function ($q) use ($searchInput): void {
                $q->where('code', 'like', "%{$searchInput}%")
                    ->orWhere('symbol', 'like', "%{$searchInput}%");
            });
        }

        $totalRecords = (clone $query)->count();
        $offset = ($page - 1) * $showLimit;

        $rows = (clone $query)
            ->orderByRaw('(code = ?) DESC', [$brandCurrencyCode])
            ->orderBy('id')
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

        $response = $rows->map(function (PpCurrency $row) use ($timezone, $brandCurrencyCode): array {
            if ($brandCurrencyCode === (string) $row->code) {
                $rateLabel = '1.00 ' . $brandCurrencyCode . ' = 1.00 ' . (string) $row->code;
                $default = 'true';
            } else {
                $rateLabel = '1.00 ' . (string) $row->code . ' = ' . $this->roundMoney($this->toFloat($row->rate), 4) . ' ' . $brandCurrencyCode;
                $default = 'false';
            }

            return [
                'default' => $default,
                'id' => (int) $row->id,
                'code' => (string) $row->code,
                'symbol' => (string) $row->symbol,
                'rate' => $rateLabel,
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

    public function edit(array $input, string $brandId): array
    {
        $currencyId = trim((string) ($input['currency_id'] ?? ''));
        $currencySymbol = trim((string) ($input['currency_symbol'] ?? ''));
        $currencyRate = trim((string) ($input['currency_rate'] ?? ''));

        if ($currencyId === '' || $currencySymbol === '' || $currencyRate === '') {
            return [
                'status' => 'false',
                'title' => 'Incomplete Information',
                'message' => 'Please fill in all required fields before proceeding.',
            ];
        }

        $row = PpCurrency::query()
            ->where('brand_id', $brandId)
            ->where('id', $currencyId)
            ->first();

        if ($row === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid Currency ID',
            ];
        }

        $row->update([
            'symbol' => $currencySymbol,
            'rate' => $this->sanitizeMoney($currencyRate),
            'updated_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return [
            'status' => 'true',
            'title' => 'Currency Updated',
            'message' => 'The currency has been updated successfully.',
        ];
    }

    public function infoById(string $itemId, string $brandId): array
    {
        $row = PpCurrency::query()
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
            'code' => (string) $row->code,
            'symbol' => (string) $row->symbol,
            'rate' => $this->sanitizeMoney((string) $row->rate),
        ];
    }

    public function bulkImport(string $brandId): array
    {
        $response = Http::withoutVerifying()->timeout(10)->get('https://gist.githubusercontent.com/ksafranski/2973986/raw/');
        if (!$response->ok()) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        $currencies = $response->json();
        if (!is_array($currencies)) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid request',
            ];
        }

        foreach ($currencies as $code => $details) {
            if (!is_string($code) || !is_array($details)) {
                continue;
            }

            $exists = PpCurrency::query()
                ->where('brand_id', $brandId)
                ->where('code', $code)
                ->exists();

            if ($exists) {
                continue;
            }

            PpCurrency::query()->create([
                'brand_id' => $brandId,
                'code' => $code,
                'symbol' => (string) ($details['symbol_native'] ?? ''),
                'rate' => '0',
                'created_date' => now()->format('Y-m-d H:i:s'),
                'updated_date' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        return [
            'status' => 'true',
            'title' => 'Currencies Imported',
            'message' => 'All currency data has been imported successfully.',
        ];
    }

    public function rateSync(string $itemId, string $brandId, string $brandCurrencyCode): array
    {
        $row = PpCurrency::query()
            ->where('id', $itemId)
            ->where('brand_id', $brandId)
            ->first();

        if ($row !== null) {
            $rates = $this->fetchRates($brandCurrencyCode);
            if ($rates === null) {
                return [
                    'status' => 'false',
                    'title' => 'Request Failed',
                    'message' => 'Invalid default currency',
                ];
            }

            foreach ($rates as $currency => $rate) {
                if (!is_string($currency) || !is_numeric($rate)) {
                    continue;
                }

                if ($currency === strtolower($brandCurrencyCode)) {
                    continue;
                }

                if ((float) $rate <= 0) {
                    continue;
                }

                if (strtolower((string) $row->code) === $currency) {
                    $row->update([
                        'rate' => $this->inverseRate((float) $rate),
                        'updated_date' => now()->format('Y-m-d H:i:s'),
                    ]);
                    break;
                }
            }
        }

        return [
            'status' => 'true',
            'title' => 'Currency Rate Updated',
            'message' => 'The selected currency rate have been updated successfully.',
        ];
    }

    public function bulkRateSync(string $brandId, string $brandCurrencyCode): array
    {
        $rates = $this->fetchRates($brandCurrencyCode);
        if ($rates === null) {
            return [
                'status' => 'false',
                'title' => 'Request Failed',
                'message' => 'Invalid default currency',
            ];
        }

        foreach ($rates as $currency => $rate) {
            if (!is_string($currency) || !is_numeric($rate)) {
                continue;
            }

            if ($currency === strtolower($brandCurrencyCode)) {
                continue;
            }

            if ((float) $rate <= 0) {
                continue;
            }

            PpCurrency::query()
                ->where('brand_id', $brandId)
                ->where('code', $currency)
                ->update([
                    'rate' => $this->inverseRate((float) $rate),
                    'updated_date' => now()->format('Y-m-d H:i:s'),
                ]);
        }

        return [
            'status' => 'true',
            'title' => 'Currencies Rate Updated',
            'message' => 'The selected currencies rate have been updated successfully.',
        ];
    }

    private function fetchRates(string $brandCurrencyCode): ?array
    {
        $base = strtolower($brandCurrencyCode);
        if ($base === '' || $base === '--') {
            return null;
        }

        $response = Http::withoutVerifying()
            ->timeout(10)
            ->get('https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/' . $base . '.json');

        if (!$response->ok()) {
            return null;
        }

        $data = $response->json();
        if (!is_array($data) || !isset($data[$base]) || !is_array($data[$base])) {
            return null;
        }

        return $data[$base];
    }

    private function inverseRate(float $rate): float
    {
        $value = sprintf('%.14f', $rate);

        if (function_exists('money_div') && function_exists('money_sanitize')) {
            return (float) money_div(1, money_sanitize($value));
        }

        if ($rate <= 0) {
            return 0.0;
        }

        return 1 / $rate;
    }

    private function sanitizeMoney(string|int|float|null $value): string
    {
        if (function_exists('money_sanitize')) {
            return (string) money_sanitize((string) $value);
        }

        return is_numeric($value) ? (string) (float) $value : '0';
    }

    private function roundMoney(float|int $amount, int $decimals = 2): string
    {
        return number_format((float) $amount, $decimals, '.', '');
    }

    private function toFloat(string|int|float|null $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
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
