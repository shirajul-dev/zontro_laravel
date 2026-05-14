@php
    if(request()->has('lang') && request('lang') != ""){
        if (function_exists('pp_set_lang')) {
            pp_set_lang(request('lang'));
        }
        $checkoutUrl = function_exists('pp_checkout_address') ? pp_checkout_address() : url('/');
        $gatewayId = request('gateway');
        echo "<script>location.href = '{$checkoutUrl}?gateway={$gatewayId}';</script>";
        exit();
    }

    $gateway_info = pp_gateway_info(request('gateway'), $pageData);
    if($gateway_info['status'] == false){
        http_response_code(403);
        exit('Direct access not allowed');
    }

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
            --tblr-btn-color: {{ $gateway_info['gateway']['text_color'] }};
            --tblr-btn-bg: {{ $gateway_info['gateway']['primary_color'] }};
            --tblr-btn-hover-color: {{ $gateway_info['gateway']['text_color'] }};
            --tblr-btn-hover-bg: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($gateway_info['gateway']['primary_color'], 0.80) : $gateway_info['gateway']['primary_color'] }};
            --tblr-btn-active-color: {{ $gateway_info['gateway']['text_color'] }};
            --tblr-btn-active-bg: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($gateway_info['gateway']['primary_color'], 0.80) : $gateway_info['gateway']['primary_color'] }};
            --tblr-btn-disabled-bg: {{ $gateway_info['gateway']['primary_color'] }};
            --tblr-btn-disabled-color: {{ $gateway_info['gateway']['text_color'] }};
            --tblr-btn-box-shadow: {{ $gateway_info['gateway']['text_color'] }};
        }
        .form-control:focus{
            border-color: {{ $gateway_info['gateway']['primary_color'] }};
            box-shadow: var(--tblr-shadow-input), 0 0 0 .25rem {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($gateway_info['gateway']['primary_color'], 0.25) : 'transparent' }};
        }

        .payment-instructions{
            background-color: {{ $gateway_info['gateway']['primary_color'] }};
            color: {{ $gateway_info['gateway']['text_color'] }};
            border-radius: 10px;
            padding: 5px 20px;
            margin: 0px;
        }
        .payment-instructions li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 0;
            word-break: break-word;
            border-bottom: 1px solid {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($gateway_info['gateway']['text_color'], 0.25) : 'transparent' }};
        }

        .payment-instructions li .dot{
            width: 6px;
            height: 6px;
            border-radius: 100%;
            background-color: {{ $gateway_info['gateway']['text_color'] }};
            min-width: 6px;
        }
        .payment-instructions li p{
            margin: 0;
        }

        .payment-instructions li .dynamic-value{
            font-weight: 600;
        }

        .payment-instructions li svg{
            width: 17px;
            height: 17px;
        }
        .payment-instructions li .button-icon{
            padding: 5px;
            margin-left: 10px;
            background-color: {{ $gateway_info['gateway']['text_color'] }};
            color: {{ $gateway_info['gateway']['primary_color'] }};
            border-radius: 5px;
            cursor: pointer;
        }

        .payment-instructions li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body style="{{ $bgStyle }}" loading="lazy">
    <div class="container container-tight py-4">
        <div class="card">
          <div class="card-body">
              <div class="d-flex align-items-center justify-content-between border rounded p-2">
                  <div onclick="location.href='{{ function_exists('pp_checkout_address') ? pp_checkout_address() : url('/') }}'" style="text-align: right; cursor: pointer; color: {{ $options['primary_color'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" style=" padding: 6px; background-color: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'], 0.05) : 'transparent' }}; border-radius: 100%; width: 32px; height: 32px; " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-arrow-left"><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                  </div>
                  <div class="btns-group d-flex gap-2">
                      <div style="text-align: right; cursor: pointer; color: {{ $options['primary_color'] }}" data-bs-target="#modal-language" data-bs-toggle="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" style=" padding: 6px; background-color: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'], 0.05) : 'transparent' }}; border-radius: 100%; width: 32px; height: 32px; " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-language"><path d="M9 6.371c0 4.418 -2.239 6.629 -5 6.629" /><path d="M4 6.371h7" /><path d="M5 9c0 2.144 2.252 3.908 6 4" /><path d="M12 20l4 -9l4 9" /><path d="M19.1 18h-6.2" /><path d="M6.694 3l.793 .582" /></svg>
                      </div>
                  </div>
              </div>

              <center>
                  <img src="{{ $gateway_info['gateway']['logo'] }}" alt="" class="company-logo">
              </center>

              {!! pp_gateway_render(request('gateway', ''), $pageData) !!}
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
                            @foreach ($gateway_info['supported_languages'] as $code => $language)
                                <option value="{{ $code }}">{{ $language }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {!! pp_assets('footer') !!}

    <script>
        function hitLanguage(){
            var language = document.querySelector("#model-languages").value;
            if(language !== ""){
                location.href = '{{ function_exists('pp_checkout_address') ? pp_checkout_address() : url('/') }}?gateway={{ request('gateway') }}&lang='+language;
            }
        }
    </script>
</body>
</html>
