@php
    if(request()->has('lang') && request('lang') != ""){
        if (function_exists('pp_set_lang')) {
            pp_set_lang(request('lang'));
        }
        echo "<script>location.href = '?lang=';</script>";
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

    $amount = (float) ($transaction['amount'] ?? 0);
    $fee = (float) ($transaction['processing_fee'] ?? 0);
    $discount = (float) ($transaction['discount_amount'] ?? 0);
    $total = $amount + $fee - $discount;

    $currencyCode = $transaction['currency'] ?? $brand['currency_code'] ?? 'USD';
    $currencySymbol = $brand['currency_symbol'] ?? '';

    $allGateways = [
        'cards' => ($pp_gateways_global['status'] ?? false) ? ($pp_gateways_global['gateway'] ?? []) : [],
        'mobile' => ($pp_gateways_mfs['status'] ?? false) ? ($pp_gateways_mfs['gateway'] ?? []) : [],
        'banking' => ($pp_gateways_bank['status'] ?? false) ? ($pp_gateways_bank['gateway'] ?? []) : [],
    ];

    $defaultTab = 'mobile';
    if (empty($allGateways['mobile']) && !empty($allGateways['cards'])) {
        $defaultTab = 'cards';
    } elseif (empty($allGateways['mobile']) && empty($allGateways['cards']) && !empty($allGateways['banking'])) {
        $defaultTab = 'banking';
    }

    $bgStyle = 'background:#f2ece4;';
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

    $primaryColor = $options['primary_color'] ?? '#d88633';
    $textColor = $options['text_color'] ?? '#ffffff';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $lang['checkout'] }} - {{ $brand['name'] }}</title>
    <link rel="shortcut icon" href="{{ $brand['favicon'] }}">
    {!! pp_assets('head') !!}

    @if(!empty($options['seo_title']) && $options['seo_title'] !== '--')
        <meta name="title" content="{{ $options['seo_title'] }}">
    @endif
    @if(!empty($options['seo_description']) && $options['seo_description'] !== '--')
        <meta name="description" content="{{ $options['seo_description'] }}">
    @endif
    @if(!empty($options['seo_keywords']) && $options['seo_keywords'] !== '--')
        <meta name="keywords" content="{{ $options['seo_keywords'] }}">
    @endif
    @if(!empty($options['analytics_code']) && $options['analytics_code'] !== '--')
        {!! $options['analytics_code'] !!}
    @endif

    <style>
        :root {
            --cz-bg: #f2ece4;
            --cz-card: #ffffff;
            --cz-border: #e9e6e3;
            --cz-muted: #7e838f;
            --cz-text: #1f2430;
            --cz-primary: {{ $primaryColor }};
            --cz-primary-weak: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($primaryColor, 0.12) : 'rgba(0,0,0,0.1)' }};
            --cz-radius-lg: 16px;
            --cz-radius-md: 12px;
            --cz-radius-sm: 10px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Inter", "Segoe UI", -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--cz-text);
        }

        .cz-shell {
            max-width: 1120px;
            margin: 24px auto;
            border: 1px solid var(--cz-border);
            border-radius: var(--cz-radius-lg);
            background: var(--cz-card);
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(20, 27, 45, 0.08);
        }

        .cz-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            border-bottom: 1px solid var(--cz-border);
            background: linear-gradient(180deg, #fff 0%, #fcfbfa 100%);
        }

        .cz-brand { display: flex; align-items: center; gap: 10px; min-width: 0; }
        .cz-logo { width: 30px; height: 30px; border-radius: 8px; object-fit: cover; border: 1px solid var(--cz-border); }
        .cz-brand-name { font-size: 20px; font-weight: 700; line-height: 1; }
        .cz-brand-sub { font-size: 12px; color: var(--cz-muted); }

        .cz-badges { display: flex; align-items: center; gap: 18px; color: var(--cz-muted); font-size: 12px; font-weight: 600; }
        .cz-badge { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
        .cz-icon-dot { width: 8px; height: 8px; border-radius: 100%; background: #8ba97b; display: inline-block; }

        .cz-main { display: grid; grid-template-columns: minmax(0, 46%) minmax(0, 54%); }
        .cz-left, .cz-right { padding: 22px; }
        .cz-right { border-left: 1px solid var(--cz-border); background: linear-gradient(180deg, #fff 0%, #fdfdfd 100%); }

        .cz-merchant {
            border: 1px solid var(--cz-border);
            border-radius: var(--cz-radius-md);
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .cz-merchant-left { display: flex; align-items: center; gap: 10px; }
        .cz-merchant-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            border: 1px solid var(--cz-border);
            color: var(--cz-primary);
            background: #fff7ef;
            font-size: 16px;
            font-weight: 700;
        }

        .cz-merchant-name { font-size: 18px; font-weight: 700; }
        .cz-merchant-ref { font-size: 12px; color: var(--cz-muted); }
        .cz-merchant-verified {
            font-size: 12px;
            color: #2f9e44;
            background: #eaf9ee;
            border-radius: 999px;
            padding: 2px 8px;
            margin-left: 8px;
            font-weight: 700;
        }

        .cz-timer {
            font-size: 12px;
            color: var(--cz-muted);
            background: #f7f7f7;
            border-radius: 999px;
            padding: 6px 10px;
            border: 1px solid var(--cz-border);
        }

        .cz-amount-box {
            border: 1px solid var(--cz-border);
            border-radius: var(--cz-radius-md);
            padding: 16px;
            background: radial-gradient(circle at right top, #f8f2eb 0, #fff 43%);
        }

        .cz-kicker {
            font-size: 11px;
            color: var(--cz-muted);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .cz-amount { margin-top: 6px; font-size: 48px; line-height: 1; font-weight: 800; letter-spacing: -0.02em; }
        .cz-amount small { font-size: 30px; color: #646a75; }
        .cz-currency { margin-top: 8px; font-size: 13px; color: var(--cz-muted); }

        .cz-breakdown { margin-top: 18px; border-top: 1px solid var(--cz-border); border-bottom: 1px solid var(--cz-border); }
        .cz-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 11px 0; font-size: 14px; border-bottom: 1px solid #f2f0ee; }
        .cz-row:last-child { border-bottom: 0; }
        .cz-label { color: #5f6570; }
        .cz-value { font-weight: 700; color: #303644; }
        .cz-promo { color: var(--cz-primary); font-weight: 700; text-decoration: none; }

        .cz-total { margin-top: 12px; display: flex; align-items: baseline; justify-content: space-between; font-size: 16px; }
        .cz-total strong { font-size: 36px; line-height: 1; letter-spacing: -0.02em; }

        .cz-emi {
            margin-top: 14px;
            background: #edf3ff;
            border: 1px solid #d8e4ff;
            border-radius: var(--cz-radius-sm);
            padding: 10px 12px;
            color: #2d4674;
            font-size: 12px;
            font-weight: 600;
        }

        .cz-tabs {
            border: 1px solid var(--cz-border);
            border-radius: var(--cz-radius-sm);
            padding: 4px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 4px;
            background: #f8f7f6;
        }

        .cz-tab {
            border: 0;
            background: transparent;
            border-radius: 8px;
            padding: 9px 8px;
            font-size: 12px;
            font-weight: 700;
            color: #6a707a;
            cursor: pointer;
        }

        .cz-tab.is-active {
            background: var(--cz-primary);
            color: {{ $textColor }};
            box-shadow: 0 8px 18px {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($primaryColor, 0.28) : 'rgba(0,0,0,0.2)' }};
        }

        .cz-wallet-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 18px; }

        .cz-wallet {
            border: 1px solid var(--cz-border);
            background: #fff;
            border-radius: var(--cz-radius-sm);
            padding: 10px;
            min-height: 88px;
            text-align: center;
            cursor: pointer;
            transition: all .18s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .cz-wallet:hover { border-color: #d8d3ce; transform: translateY(-1px); }
        .cz-wallet.is-selected { border-color: var(--cz-primary); box-shadow: 0 0 0 2px var(--cz-primary-weak); }

        .cz-wallet-logo {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            object-fit: contain;
            background: #f7f7f8;
            border: 1px solid #ece9e6;
            padding: 3px;
        }

        .cz-wallet-name { font-size: 12px; font-weight: 700; color: #3a4050; line-height: 1.2; }

        .cz-empty {
            border: 1px dashed #d9d4cf;
            border-radius: var(--cz-radius-sm);
            padding: 18px;
            color: var(--cz-muted);
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            margin-top: 18px;
        }

        .cz-pay {
            margin-top: 14px;
            width: 100%;
            height: 50px;
            border-radius: 11px;
            border: 0;
            background: var(--cz-primary);
            color: {{ $textColor }};
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 24px {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($primaryColor, 0.28) : 'rgba(0,0,0,0.2)' }};
        }

        .cz-pay.is-disabled { opacity: .45; pointer-events: none; box-shadow: none; }

        @media (max-width: 980px) {
            .cz-main { grid-template-columns: 1fr; }
            .cz-right { border-left: 0; border-top: 1px solid var(--cz-border); }
            .cz-amount { font-size: 40px; }
        }

        @media (max-width: 720px) {
            .cz-shell { margin: 12px; }
            .cz-topbar { padding: 12px; align-items: flex-start; gap: 8px; flex-direction: column; }
            .cz-badges { gap: 10px; font-size: 11px; flex-wrap: wrap; }
            .cz-left, .cz-right { padding: 14px; }
            .cz-wallet-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .cz-tabs { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .cz-amount { font-size: 34px; }
            .cz-total strong { font-size: 30px; }
        }
    </style>
</head>
<body style="{{ $bgStyle }}" loading="lazy">
    <div class="cz-shell">
        <div class="cz-topbar">
            <div class="cz-brand">
                <img class="cz-logo" src="{{ $brand['favicon'] }}" alt="Logo">
                <div>
                    <div class="cz-brand-name">{{ $brand['name'] }}</div>
                    <div class="cz-brand-sub">Secure Payment Gateway</div>
                </div>
            </div>
            <div class="cz-badges">
                <span class="cz-badge"><span class="cz-icon-dot"></span> 256-bit SSL Encrypted</span>
                <span class="cz-badge"><span class="cz-icon-dot"></span> PCI DSS Compliant</span>
            </div>
        </div>

        <div class="cz-main">
            <section class="cz-left">
                <div class="cz-merchant">
                    <div class="cz-merchant-left">
                        <div class="cz-merchant-icon">{{ substr($brand['name'], 0, 1) }}</div>
                        <div>
                            <div class="cz-merchant-name">
                                {{ $brand['name'] }}
                                <span class="cz-merchant-verified">Verified</span>
                            </div>
                            <div class="cz-merchant-ref">{{ $transaction['ref'] }}</div>
                        </div>
                    </div>
                    <div class="cz-timer">06:29</div>
                </div>

                <div class="cz-amount-box">
                    <div class="cz-kicker">Payment Amount</div>
                    <div class="cz-amount">
                        {{ $currencySymbol }}{{ number_format($total, 0) }}<small>.00</small>
                    </div>
                    <div class="cz-currency">{{ $currencyCode }} - {{ $lang['pay_now'] }}</div>
                </div>

                <div class="cz-breakdown">
                    <div class="cz-row">
                        <span class="cz-label">Subtotal</span>
                        <span class="cz-value">{{ $currencySymbol }}{{ number_format($amount, 2) }}</span>
                    </div>
                    <div class="cz-row">
                        <span class="cz-label">Convenience Fee</span>
                        <span class="cz-value">{{ $currencySymbol }}{{ number_format($fee, 2) }}</span>
                    </div>
                    @if($discount > 0)
                        <div class="cz-row">
                            <span class="cz-label">Discount</span>
                            <span class="cz-value">-{{ $currencySymbol }}{{ number_format($discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="cz-row">
                        <a href="javascript:void(0)" class="cz-promo">Apply Promo Code</a>
                        <span></span>
                    </div>
                </div>

                <div class="cz-total">
                    <span class="cz-label">Total</span>
                    <strong>{{ $currencySymbol }}{{ number_format($total, 2) }}</strong>
                </div>

                <div class="cz-emi">
                    EMI available from {{ $currencySymbol }}13,335/mo
                    <br>
                    View participating banks ->
                </div>
            </section>

            <section class="cz-right">
                <div class="cz-right-head">
                    <div>
                        <h3>Choose Payment Method</h3>
                        <p>All transactions are secure and encrypted</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-language">Lang</button>
                        <button type="button" class="btn btn-sm btn-outline-danger ms-1" onclick="location.href='?cancel'">Cancel</button>
                    </div>
                </div>

                <div class="cz-tabs" id="paymentTabs">
                    <button type="button" class="cz-tab" data-tab="cards">Cards</button>
                    <button type="button" class="cz-tab" data-tab="mobile">Mobile</button>
                    <button type="button" class="cz-tab" data-tab="banking">Banking</button>
                    <button type="button" class="cz-tab" data-tab="more">More</button>
                </div>

                <div id="walletGrid" class="cz-wallet-grid"></div>
                <div id="emptyState" class="cz-empty" style="display:none;">No payment options in this category yet.</div>

                <a id="payNowBtn" class="cz-pay is-disabled" href="javascript:void(0)">
                    <span>Pay {{ $currencySymbol }}{{ number_format($total, 2) }}</span>
                </a>

                <p class="cz-policy">
                    By proceeding, you agree to our
                    <a href="javascript:void(0)">Terms</a> and
                    <a href="javascript:void(0)">Privacy Policy</a>.
                </p>
            </section>
        </div>
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
                    <label class="form-label">{{ $lang['language'] }}</label>
                    <select class="form-select" id="model-languages" onchange="hitLanguage()">
                        <option value="">{{ $lang['select_a_language'] }}</option>
                        <option value="en">English</option>
                        <option value="bn">বাংলা</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {!! pp_assets('footer') !!}

    <script>
        (function () {
            const gateways = {
                cards: {!! json_encode(array_values($allGateways['cards'])) !!},
                mobile: {!! json_encode(array_values($allGateways['mobile'])) !!},
                banking: {!! json_encode(array_values($allGateways['banking'])) !!},
                more: []
            };

            const tabs = document.querySelectorAll('.cz-tab');
            const walletGrid = document.getElementById('walletGrid');
            const emptyState = document.getElementById('emptyState');
            const payBtn = document.getElementById('payNowBtn');

            let activeTab = '{{ $defaultTab }}';
            let selectedUrl = '';

            function setActiveTab(tabName) {
                activeTab = tabName;
                tabs.forEach((tab) => tab.classList.toggle('is-active', tab.dataset.tab === tabName));
                selectedUrl = '';
                renderWallets();
                updatePayButton();
            }

            function renderWallets() {
                const list = gateways[activeTab] || [];
                walletGrid.innerHTML = '';

                if (!list.length) {
                    walletGrid.style.display = 'none';
                    emptyState.style.display = 'block';
                    return;
                }

                walletGrid.style.display = 'grid';
                emptyState.style.display = 'none';

                list.forEach((item) => {
                    const card = document.createElement('button');
                    card.type = 'button';
                    card.className = 'cz-wallet';
                    card.addEventListener('click', () => {
                        document.querySelectorAll('.cz-wallet').forEach((el) => el.classList.remove('is-selected'));
                        card.classList.add('is-selected');
                        selectedUrl = '?gateway=' + encodeURIComponent(item.gateway_id);
                        updatePayButton();
                    });

                    const img = document.createElement('img');
                    img.className = 'cz-wallet-logo';
                    img.src = item.logo;

                    const title = document.createElement('div');
                    title.className = 'cz-wallet-name';
                    title.textContent = item.display;

                    card.appendChild(img);
                    card.appendChild(title);
                    walletGrid.appendChild(card);
                });
            }

            function updatePayButton() {
                if (!selectedUrl) {
                    payBtn.classList.add('is-disabled');
                    payBtn.href = 'javascript:void(0)';
                } else {
                    payBtn.classList.remove('is-disabled');
                    payBtn.href = selectedUrl;
                }
            }

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => setActiveTab(tab.dataset.tab));
            });

            setActiveTab(activeTab);
        })();

        function hitLanguage(){
            const language = document.querySelector("#model-languages").value;
            if(language !== ""){
                location.href = '?lang='+language;
            }
        }
    </script>
</body>
</html>
