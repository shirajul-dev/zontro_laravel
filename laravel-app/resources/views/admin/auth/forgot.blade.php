<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="author" content="{{ config('app.name') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Forgot Password - {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon-light.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/css/tabler.min.css') }}?v=1.5" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-vendors.min.css" />

    <style>
        @import url("{{ asset('assets/css/inter.css') }}");
        body {
            background-color: #f7f8fa;
            background-image:
                linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
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
                    <h2 class="card-title text-center mb-4">Forgot password</h2>
                    <p class="text-secondary mb-4">Enter your email address and your password will be reset and emailed to you.</p>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><rect x="3" y="5" width="18" height="14" rx="2"></path><polyline points="3 7 12 13 21 7"></polyline></svg>
                            Send temporary password
                        </button>
                    </div>
                </div>
            </form>
            <div class="text-center text-secondary mt-3">
                Forget it, <a href="{{ route('native.auth.login') }}">send me back</a> to the sign in screen.
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery-3.6.4.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom-toast.js') }}?v=1.2"></script>
    <script>
        $('.form-method').submit(function(e) {
            e.preventDefault();
            var btn = $(this).find('button[type="submit"]');
            var originalHtml = btn.html();
            btn.html('<div class="spinner-border spinner-border-sm" role="status"></div>').prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route("native.forgot.post") }}',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    btn.html(originalHtml).prop('disabled', false);
                    if (response.status === 'true') {
                        createToast({
                            title: response.title,
                            description: response.message,
                            svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2fb344" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path d="M5 12l5 5l10 -10" /></svg>`,
                            timeout: 8000
                        });
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
