<?php

namespace App\Services\Admin;

use App\Models\PpGateway;
use App\Models\PpTransaction;

class DashboardStatisticsService
{
    public function transactionStatistics(string $brandId, string $date = 'this_year', string $start = '', string $end = ''): array
    {
        [$labels, $keys, $mode] = $this->buildTransactionRange($date, $start, $end);

        $total = array_fill(0, count($keys), 0);
        $complete = array_fill(0, count($keys), 0);
        $pending = array_fill(0, count($keys), 0);
        $keyMap = array_flip($keys);

        $transactions = PpTransaction::query()
            ->where('brand_id', $brandId)
            ->where('status', '!=', 'initiated')
            ->get(['status', 'created_date']);

        foreach ($transactions as $transaction) {
            $created = strtotime((string) $transaction->created_date);
            if ($created === false) {
                continue;
            }

            $trxKey = match ($mode) {
                'hour' => date('Y-m-d H', $created),
                'day' => date('Y-m-d', $created),
                default => date('Y-m', $created),
            };

            if (!isset($keyMap[$trxKey])) {
                continue;
            }

            $i = $keyMap[$trxKey];
            $total[$i]++;

            if ((string) $transaction->status === 'completed') {
                $complete[$i]++;
            }

            if ((string) $transaction->status === 'pending') {
                $pending[$i]++;
            }
        }

        return [
            'labels' => $labels,
            'total' => $total,
            'complete' => $complete,
            'pending' => $pending,
        ];
    }

    public function gatewayStatistics(string $brandId, string $date = 'this_year', string $start = '', string $end = ''): array
    {
        [$labels, $keys, $mode] = $this->buildGatewayRange($date, $start, $end);
        $keyMap = array_flip($keys);

        $gatewayData = [];
        $gatewayLabelsById = [];
        $gatewayColorsByName = [];

        $transactions = PpTransaction::query()
            ->where('brand_id', $brandId)
            ->where('status', 'completed')
            ->get(['gateway_id', 'created_date']);

        $gatewayRows = PpGateway::query()
            ->where('brand_id', $brandId)
            ->get(['gateway_id', 'name', 'primary_color'])
            ->keyBy('gateway_id');

        foreach ($transactions as $transaction) {
            $created = strtotime((string) $transaction->created_date);
            if ($created === false) {
                continue;
            }

            $trxKey = match ($mode) {
                'hour' => date('Y-m-d H', $created),
                'day' => date('Y-m-d', $created),
                default => date('Y-m', $created),
            };

            if (!isset($keyMap[$trxKey])) {
                continue;
            }

            $gatewayId = (string) $transaction->gateway_id;
            if (!isset($gatewayLabelsById[$gatewayId])) {
                $gateway = $gatewayRows->get($gatewayId);
                $gatewayName = trim((string) ($gateway?->name ?? ''));
                $gatewayName = $gatewayName === '' ? 'Unknown' : $gatewayName;

                $gatewayColor = trim((string) ($gateway?->primary_color ?? ''));
                $gatewayColor = $gatewayColor === '' ? '#d3d3d3' : $gatewayColor;

                $gatewayLabelsById[$gatewayId] = $gatewayName;
                $gatewayColorsByName[$gatewayName] = $gatewayColor;
                $gatewayData[$gatewayName] = array_fill(0, count($keys), 0);
            }

            $index = $keyMap[$trxKey];
            $gatewayName = $gatewayLabelsById[$gatewayId];
            $gatewayData[$gatewayName][$index]++;
        }

        if ($gatewayData === []) {
            $gatewayData['No Data'] = [1];
            $gatewayColorsByName['No Data'] = '#f0f0f0';
        }

        return [
            'labels' => $labels,
            'keys' => $keys,
            'gateway_labels' => array_values($gatewayLabelsById) === [] ? ['No Data'] : array_values($gatewayLabelsById),
            'data' => $gatewayData,
            'colors' => array_values($gatewayColorsByName),
        ];
    }

    private function buildTransactionRange(string $date, string $start, string $end): array
    {
        $date = trim($date) === '' ? 'this_year' : trim($date);
        $start = trim($start);
        $end = trim($end);

        $labels = [];
        $keys = [];

        if ($start !== '' || $end !== '') {
            if ($start === '') {
                $start = $end;
            }

            if ($end === '') {
                $end = $start;
            }

            $startTs = strtotime($start);
            $endTs = strtotime($end);

            if ($startTs === false || $endTs === false) {
                return $this->buildTransactionRange('this_year', '', '');
            }

            if ($startTs > $endTs) {
                [$startTs, $endTs] = [$endTs, $startTs];
            }

            for ($ts = $startTs; $ts <= $endTs; $ts = strtotime('+1 day', $ts)) {
                if ($ts === false) {
                    break;
                }

                $labels[] = date('d M', $ts);
                $keys[] = date('Y-m-d', $ts);
            }

            return [$labels, $keys, 'day'];
        }

        return $this->buildPresetRange($date, true);
    }

    private function buildGatewayRange(string $date, string $start, string $end): array
    {
        $date = trim($date) === '' ? 'this_year' : trim($date);
        $start = trim($start);
        $end = trim($end);

        if ($start !== '' && $end !== '') {
            $startTs = strtotime($start);
            $endTs = strtotime($end);

            if ($startTs !== false && $endTs !== false) {
                if ($startTs > $endTs) {
                    [$startTs, $endTs] = [$endTs, $startTs];
                }

                $labels = [];
                $keys = [];
                while ($startTs <= $endTs) {
                    $labels[] = date('d M', $startTs);
                    $keys[] = date('Y-m-d', $startTs);
                    $next = strtotime('+1 day', $startTs);
                    if ($next === false) {
                        break;
                    }
                    $startTs = $next;
                }

                return [$labels, $keys, 'day'];
            }
        }

        return $this->buildPresetRange($date, false);
    }

    private function buildPresetRange(string $date, bool $isTransaction): array
    {
        $labels = [];
        $keys = [];

        switch ($date) {
            case 'today':
                for ($i = 6; $i >= 0; $i--) {
                    $ts = strtotime("-$i hour");
                    $labels[] = date('h A', $ts);
                    $keys[] = date('Y-m-d H', $ts);
                }
                return [$labels, $keys, 'hour'];

            case 'yesterday':
                for ($i = 0; $i < 7; $i++) {
                    $ts = strtotime("yesterday +$i hour");
                    $labels[] = date('h A', $ts);
                    $keys[] = date('Y-m-d H', $ts);
                }
                return [$labels, $keys, 'hour'];

            case 'this_week':
            case 'last_week':
                $start = $date === 'this_week' ? strtotime('monday this week') : strtotime('monday last week');
                for ($i = 0; $i < 7; $i++) {
                    $ts = strtotime("+$i day", $start);
                    $labels[] = date('D', $ts);
                    $keys[] = date('Y-m-d', $ts);
                }
                return [$labels, $keys, 'day'];

            case 'this_month':
            case 'last_month':
                $start = $date === 'this_month' ? strtotime(date('Y-m-01')) : strtotime('first day of last month');
                $days = (int) date('t', $start);
                for ($i = 0; $i < $days; $i++) {
                    $ts = strtotime("+$i day", $start);
                    $labels[] = date('d', $ts);
                    $keys[] = date('Y-m-d', $ts);
                }
                return [$labels, $keys, 'day'];

            case 'previous_year':
                for ($i = 11; $i >= 0; $i--) {
                    $ts = strtotime("-$i month", strtotime('first day of january last year'));
                    $labels[] = date('M', $ts);
                    $keys[] = date('Y-m', $ts);
                }
                return [$labels, $keys, 'month'];

            case 'this_year':
            default:
                if ($isTransaction) {
                    for ($i = 11; $i >= 0; $i--) {
                        $ts = strtotime("-$i month");
                        $labels[] = date('M', $ts);
                        $keys[] = date('Y-m', $ts);
                    }
                } else {
                    for ($i = 6; $i >= 0; $i--) {
                        $ts = strtotime("-$i month");
                        $labels[] = date('M', $ts);
                        $keys[] = date('Y-m', $ts);
                    }
                }

                return [$labels, $keys, 'month'];
        }
    }
}
