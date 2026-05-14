@php
    if(request()->has('lang') && request('lang') != ""){
        if (function_exists('pp_set_lang')) {
            pp_set_lang(request('lang'));
        }
        echo "<script>sessionStorage.setItem('lang_changed', '1'); location.href = '" . url()->current() . "';</script>";
        exit();
    }

    if(request()->has('cancel')){
        if (function_exists('pp_set_transaction_status')) {
            pp_set_transaction_status($transaction['ref'], 'canceled');
        }
        $checkoutUrl = function_exists('pp_checkout_address') ? pp_checkout_address() : url('/');
        echo "<script>location.href = '{$checkoutUrl}';</script>";
        exit();
    }

    $pp_gateways_mfs = pp_gateways('mfs', ['transaction' => $transaction, 'brand' => $brand]);
    $pp_gateways_bank = pp_gateways('bank', ['transaction' => $transaction, 'brand' => $brand]);
    $pp_gateways_global = pp_gateways('global', ['transaction' => $transaction, 'brand' => $brand]);

    $bgStyle = 'background-color:#f8f9fa;';
    if (!empty($options['enable_bg_image']) && $options['enable_bg_image'] === 'enabled' && !empty($options['background_image'])) {
        $bgImage = function_exists('pp_asset_url') ? pp_asset_url($options['background_image']) : $options['background_image'];
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
    <link rel="shortcut icon" href="{{ function_exists('pp_asset_url') ? pp_asset_url($brand['favicon']) : $brand['favicon'] }}">

    {!! pp_assets('head') !!}

    <style>
        .container {
            max-width: 650px;
            width: 100%;
        }
        .company-logo {
            margin-top: 15px;
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 100% !important;
        }
        .company-name {
            margin-top: 15px;
            margin-bottom: 15px;
            font-size: 20px;
            font-weight: 600;
        }

        .btn-primary {
            border: none;
            background: {{ $options['primary_color'] ?? '#5f38f9' }} !important;
            color: {{ $options['text_color'] ?? '#FFFFFF' }} !important;
        }
        .btn-outline-primary {
            border-color: {{ $options['primary_color'] ?? '#5f38f9' }} !important;
            color: {{ $options['primary_color'] ?? '#5f38f9' }} !important;
        }
        .btn-outline-primary:hover {
            background-color: {{ $options['primary_color'] ?? '#5f38f9' }} !important;
            color: {{ $options['text_color'] ?? '#FFFFFF' }} !important;
        }

        /* Tab Active State */
        .btn-group .btn.active {
            background-color: {{ $options['primary_color'] ?? '#5f38f9' }} !important;
            border-color: {{ $options['primary_color'] ?? '#5f38f9' }} !important;
            color: {{ $options['text_color'] ?? '#FFFFFF' }} !important;
        }

        .gateway-card {
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #e6e7e9;
        }
        .gateway-card.active {
            border-color: {{ $options['primary_color'] ?? '#5f38f9' }} !important;
            border-width: 2px !important;
            background-color: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'] ?? '#5f38f9', 0.05) : 'transparent' }};
        }

        #btn-pay-now {
            transition: all 0.3s ease;
        }
        #btn-pay-now:disabled {
            pointer-events: none;
            opacity: 0.5;
            filter: grayscale(0.5);
        }
    </style>

    <!-- SEO & Tracking -->
    @if(!empty($options['seo_title']) && $options['seo_title'] !== '--')
        <meta name="title" content="{{ $options['seo_title'] }}">
        <meta property="og:title" content="{{ $options['seo_title'] }}">
    @endif
    @if(!empty($options['seo_description']) && $options['seo_description'] !== '--')
        <meta name="description" content="{{ $options['seo_description'] }}">
        <meta property="og:description" content="{{ $options['seo_description'] }}">
    @endif
    @if(!empty($options['seo_keywords']) && $options['seo_keywords'] !== '--')
        <meta name="keywords" content="{{ $options['seo_keywords'] }}">
    @endif
    @if(!empty($options['analytics_code']) && $options['analytics_code'] !== '--')
        {!! $options['analytics_code'] !!}
    @endif
</head>
<body style="{{ $bgStyle }}" loading="lazy">
    <div class="container container-tight py-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between border rounded p-2">
                    <div onclick="location.href='?cancel'" style="text-align: right; cursor: pointer; color: {{ $options['primary_color'] ?? '#5f38f9' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" style="padding: 6px; background-color: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'] ?? '#5f38f9', 0.05) : 'transparent' }}; border-radius: 100%; width: 32px; height: 32px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-x"><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                    </div>
                    <div class="btns-group d-flex gap-2">
                        <div class="btns" data-tab="support" style="text-align: right; cursor: pointer; color: {{ $options['primary_color'] ?? '#5f38f9' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" style="padding: 6px; background-color: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'] ?? '#5f38f9', 0.05) : 'transparent' }}; border-radius: 100%; width: 32px; height: 32px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-headphones"><path d="M4 15a2 2 0 0 1 2 -2h1a2 2 0 0 1 2 2v3a2 2 0 0 1 -2 2h-1a2 2 0 0 1 -2 -2l0 -3" /><path d="M15 15a2 2 0 0 1 2 -2h1a2 2 0 0 1 2 2v3a2 2 0 0 1 -2 2h-1a2 2 0 0 1 -2 -2l0 -3" /><path d="M4 15v-3a8 8 0 0 1 16 0v3" /></svg>
                        </div>
                        <div class="btns" data-tab="details" style="text-align: right; cursor: pointer; color: {{ $options['primary_color'] ?? '#5f38f9' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" style="padding: 6px; background-color: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'] ?? '#5f38f9', 0.05) : 'transparent' }}; border-radius: 100%; width: 32px; height: 32px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-info-circle"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></svg>
                        </div>
                        <div class="btns" data-tab="faq" style="text-align: right; cursor: pointer; color: {{ $options['primary_color'] ?? '#5f38f9' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" style="padding: 6px; background-color: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'] ?? '#5f38f9', 0.05) : 'transparent' }}; border-radius: 100%; width: 32px; height: 32px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-help-hexagon"><path d="M19.875 6.27c.7 .398 1.13 1.143 1.125 1.948v7.284c0 .809 -.443 1.555 -1.158 1.948l-6.75 4.27a2.269 2.269 0 0 1 -2.184 0l-6.75 -4.27a2.225 2.225 0 0 1 -1.158 -1.948v-7.285c0 -.809 .443 -1.554 1.158 -1.947l6.75 -3.98a2.33 2.33 0 0 1 2.25 0l6.75 3.98h-.033" /><path d="M12 16v.01" /><path d="M12 13a2 2 0 0 0 .914 -3.782a1.98 1.98 0 0 0 -2.414 .483" /></svg>
                        </div>
                        <div style="text-align: right; cursor: pointer; color: {{ $options['primary_color'] ?? '#5f38f9' }}" data-bs-target="#modal-language" data-bs-toggle="modal">
                            <svg xmlns="http://www.w3.org/2000/svg" style="padding: 6px; background-color: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'] ?? '#5f38f9', 0.05) : 'transparent' }}; border-radius: 100%; width: 32px; height: 32px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-language"><path d="M9 6.371c0 4.418 -2.239 6.629 -5 6.629" /><path d="M4 6.371h7" /><path d="M5 9c0 2.144 2.252 3.908 6 4" /><path d="M12 20l4 -9l4 9" /><path d="M19.1 18h-6.2" /><path d="M6.694 3l.793 .582" /></svg>
                        </div>
                    </div>
                </div>

                <center>
                    <img src="{{ function_exists('pp_asset_url') ? pp_asset_url($brand['favicon']) : $brand['favicon'] }}" alt="" class="company-logo">
                    <p class="company-name">{{ $brand['name'] }}</p>
                </center>

                <!-- Category Tabs -->
                <div class="btn-group w-100" role="group">
                    @if($pp_gateways_mfs['status'] && !empty($pp_gateways_mfs['gateway']))
                        <div class="btn btn-outline-primary w-100" data-tab="mfs">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-device-mobile"><path d="M6 5a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2v-14" /><path d="M11 4h2" /><path d="M12 17v.01" /></svg>
                            <span class="d-none d-sm-inline ms-1">{{ $lang['mobile_banking'] }}</span>
                        </div>
                    @endif
                    @if($pp_gateways_bank['status'] && !empty($pp_gateways_bank['gateway']))
                        <div class="btn btn-outline-primary w-100" data-tab="bank">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-building-bank"><path d="M3 21l18 0" /><path d="M3 10l18 0" /><path d="M5 6l7 -3l7 3" /><path d="M4 10l0 11" /><path d="M20 10l0 11" /><path d="M8 14l0 3" /><path d="M12 14l0 3" /><path d="M16 14l0 3" /></svg>
                            <span class="d-none d-sm-inline ms-1">{{ $lang['net_banking'] }}</span>
                        </div>
                    @endif
                    @if($pp_gateways_global['status'] && !empty($pp_gateways_global['gateway']))
                        <div class="btn btn-outline-primary w-100" data-tab="global">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-world"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M3.6 9h16.8" /><path d="M3.6 15h16.8" /><path d="M11.5 3a17 17 0 0 0 0 18" /><path d="M12.5 3a17 17 0 0 1 0 18" /></svg>
                            <span class="d-none d-sm-inline ms-1">{{ $lang['global'] }}</span>
                        </div>
                    @endif
                </div>

                <!-- Gateways Display -->
                <div id="gateways-mfs" class="mt-3 row g-3 text-center tab-content" style="display: none;">
                    @foreach($pp_gateways_mfs['gateway'] ?? [] as $row)
                        <div class="col-6 col-md-4 gateway-item" onclick="selectGateway('{{ $row['gateway_id'] }}', this)">
                            <div class="border rounded p-2 gateway-card">
                                <img src="{{ $row['logo'] }}" alt="" class="img-fluid mb-2" style="max-height: 40px;">
                                <div class="fw-semibold small">{{ $row['display'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div id="gateways-bank" class="mt-3 row g-3 text-center tab-content" style="display: none;">
                    @foreach($pp_gateways_bank['gateway'] ?? [] as $row)
                        <div class="col-6 col-md-4 gateway-item" onclick="selectGateway('{{ $row['gateway_id'] }}', this)">
                            <div class="border rounded p-2 gateway-card">
                                <img src="{{ $row['logo'] }}" alt="" class="img-fluid mb-2" style="max-height: 40px;">
                                <div class="fw-semibold small">{{ $row['display'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div id="gateways-global" class="mt-3 row g-3 text-center tab-content" style="display: none;">
                    @foreach($pp_gateways_global['gateway'] ?? [] as $row)
                        <div class="col-6 col-md-4 gateway-item" onclick="selectGateway('{{ $row['gateway_id'] }}', this)">
                            <div class="border rounded p-2 gateway-card">
                                <img src="{{ $row['logo'] }}" alt="" class="img-fluid mb-2" style="max-height: 40px;">
                                <div class="fw-semibold small">{{ $row['display'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Support View -->
                <div id="gateways-support" class="mt-3 row g-3 text-center tab-content" style="display: none;">
                    @php $support = $brand['support']; @endphp
                    @if(!empty($support['email']) && $support['email'] != '--')
                        <div class="col-12">
                            <a href="mailto:{{ $support['email'] }}" class="text-decoration-none text-dark">
                                <div class="border rounded p-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-mail"><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10" /><path d="M3 7l9 6l9 -6" /></svg>
                                    <div class="fw-semibold mt-2">{{ $lang['contact_email'] }}</div>
                                    <div class="text-muted small">{{ $support['email'] }}</div>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Details View -->
                <div id="gateways-details" class="mt-3 tab-content" style="display: none;">
                    <ul class="list-group list-group-flush border rounded">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $lang['currency'] }}</span>
                            <span class="fw-semibold">{{ $transaction['currency'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $lang['total'] }}</span>
                            <span class="fw-semibold">{{ $transaction['amount'] }} {{ $transaction['currency'] }}</span>
                        </li>
                    </ul>
                </div>

                <!-- FAQ View -->
                <div id="gateways-faq" class="mt-3 tab-content" style="display: none;">
                    <div class="accordion" id="accordion-faq">
                        @foreach($faqs ?? [] as $index => $faq)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button {{ $index == 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $index }}">
                                        {{ $faq['title'] }}
                                    </button>
                                </h2>
                                <div id="faq-{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" data-bs-parent="#accordion-faq">
                                    <div class="accordion-body">
                                        {!! $faq['description'] !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button id="btn-pay-now" class="btn btn-outline-primary w-100 mt-4" onclick="initiatePayment()" disabled>
                    <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    <span class="btn-text">{{ $lang['pay_now'] }} ({{ $transaction['amount'] }} {{ $transaction['currency'] }})</span>
                </button>
            </div>
        </div>

        <center class="footer-branding" style="margin-top: 20px;">{!! $options['watermark_text'] ?? '' !!}</center>
    </div>

    <!-- Language Modal -->
    <div class="modal fade" id="modal-language" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-top">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $lang['select_language'] }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mt-1">
                        <label class="form-label">{{ $lang['language'] }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="model-languages" onchange="hitLanguage()">
                            <option value="" selected>{{ $lang['select_a_language'] }}</option>
                            <option value="en">English</option>
                            <option value="bn">বাংলা</option>
                            <option value="hi">हिन्दी</option>
                            <option value="ur">اردو</option>
                            <option value="ar">العربية</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">{{ $lang['close'] }}</button>
                </div>
            </div>
        </div>
    </div>

    {!! pp_assets('footer') !!}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if(sessionStorage.getItem('lang_changed')){
                sessionStorage.removeItem('lang_changed');
                createToast({
                    title: 'Success',
                    description: 'Language changed successfully.',
                    svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2fb344" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-check"><path d="M5 12l5 5l10 -10" /></svg>`,
                    timeout: 4000
                });
            }

            const tabs = document.querySelectorAll('[data-tab]');
            const contents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const target = this.dataset.tab;

                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    contents.forEach(c => c.style.display = 'none');
                    const targetContent = document.getElementById('gateways-' + target);
                    if (targetContent) {
                        targetContent.style.display = (targetContent.classList.contains('row') ? 'flex' : 'block');
                    }
                });
            });

            // Default tab
            const firstTab = document.querySelector('.btn-group [data-tab]');
            if (firstTab) firstTab.click();
        });

        let selectedGateway = null;

        function selectGateway(id, element) {
            selectedGateway = id;
            document.querySelectorAll('.gateway-card').forEach(card => card.classList.remove('active'));
            element.querySelector('.gateway-card').classList.add('active');
            
            const payBtn = document.getElementById('btn-pay-now');
            payBtn.disabled = false;
            payBtn.classList.remove('btn-outline-primary');
            payBtn.classList.add('btn-primary');
        }

        function resetPayButton() {
            const btn = document.getElementById('btn-pay-now');
            const spinner = btn.querySelector('.spinner-border');
            const btnText = btn.querySelector('.btn-text');
            btn.disabled = false;
            spinner.classList.add('d-none');
            btnText.style.opacity = '1';
        }

        async function initiatePayment() {
            if (!selectedGateway) return;

            const btn = document.getElementById('btn-pay-now');
            const spinner = btn.querySelector('.spinner-border');
            const btnText = btn.querySelector('.btn-text');

            btn.disabled = true;
            spinner.classList.remove('d-none');
            btnText.style.opacity = '0.5';

            try {
                const response = await fetch('?gateway=' + selectedGateway + '&ajax=1');
                const result = await response.json();

                if (result.status === 'success') {
                    if (result.redirect_url) {
                        window.location.href = result.redirect_url;
                    } else {
                        window.location.href = '?gateway=' + selectedGateway;
                    }
                } else {
                    throw new Error(result.message || 'Payment initiation failed.');
                }
            } catch (error) {
                console.error(error);
                createToast({
                    title: 'Error',
                    description: error.message,
                    svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fa3939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-x"><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>`,
                    timeout: 5000
                });
                resetPayButton();
            }
        }

        function hitLanguage(){
            var language = document.querySelector("#model-languages").value;
            if(language !== ""){
                location.href = '?lang='+language;
            }
        }
    </script>
</body>
</html>
