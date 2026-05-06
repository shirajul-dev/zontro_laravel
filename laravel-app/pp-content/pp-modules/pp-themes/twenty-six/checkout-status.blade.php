@php
    if(request()->has('receipt')){
        if (function_exists('pp_downloadReceiptPDF')) {
            pp_downloadReceiptPDF($pageData);
        }
    }

    $status = strtolower($transaction['status'] ?? 'pending');
    $statusMap = [
        'completed' => ['text' => $lang['payment_successful'], 'color' => 'success', 'icon' => 'check-circle-fill'],
        'pending'   => ['text' => $lang['payment_pending'], 'color' => 'warning', 'icon' => 'hourglass-split'],
        'refunded'  => ['text' => $lang['payment_refunded'], 'color' => 'info', 'icon' => 'arrow-counterclockwise'],
        'canceled'  => ['text' => $lang['payment_canceled'], 'color' => 'danger', 'icon' => 'x-circle-fill'],
        'failed'    => ['text' => $lang['payment_failed'] ?? 'Payment Failed', 'color' => 'danger', 'icon' => 'x-circle-fill'],
    ];
    $currentStatus = $statusMap[$status] ?? $statusMap['pending'];

    $bgStyle = 'background-color:#f8f9fa;';
    if (!empty($options['enable_bg_image']) && $options['enable_bg_image'] === 'enabled' && !empty($options['background_image'])) {
        $bgImage = $options['background_image'];
        $bgStyle = "
            background-image: url('{$bgImage}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        ";
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $lang['checkout'] }} - {{ $brand['name'] }}</title>
    <link rel="shortcut icon" href="{{ $brand['favicon'] }}">

    {!! pp_assets('head') !!}

    <style>
        .container{
            max-width: 650px; 
            width: 100%;
        }
        .company-logo{
            margin-top: 15px;
            height: 50px;
            margin-bottom: 15px;
        }
        .btn-primary {
            --tblr-btn-border-color: transparent;
            --tblr-btn-hover-border-color: transparent;
            --tblr-btn-active-border-color: transparent;
            --tblr-btn-color: {{ $options['text_color'] ?? '#FFFFFF' }};
            --tblr-btn-bg: {{ $options['primary_color'] ?? '#5f38f9' }};
            --tblr-btn-hover-color: {{ $options['text_color'] ?? '#FFFFFF' }};
            --tblr-btn-hover-bg: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'] ?? '#5f38f9', 0.80) : ($options['primary_color'] ?? '#5f38f9') }};
            --tblr-btn-active-color: {{ $options['text_color'] ?? '#FFFFFF' }};
            --tblr-btn-active-bg: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'] ?? '#5f38f9', 0.80) : ($options['primary_color'] ?? '#5f38f9') }};
            --tblr-btn-disabled-bg: {{ $options['primary_color'] ?? '#5f38f9' }};
            --tblr-btn-disabled-color: {{ $options['text_color'] ?? '#FFFFFF' }};
            --tblr-btn-box-shadow: {{ $options['text_color'] ?? '#FFFFFF' }};
        }

        /* SEO & Tracking */
        @php
            $seoTitle = trim($options['seo_title'] ?? '');
            $seoDesc  = trim($options['seo_description'] ?? '');
            $seoKey   = trim($options['seo_keywords'] ?? '');
            $analyticsCode = trim($options['analytics_code'] ?? '');
        @endphp
        @if($seoTitle !== '' && $seoTitle !== '--')
            <meta name="title" content="{{ $seoTitle }}">
            <meta property="og:title" content="{{ $seoTitle }}">
        @endif
        @if($seoDesc !== '' && $seoDesc !== '--')
            <meta name="description" content="{{ $seoDesc }}">
            <meta property="og:description" content="{{ $seoDesc }}">
        @endif
        @if($seoKey !== '' && $seoKey !== '--')
            <meta name="keywords" content="{{ $seoKey }}">
        @endif
        @if($analyticsCode !== '' && $analyticsCode !== '--')
            {!! $analyticsCode !!}
        @endif
    </style>
</head>
<body style="{{ $bgStyle }}" loading="lazy">
    <div class="container container-tight py-4">
        <div class="card">
            <div class="card-body text-center">

                <div class="mb-4 mt-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="text-{{ $currentStatus['color'] }}" width="80" height="80" fill="currentColor" viewBox="0 0 16 16">
                        @switch($status)
                            @case('completed')
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM6.97 11.03a.75.75 0 0 0 1.08 0l3.992-3.992a.75.75 0 1 0-1.06-1.06L7.5 9.439 5.97 7.97a.75.75 0 1 0-1.06 1.06l2.06 2.06z"/>
                                @break
                            @case('pending')
                                <path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0-1A6 6 0 1 1 8 2a6 6 0 0 1 0 12zm-.5-6V4h1v5h-1zm0 2h1v1h-1v-1z"/>
                                @break
                            @case('refunded')
                                <path d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 0-.853-.521A4 4 0 1 1 8 4v1l2-2-2-2v1a5 5 0 0 0 0 10z"/>
                                @break
                            @case('canceled')
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.646 4.646a.5.5 0 0 0 0 .708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646a.5.5 0 0 0-.708 0z"/>
                                @break
                        @endswitch
                    </svg>
                </div>

                <h2 class="text-{{ $currentStatus['color'] }} mb-3">{{ $currentStatus['text'] }}</h2>
                <p class="text-muted mb-4">
                    @switch($status)
                        @case('completed')
                            {{ $lang['change_status_completed'] }}
                            @break
                        @case('pending')
                            {{ $lang['change_status_pending'] }}
                            @break
                        @case('refunded')
                            {{ $lang['change_status_refunded'] }}
                            @break
                        @case('canceled')
                            {{ $lang['change_status_cancled'] }}
                            @break
                    @endswitch
                </p>

                <div class="table-responsive mb-4 {{ ($status == "canceled") ? 'd-none' : '' }}">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>{{ $lang['payment_method'] }}</th>
                                <td>{{ $transaction['payment_method'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>{{ $lang['amount'] }}</th>
                                <td>{{ number_format($transaction['amount'] ?? 0, 2) }} {{ $transaction['currency'] ?? 'BDT' }}</td>
                            </tr>
                            <tr>
                                <th>{{ $lang['discount'] }}</th>
                                <td>{{ number_format($transaction['discount_amount'] ?? 0, 2) }} {{ $transaction['currency'] ?? 'BDT' }}</td>
                            </tr>
                            <tr>
                                <th>{{ $lang['processing_fee'] }}</th>
                                <td>{{ number_format($transaction['processing_fee'] ?? 0, 2) }} {{ $transaction['currency'] ?? 'BDT' }}</td>
                            </tr>
                            <tr>
                                <th>{{ $lang['net_amount'] }}</th>
                                <td>{{ number_format(($transaction['amount'] ?? 0) - ($transaction['discount_amount'] ?? 0) + ($transaction['processing_fee'] ?? 0), 2) }} {{ $transaction['currency'] ?? 'BDT' }}</td>
                            </tr>
                            <tr>
                                <th>{{ $lang['net_local_amount'] }}</th>
                                <td>{{ number_format($transaction['local_net_amount'] ?? 0, 2) }} {{ $transaction['local_currency'] ?? 'BDT' }}</td>
                            </tr>
                            <tr>
                                <th>{{ $lang['status'] }}</th>
                                <td><span class="text-{{ $currentStatus['color'] }}">{{ ucfirst($status) }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mb-3">
                    <a href="{{ $transaction['return_url'] }}" class="btn btn-primary {{ ($transaction['return_url'] == "--" || $transaction['return_url'] == "") ? 'd-none' : '' }}">{{ $lang['go_to_site'] }}</a>
                    @if(in_array($status, ['completed', 'pending', 'refunded']))
                        <a href="{{ function_exists('pp_checkout_address') ? pp_checkout_address() : url('/') }}?receipt" class="btn btn-success">{{ $lang['download_receipt'] }}</a>
                    @endif
                </div>

            </div>
        </div>

        <center class="footer-branding" style="margin-top: 20px;">{!! $options['watermark_text'] ?? '' !!}</center>
    </div>

    {!! pp_assets('footer') !!}
</body>
</html>
