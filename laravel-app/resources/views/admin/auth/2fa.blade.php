<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="author" content="{{ config('app.name') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Two-Factor Authentication - {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon-light.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/css/tabler.min.css') }}?v=1.5" />
    <style>
        @import url("{{ asset('assets/css/inter.css') }}");
        body {
            background-color: #f7f8fa;
            background-image:
                linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .code-input {
            width: 45px;
            height: 55px;
            font-size: 24px;
            text-align: center;
            border-radius: 8px;
            margin: 0 4px;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        .code-input:focus {
            border-color: #206bc4;
            box-shadow: 0 0 0 2px rgba(32, 107, 196, 0.1);
            outline: none;
        }
    </style>
</head>

<body class="d-flex flex-column" data-bs-theme="light">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <img src="{{ asset('assets/images/logo-light.png') }}" alt="" style="height: 40px;">
            </div>
            <form class="card card-md form-method" action="" method="post" autocomplete="off" style="border-radius: 0.75rem">
                <div class="card-body">
                    <h2 class="card-title text-center mb-2">Two-Factor Authentication</h2>
                    <p class="text-secondary text-center mb-4">Please enter the 6-digit code from your authenticator app.</p>
                    @csrf
                    <div class="d-flex justify-content-center mb-4">
                        <input type="text" name="code_one" class="code-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" name="code_two" class="code-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" name="code_three" class="code-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" name="code_four" class="code-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" name="code_five" class="code-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                        <input type="text" name="code_six" class="code-input" maxlength="1" pattern="\d*" inputmode="numeric" required>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">Verify Code</button>
                    </div>
                </div>
            </form>
            <div class="text-center text-secondary mt-3">
                Wait, I want to <a href="{{ route('native.auth.login', ['logout' => 1]) }}">login again</a>.
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery-3.6.4.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom-toast.js') }}?v=1.2"></script>
    <script>
        // Auto-focus logic for code inputs
        $('.code-input').on('input', function() {
            if ($(this).val().length === 1) {
                $(this).next('.code-input').focus();
            }
        });

        $('.code-input').on('keydown', function(e) {
            if (e.key === 'Backspace' && $(this).val().length === 0) {
                $(this).prev('.code-input').focus();
            }
        });

        $('.form-method').submit(function(e) {
            e.preventDefault();
            var btn = $(this).find('button[type="submit"]');
            var originalHtml = btn.html();
            btn.html('<div class="spinner-border spinner-border-sm" role="status"></div>').prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route("admin.2fa.verify") }}',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    btn.html(originalHtml).prop('disabled', false);
                    if (response.status === 'true') {
                        location.href = response.target;
                    } else {
                        createToast({
                            title: response.title,
                            description: response.message,
                            svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`,
                            timeout: 6000
                        });
                    }
                },
                error: function(xhr, status, error) {
                    btn.html(originalHtml).prop('disabled', false);
                    createToast({ title: 'Error', description: error });
                }
            });
        });
    </script>
</body>
</html>
