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
    <title>{{ $lang['invoice'] }} - {{ $brand['name'] }}</title>
    <link rel="shortcut icon" href="{{ $brand['favicon'] }}">
    
    {!! pp_assets('head') !!}

    <style>
        .container{
            margin-top: 20px !important;
            margin-bottom: 20px !important;
        }
        .padding-1{
            padding: 20px 10px;
        }
        .padding-2{
            padding: 0 10px 20px 10px;
        }

        @media only screen and (min-width: 768px) {
            .padding-1{
                padding: 30px 40px;
            }
            .padding-2{
                padding: 0 40px 40px 40px;
            }
        }

        @media only screen and (max-width: 600px) {
            .container{
                margin: 0px !important;
            }
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
    </style>
</head>
<body style="background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <div class="container" style="max-width: 1000px; margin: 0 auto; background: white; border-radius: 4px; box-shadow: 0 2px 15px rgba(0,0,0,0.08);">
        <div class="padding-1">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <img src="{{ $brand['logo'] }}" alt="" style=" height: 40px; ">

                <div style="cursor: pointer; color: {{ $options['primary_color'] }}" class="mb-2" data-bs-target="#modal-language" data-bs-toggle="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" style=" padding: 10px; background-color: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'], 0.05) : 'transparent' }}; border-radius: 100%; width: 40px; height: 40px; " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-language"><path d="M9 6.371c0 4.418 -2.239 6.629 -5 6.629" /><path d="M4 6.371h7" /><path d="M5 9c0 2.144 2.252 3.908 6 4" /><path d="M12 20l4 -9l4 9" /><path d="M19.1 18h-6.2" /><path d="M6.694 3l.793 .582" /></svg>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; border-top: 1px solid #dee2e6; padding-top: 20px; margin-top: 20px;">
                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <div>
                        <div style="font-size: 0.85rem; color: #6c757d; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.5px;"> {{ $lang['invoice_date'] }}</div>
                        <div style="font-size: 1rem; font-weight: 600; color: #2c3e50;">{{ $invoice['created_date'] }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: #6c757d; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.5px;"> {{ $lang['due_date'] }}</div>
                        <div style="font-size: 1rem; font-weight: 600; color: #2c3e50;">{{ $invoice['due_date'] }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: #6c757d; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.5px;"> {{ $lang['payment_method'] }}</div>
                        <div style="font-size: 1rem; font-weight: 600; color: #2c3e50;">{{ ($invoice['status'] == "paid") ? $invoice['gateway'] : '' }}</div>
                    </div>
                </div>
                
                @if($invoice['status'] == "paid")
                    <div style="padding: 6px 15px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; background-color: #2fb3442e; color: #2fb344; margin-top: 10px;">
                        {{ $lang['badge_' . $invoice['status']] }}
                    </div>
                @else
                    <div style="padding: 6px 15px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; background-color: rgba(231, 76, 60, 0.1); color: #e74c3c; margin-top: 10px;">
                        {{ $lang['badge_' . $invoice['status']] }}
                    </div>
                @endif
            </div>
        </div>
        
        <div class="padding-2" style="position: relative; z-index: 0;">
            <div class="row" style="margin-top: 10px;">
                <div class="col-lg-6" style="margin-bottom: 20px;">
                    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; border-left: 3px solid {{ $options['primary_color'] }};">
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; font-size: 1rem; display: flex; align-items: center;">
                             {{ $lang['bill_from'] }}
                        </div>
                        <strong style="color: #2c3e50;">{{ $brand['name'] }}</strong>
                        <div style="height:10px;"></div>
                        <address style="margin-bottom: 0;">
                            <strong> {{ $lang['email'] }}:</strong> {{ $brand['support']['email'] }}<br>
                            <strong> {{ $lang['phone'] }}:</strong> {{ $brand['support']['phone'] }}
                        </address>
                    </div>
                </div>
                <div class="col-lg-6" style="margin-bottom: 20px;">
                    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; border-left: 3px solid {{ $options['primary_color'] }};">
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; font-size: 1rem; display: flex; align-items: center;">
                             {{ $lang['bill_to'] }}
                        </div>
                        <strong style="color: #2c3e50;">{{ $invoice['customer']['name'] }}</strong>
                        <div style="height:10px;"></div>
                        <address style="margin-bottom: 0;">
                            <strong> {{ $lang['email'] }}:</strong> {{ $invoice['customer']['email'] }}<br>
                            <strong> {{ $lang['phone'] }}:</strong> {{ $invoice['customer']['mobile'] }}
                        </address>
                    </div>
                </div>
            </div>

            <div class="table-responsive" style="margin-top: 30px;">
                <table class="table" style="width: 100%; margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 5%; background-color: #f8f9fa; color: #2c3e50; padding: 15px; font-weight: 600; border-bottom: 1px solid #dee2e6; text-align: center;">#</th>
                            <th style="width: 45%; background-color: #f8f9fa; color: #2c3e50; padding: 15px; font-weight: 600; border-bottom: 1px solid #dee2e6;"> {{ $lang['description'] }}</th>
                            <th style="width: 10%; background-color: #f8f9fa; color: #2c3e50; padding: 15px; font-weight: 600; border-bottom: 1px solid #dee2e6; text-align: center;"> {{ $lang['qty'] }}</th>
                            <th style="width: 20%; background-color: #f8f9fa; color: #2c3e50; padding: 15px; font-weight: 600; border-bottom: 1px solid #dee2e6; text-align: right;"> {{ $lang['unit_price'] }}</th>
                            <th style="width: 20%; background-color: #f8f9fa; color: #2c3e50; padding: 15px; font-weight: 600; border-bottom: 1px solid #dee2e6; text-align: right;"> {{ $lang['amount'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $subtotal = 0;
                            $totalDiscount = 0;
                            $totalVAT = 0;
                            $grandTotal = 0;
                        @endphp

                        @if(!empty($items))
                            @foreach ($items as $index => $item)
                                @php
                                    $itemTotalBeforeDiscount = $item['unitPrice'] * $item['quantity'];
                                    $discountAmount = $item['discount'] ?? 0;
                                    $priceAfterDiscount = $itemTotalBeforeDiscount - $discountAmount;
                                    $vatAmount = $priceAfterDiscount * ($item['vat'] / 100);
                                    $itemFinalTotal = $priceAfterDiscount + $vatAmount;

                                    $subtotal += $itemTotalBeforeDiscount;
                                    $totalDiscount += $discountAmount;
                                    $totalVAT += $vatAmount;
                                    $grandTotal += $itemFinalTotal;
                                @endphp
                                <tr style="background-color: rgba(52, 152, 219, 0.03);">
                                    <td style="padding: 15px; border-bottom: 1px solid #dee2e6; text-align: center; vertical-align: middle;">{{ $index + 1 }}</td>
                                    <td style="padding: 15px; border-bottom: 1px solid #dee2e6; vertical-align: middle;">
                                        <div style="color: #6c757d; font-size: 0.9rem;">{{ $item['description'] }}</div>
                                    </td>
                                    <td style="padding: 15px; border-bottom: 1px solid #dee2e6; text-align: center; vertical-align: middle;">{{ $item['quantity'] }}</td>
                                    <td style="padding: 15px; border-bottom: 1px solid #dee2e6; text-align: right; vertical-align: middle;">{{ number_format($item['unitPrice'], 2) }}{{ $invoice['currency'] }}</td>
                                    <td style="padding: 15px; border-bottom: 1px solid #dee2e6; text-align: right; vertical-align: middle;">{{ number_format($itemFinalTotal, 2) }}{{ $invoice['currency'] }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            
            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 25px; margin-top: 30px;">
                <div class="row">
                    <div class="col-md-8">
                        <div style="margin-bottom: 15px;">
                            <h4 style="color: #2c3e50;"> {{ $lang['note'] }}</h4>
                            <p style="color: #6c757d; margin-bottom: 0;">
                                {{ $invoice['note'] }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1rem;">
                            <span> {{ $lang['subtotal'] }}:</span>
                            <span>{{ number_format($subtotal, 2) }}{{ $invoice['currency'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1rem;">
                            <span> {{ $lang['shipping'] }}:</span>
                            <span>{{ number_format($invoice['shippingFee'], 2) }}{{ $invoice['currency'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1rem;">
                            <span> {{ $lang['tax'] }}:</span>
                            <span>{{ number_format($totalVAT, 2) }}{{ $invoice['currency'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1rem;">
                            <span> {{ $lang['discount'] }}:</span>
                            <span>{{ number_format($totalDiscount, 2) }}{{ $invoice['currency'] }}</span>
                        </div>
                        
                        @if($invoice['status'] == "paid")
                            <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 600; color: #2fb344; border-top: 2px solid #dee2e6; padding-top: 15px; margin-top: 15px;">
                                <span> {{ $lang['total'] }}:</span>
                                <span>{{ number_format($grandTotal + $invoice['shippingFee'], 2) }}{{ $invoice['currency'] }}</span>
                            </div>
                        @else
                            <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 600; color: #e74c3c; border-top: 2px solid #dee2e6; padding-top: 15px; margin-top: 15px;">
                                <span> {{ $lang['total_due'] }}:</span>
                                <span>{{ number_format($grandTotal + $invoice['shippingFee'], 2) }}{{ $invoice['currency'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="row" style="margin-top: 30px; align-items: center;">
                <div class="col-md-12 d-flex flex-md-row-reverse justify-content-md-start justify-content-center align-items-center gap-3">
                    <button onclick="window.print()" class="btn btn-success no-print">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-printer"><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 15a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2l0 -4" /></svg> {{ $lang['print_invoice'] }}
                    </button>
                    @if($invoice['status'] == "unpaid")
                        <form action="" method="POST" id="form" enctype="multipart/form-data">
                            @php pp_renderFormFields('invoice', $pageData); @endphp
                            <button id="payButton" class="btn btn-primary no-print">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-credit-card"><path d="M3 8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3l0 -8" /><path d="M3 10l18 0" /><path d="M7 15l.01 0" /><path d="M11 15l2 0" /></svg> {{ $lang['pay_now'] }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            
            <div style="background-color: #f8f9fa; padding: 25px; text-align: center; margin-top: 30px;">
                <div class="row">
                    <div class="col-md-12">
                        <p style="color: #6c757d; margin-bottom: 0;">
                            {{ $brand['name'] }} • {{ $brand['address']['street'] }}, {{ $brand['address']['city'] }} - {{ $brand['address']['postal'] }} • {{ $brand['address']['country'] }}
                        </p>
                        <p style="color: #6c757d; margin-bottom: 0; margin-top: 10px; font-size: 0.9rem;">
                            {{ $lang['no_signature'] }}
                        </p>
                    </div>
                </div>
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
        function hitLanguage(){
            var language = document.querySelector("#model-languages").value;
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
                var formData = $(this).serialize(); 
                document.querySelector("#payButton").innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';

                $.ajax({
                    url: '{{ url('/') }}',
                    type: 'POST',
                    dataType: 'json',
                    data: formData, 
                    success: function(data) {
                        document.querySelector("#payButton").innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-credit-card"><path d="M3 8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3l0 -8" /><path d="M3 10l18 0" /><path d="M7 15l.01 0" /><path d="M11 15l2 0" /></svg> {{ $lang['pay_now'] }}';
                        if (data.status == "true") {
                            location.href = data.redirect;
                        } else {
                            createToast({
                                title: data.title,
                                description: data.message,
                                svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-exclamation-circle"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`,
                                timeout: 6000
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        createToast({
                            title: 'Something Wrong!',
                            description: 'For further assistance, please contact our support team.',
                            svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-exclamation-circle"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`,
                            timeout: 6000
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
