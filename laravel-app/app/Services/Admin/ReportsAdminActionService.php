<?php

namespace App\Services\Admin;

use App\Models\PpCurrency;
use App\Models\PpTransaction;
use Carbon\Carbon;

class ReportsAdminActionService
{
    public function generate(array $input, string $brandId, string $brandCurrencyCode): array
    {
        $date = trim((string) ($input['date'] ?? 'this_year'));
        $rawStart = trim((string) ($input['start'] ?? ''));
        $rawEnd = trim((string) ($input['end'] ?? ''));

        $range = $this->resolveRange($date, $rawStart, $rawEnd);
        if (($range['status'] ?? 'true') === 'false') {
            return $range;
        }

        $currencyRates = PpCurrency::query()
            ->where('brand_id', $brandId)
            ->get(['code', 'rate'])
            ->mapWithKeys(static fn (PpCurrency $c) => [(string) $c->code => (string) $c->rate])
            ->all();

        $rows = PpTransaction::query()
            ->where('brand_id', $brandId)
            ->whereNotIn('status', ['initiated', 'expired'])
            ->whereDate('created_date', '>=', $range['current_start'])
            ->whereDate('created_date', '<=', $range['current_end'])
            ->get();

        $total = 0;
        $completed = 0;
        $revenue = '0';

        foreach ($rows as $row) {
            $total++;
            if ((string) $row->status === 'completed') {
                $completed++;

                $txnCurrency = (string) $row->currency;
                $rate = $txnCurrency === $brandCurrencyCode ? '1' : (string) ($currencyRates[$txnCurrency] ?? '0');

                $convertedAmount = $this->moneyMul((string) $row->amount, $rate);
                $revenue = $this->moneyAdd($revenue, $convertedAmount);
            }
        }

        $successRate = $total > 0 ? $this->moneyDiv((string) ($completed * 100), (string) $total, 2) : '0';
        $average = $completed > 0 ? $this->moneyDiv($revenue, (string) $completed, 2) : '0';

        $prevRows = PpTransaction::query()
            ->where('brand_id', $brandId)
            ->whereNotIn('status', ['initiated', 'expired'])
            ->whereDate('created_date', '>=', $range['prev_start'])
            ->whereDate('created_date', '<=', $range['prev_end'])
            ->get();

        $prevTotal = 0;
        $prevCompleted = 0;

        foreach ($prevRows as $row) {
            $prevTotal++;
            if ((string) $row->status === 'completed') {
                $prevCompleted++;
            }
        }

        $prevSuccessRate = $prevTotal > 0 ? $this->moneyDiv((string) ($prevCompleted * 100), (string) $prevTotal, 2) : '0';

        $cmp = $this->compareMoney($successRate, $prevSuccessRate, 2);
        $trend = $cmp > 0 ? 'up' : ($cmp < 0 ? 'down' : 'same');

        return [
            'status' => 'true',
            'date_range' => $range['from_label'] . ' – ' . $range['to_label'],
            'revenue' => $this->moneyRound($revenue, 2),
            'completed' => $completed,
            'total' => $total,
            'success_rate' => $this->moneyRound($successRate, 2),
            'prev_success_rate' => $this->moneyRound($prevSuccessRate, 2),
            'success_trend' => $trend,
            'average' => $this->moneyRound($average, 2),
        ];
    }

    private function resolveRange(string $date, string $rawStart, string $rawEnd): array
    {
        $start = $this->parseDateFlexible($rawStart);
        $end = $this->parseDateFlexible($rawEnd);

        if ($start !== null || $end !== null) {
            if ($start !== null && $end !== null) {
                if ($start->gt($end)) {
                    return [
                        'status' => 'false',
                        'title' => 'Invalid Date Range',
                        'message' => 'Start date must be earlier than end date.',
                    ];
                }
                $rangeStart = $start;
                $rangeEnd = $end;
            } elseif ($start !== null) {
                $rangeStart = $start;
                $rangeEnd = Carbon::today();
            } else {
                $rangeStart = $end;
                $rangeEnd = $end;
            }

            $days = $rangeStart->diffInDays($rangeEnd) + 1;
            $prevStart = $rangeStart->copy()->subDays($days);
            $prevEnd = $rangeEnd->copy()->subDays($days);

            return [
                'current_start' => $rangeStart->toDateString(),
                'current_end' => $rangeEnd->toDateString(),
                'prev_start' => $prevStart->toDateString(),
                'prev_end' => $prevEnd->toDateString(),
                'from_label' => $rangeStart->format('M d, Y'),
                'to_label' => $rangeEnd->format('M d, Y'),
            ];
        }

        $today = Carbon::today();

        switch ($date) {
            case 'today':
                $curStart = $today->copy();
                $curEnd = $today->copy();
                $prevStart = $today->copy()->subDay();
                $prevEnd = $today->copy()->subDay();
                break;
            case 'yesterday':
                $curStart = $today->copy()->subDay();
                $curEnd = $today->copy()->subDay();
                $prevStart = $today->copy()->subDays(2);
                $prevEnd = $today->copy()->subDays(2);
                break;
            case 'this_week':
                $curStart = $today->copy()->startOfWeek(Carbon::MONDAY);
                $curEnd = $today->copy()->endOfWeek(Carbon::SUNDAY);
                $prevStart = $curStart->copy()->subWeek();
                $prevEnd = $curEnd->copy()->subWeek();
                break;
            case 'last_week':
                $curStart = $today->copy()->startOfWeek(Carbon::MONDAY)->subWeek();
                $curEnd = $today->copy()->endOfWeek(Carbon::SUNDAY)->subWeek();
                $prevStart = $curStart->copy()->subWeek();
                $prevEnd = $curEnd->copy()->subWeek();
                break;
            case 'this_month':
                $curStart = $today->copy()->startOfMonth();
                $curEnd = $today->copy()->endOfMonth();
                $prevStart = $curStart->copy()->subMonth()->startOfMonth();
                $prevEnd = $curStart->copy()->subMonth()->endOfMonth();
                break;
            case 'last_month':
                $curStart = $today->copy()->subMonth()->startOfMonth();
                $curEnd = $today->copy()->subMonth()->endOfMonth();
                $prevStart = $today->copy()->subMonths(2)->startOfMonth();
                $prevEnd = $today->copy()->subMonths(2)->endOfMonth();
                break;
            case 'previous_year':
                $curStart = Carbon::create($today->year - 1, 1, 1);
                $curEnd = Carbon::create($today->year - 1, 12, 31);
                $prevStart = Carbon::create($today->year - 2, 1, 1);
                $prevEnd = Carbon::create($today->year - 2, 12, 31);
                break;
            case 'this_year':
            default:
                $curStart = Carbon::create($today->year, 1, 1);
                $curEnd = Carbon::create($today->year, 12, 31);
                $prevStart = Carbon::create($today->year - 1, 1, 1);
                $prevEnd = Carbon::create($today->year - 1, 12, 31);
                break;
        }

        return [
            'current_start' => $curStart->toDateString(),
            'current_end' => $curEnd->toDateString(),
            'prev_start' => $prevStart->toDateString(),
            'prev_end' => $prevEnd->toDateString(),
            'from_label' => $curStart->format('M d, Y'),
            'to_label' => $curEnd->format('M d, Y'),
        ];
    }

    private function parseDateFlexible(string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        try {
            $dt = Carbon::createFromFormat('Y-m-d', $value);
            if ($dt !== false) {
                return $dt;
            }
        } catch (\Throwable) {
            // noop
        }

        try {
            $dt = Carbon::createFromFormat('m/d/Y', $value);
            if ($dt !== false) {
                return $dt;
            }
        } catch (\Throwable) {
            // noop
        }

        return null;
    }

    private function moneyAdd(string $a, string $b): string
    {
        if (function_exists('money_add')) {
            return (string) money_add($a, $b);
        }

        return (string) ((float) $a + (float) $b);
    }

    private function moneyMul(string $a, string $b): string
    {
        if (function_exists('money_mul')) {
            return (string) money_mul($a, $b);
        }

        return (string) ((float) $a * (float) $b);
    }

    private function moneyDiv(string $a, string $b, int $scale = 2): string
    {
        if (function_exists('money_div')) {
            return (string) money_div($a, $b, $scale);
        }

        if ((float) $b == 0.0) {
            return '0';
        }

        return number_format(((float) $a / (float) $b), $scale, '.', '');
    }

    private function moneyRound(string $value, int $scale = 2): string
    {
        if (function_exists('money_round')) {
            return (string) money_round($value, $scale);
        }

        return number_format((float) $value, $scale, '.', '');
    }

    private function compareMoney(string $a, string $b, int $scale = 2): int
    {
        if (function_exists('bccomp')) {
            return (int) bccomp($a, $b, $scale);
        }

        $fa = round((float) $a, $scale);
        $fb = round((float) $b, $scale);

        return $fa <=> $fb;
    }
}
