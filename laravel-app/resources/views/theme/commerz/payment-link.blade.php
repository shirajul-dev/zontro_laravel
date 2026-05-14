@php
    if(request()->has('lang') && request('lang') != ""){
        if (function_exists('pp_set_lang')) {
            pp_set_lang(request('lang'));
        }
        echo "<script>sessionStorage.setItem('lang_changed', '1'); location.href = '" . url()->current() . "';</script>";
        exit();
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $lang['payment_link'] }} - {{ $brand['name'] }}</title>
    <link rel="shortcut icon" href="{{ $brand['favicon'] }}">
    {!! pp_assets('head') !!}

    <style>
        .btn-primary {
            --tblr-btn-border-color: transparent;
            --tblr-btn-hover-border-color: transparent;
            --tblr-btn-active-border-color: transparent;
            --tblr-btn-color: {{ $options['text_color'] ?? '#FFFFFF' }};
            --tblr-btn-bg: {{ $options['primary_color'] ?? '#5f38f9' }};
            --tblr-btn-hover-color: {{ $options['text_color'] ?? '#FFFFFF' }};
            --tblr-btn-hover-bg: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'] ?? '#5f38f9', 0.80) : ($options['primary_color'] ?? '#5f38f9') }};
        }
    </style>
</head>
<body style="background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <img src="{{ $brand['logo'] }}" alt="" style=" height: 40px; ">
        </div>
        <div class="card card-md">
          <div class="card-body text-center py-3 p-sm-4">
            @if(($paymentLink['status'] ?? '') !== 'active')
                <div class="mb-4 mt-4 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="#d63939" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.646 4.646a.5.5 0 0 0 0 .708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646a.5.5 0 0 0-.708 0z"/>
                    </svg>
                </div>
                <h2 class="mb-3 text-center">{{ $lang['product_not_active'] }}</h2>
                <p class="text-muted text-center">{{ $lang['product_not_active_text'] }}</p>
            @else
                <div style="text-align: right; cursor: pointer; color: {{ $options['primary_color'] }}" class="mb-2" data-bs-target="#modal-language" data-bs-toggle="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" style=" padding: 10px; background-color: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'], 0.05) : 'transparent' }}; border-radius: 100%; width: 40px; height: 40px; " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-language"><path d="M9 6.371c0 4.418 -2.239 6.629 -5 6.629" /><path d="M4 6.371h7" /><path d="M5 9c0 2.144 2.252 3.908 6 4" /><path d="M12 20l4 -9l4 9" /><path d="M19.1 18h-6.2" /><path d="M6.694 3l.793 .582" /></svg>
                </div>

                <h1>{{ $paymentLink['product']['title'] ?? '' }}</h1>
                <p class="text-secondary">{{ $paymentLink['product']['description'] ?? '' }}</p>

                <div class="card-body">
                    <form action="" method="POST" id="form">
                        {!! pp_renderFormFields('payment-link', $pageData) !!}
                        <button type="submit" id="payButton" class="btn btn-primary w-100">{{ $lang['pay_now'] }}</button>
                    </form>
                </div>
            @endif
          </div>
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
        function hitLanguage(){
            const language = document.querySelector("#model-languages").value;
            if(language !== ""){
                location.href = '?lang='+language;
            }
        }
        
        $(document).ready(function() {
            if(sessionStorage.getItem('lang_changed')){
                sessionStorage.removeItem('lang_changed');
                createToast({
                    title: 'Success',
                    description: 'Language changed successfully.',
                    svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2fb344" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-check"><path d="M5 12l5 5l10 -10" /></svg>`,
                    timeout: 4000
                });
            }

            $('#form').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#payButton');
                const originalHtml = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm"></span>');

                $.ajax({
                    url: '{{ url()->current() }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(data) {
                        if (data.status == "true") {
                            location.href = data.redirect;
                        } else {
                            btn.html(originalHtml);
                            alert(data.message || 'Payment failed');
                        }
                    },
                    error: function() {
                        btn.html(originalHtml);
                        alert('Something went wrong');
                    }
                });
            });
        });
    </script>
</body>
</html>
