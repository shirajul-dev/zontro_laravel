@php
    if(request()->has('lang') && request('lang') != ""){
        if (function_exists('pp_set_lang')) {
            pp_set_lang(request('lang'));
        }
        echo "<script>location.href = '?lang=';</script>";
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
        .padding-1{ padding: 30px 40px; }
        .padding-2{ padding: 0 40px 40px 40px; }

        @media only screen and (max-width: 600px) {
            .container{ margin: 0px !important; }
            .padding-1{ padding: 20px 10px; }
            .padding-2{ padding: 0 10px 20px 10px; }
        }

        .btn-primary {
            --tblr-btn-border-color: transparent;
            --tblr-btn-hover-border-color: transparent;
            --tblr-btn-active-border-color: transparent;
            --tblr-btn-color: {{ $options['text_color'] ?? '#FFFFFF' }};
            --tblr-btn-bg: {{ $options['primary_color'] ?? '#d88633' }};
            --tblr-btn-hover-color: {{ $options['text_color'] ?? '#FFFFFF' }};
            --tblr-btn-hover-bg: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'] ?? '#d88633', 0.80) : ($options['primary_color'] ?? '#d88633') }};
        }
    </style>
</head>
<body style="background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <div class="container" style="max-width: 1000px; margin: 0 auto; background: white; border-radius: 4px; box-shadow: 0 2px 15px rgba(0,0,0,0.08);">
        <div class="padding-1">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <img src="{{ $brand['logo'] }}" alt="" style=" height: 40px; ">
                <div style="cursor: pointer; color: {{ $options['primary_color'] }}" data-bs-target="#modal-language" data-bs-toggle="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" style=" padding: 10px; background-color: {{ function_exists('pp_hexToRgba') ? pp_hexToRgba($options['primary_color'], 0.05) : 'transparent' }}; border-radius: 100%; width: 40px; height: 40px; " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler-language"><path d="M9 6.371c0 4.418 -2.239 6.629 -5 6.629" /><path d="M4 6.371h7" /><path d="M5 9c0 2.144 2.252 3.908 6 4" /><path d="M12 20l4 -9l4 9" /><path d="M19.1 18h-6.2" /><path d="M6.694 3l.793 .582" /></svg>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; border-top: 1px solid #dee2e6; padding-top: 20px; margin-top: 20px;">
                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <div>
                        <div style="font-size: 0.85rem; color: #6c757d; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.5px;">{{ $lang['invoice_date'] }}</div>
                        <div style="font-size: 1rem; font-weight: 600; color: #2c3e50;">{{ $invoice['created_date'] }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: #6c757d; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.5px;">{{ $lang['due_date'] }}</div>
                        <div style="font-size: 1rem; font-weight: 600; color: #2c3e50;">{{ $invoice['due_date'] }}</div>
                    </div>
                </div>
                
                <div style="padding: 6px 15px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; background-color: {{ $invoice['status'] == 'paid' ? '#2fb3442e' : 'rgba(231, 76, 60, 0.1)' }}; color: {{ $invoice['status'] == 'paid' ? '#2fb344' : '#e74c3c' }}; margin-top: 10px;">
                    {{ $lang['badge_' . $invoice['status']] }}
                </div>
            </div>
        </div>
        
        <div class="padding-2">
            <div class="row">
                <div class="col-lg-6" style="margin-bottom: 20px;">
                    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; border-left: 3px solid {{ $options['primary_color'] }};">
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; font-size: 1rem;">{{ $lang['bill_from'] }}</div>
                        <strong>{{ $brand['name'] }}</strong>
                        <div style="height:10px;"></div>
                        <address style="margin-bottom: 0;">
                            <strong>{{ $lang['email'] }}:</strong> {{ $brand['support']['email'] }}<br>
                            <strong>{{ $lang['phone'] }}:</strong> {{ $brand['support']['phone'] }}
                        </address>
                    </div>
                </div>
                <div class="col-lg-6" style="margin-bottom: 20px;">
                    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; border-left: 3px solid {{ $options['primary_color'] }};">
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; font-size: 1rem;">{{ $lang['bill_to'] }}</div>
                        <strong>{{ $invoice['customer']['name'] ?? 'N/A' }}</strong>
                        <div style="height:10px;"></div>
                        <address style="margin-bottom: 0;">
                            <strong>{{ $lang['email'] }}:</strong> {{ $invoice['customer']['email'] ?? 'N/A' }}<br>
                            <strong>{{ $lang['phone'] }}:</strong> {{ $invoice['customer']['mobile'] ?? 'N/A' }}
                        </address>
                    </div>
                </div>
            </div>

            <div class="table-responsive" style="margin-top: 30px;">
                <table class="table" style="width: 100%; margin-bottom: 0;">
                    <thead>
                        <tr style="background-color: #f8f9fa; color: #2c3e50; font-weight: 600; border-bottom: 1px solid #dee2e6;">
                            <th style="padding: 15px; text-align: center;">#</th>
                            <th style="padding: 15px;">{{ $lang['description'] }}</th>
                            <th style="padding: 15px; text-align: center;">{{ $lang['qty'] }}</th>
                            <th style="padding: 15px; text-align: right;">{{ $lang['unit_price'] }}</th>
                            <th style="padding: 15px; text-align: right;">{{ $lang['amount'] }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $index => $item)
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 15px; text-align: center;">{{ $index + 1 }}</td>
                                <td style="padding: 15px;">{{ $item['description'] }}</td>
                                <td style="padding: 15px; text-align: center;">{{ $item['quantity'] }}</td>
                                <td style="padding: 15px; text-align: right;">{{ number_format($item['amount'], 2) }}{{ $invoice['currency'] }}</td>
                                <td style="padding: 15px; text-align: right;">{{ number_format($item['amount'] * $item['quantity'], 2) }}{{ $invoice['currency'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 25px; margin-top: 30px;">
                <div class="row">
                    <div class="col-md-8">
                        <h4 style="color: #2c3e50;">{{ $lang['note'] }}</h4>
                        <p style="color: #6c757d; margin-bottom: 0;">{{ $invoice['note'] }}</p>
                    </div>
                    <div class="col-md-4">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>{{ $lang['subtotal'] }}:</span>
                            <span>{{ number_format($totals['sub_total'], 2) }}{{ $invoice['currency'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>{{ $lang['tax'] }}:</span>
                            <span>{{ number_format($totals['total_vat'], 2) }}{{ $invoice['currency'] }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-weight: 600; font-size: 1.2rem; border-top: 1px solid #dee2e6; padding-top: 10px;">
                            <span>{{ $lang['total'] }}:</span>
                            <span>{{ number_format($totals['grand_total'], 2) }}{{ $invoice['currency'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row" style="margin-top: 30px;">
                <div class="col-md-12 d-flex justify-content-end gap-2">
                    <button onclick="window.print()" class="btn btn-outline-secondary no-print">Print</button>
                    @if($invoice['status'] == 'unpaid')
                        <form action="" method="POST" id="form">
                            {!! pp_renderFormFields('invoice', $pageData) !!}
                            <button type="submit" id="payButton" class="btn btn-primary">Pay Now</button>
                        </form>
                    @endif
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
