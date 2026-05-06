<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="author" content="{{ config('app.name') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - {{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon-light.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/css/tabler.min.css') }}?v=1.5" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-vendors.min.css" />

    <style>
        @import url("{{ asset('assets/css/inter.css') }}");

    </style>
    <style>
        body {
            /* Subtle grid background effect */
            background-color: #f7f8fa;
            background-image:
                linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            /* background-color: #f7f8fa;
            background-image: url("https://images.hdqwalls.com/wallpapers/blured-background.jpg"); */

        }

    </style>
    <style>
        :root {
            --tblr-font-monospace: Monaco, Consolas, Liberation Mono, Courier New, monospace;
            --tblr-font-sans-serif: Inter Var, Inter, -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
            --tblr-font-serif: Georgia, Times New Roman, times, serif;
            --tblr-font-comic: Comic Sans MS, Comic Sans, Chalkboard SE, Comic Neue, sans-serif, cursive;
        }

    </style>
</head>

<body cz-shortcut-listen="true" data-bs-theme=light>
    <div class="page page-center">
        <div class="container container-tight py-4">

            <div class="text-center mb-4">
                <img src="{{ asset('assets/images/logo-light.png') }}" alt="" style=" height: 40px; ">
            </div>
            <h2 class="h2 text-center mb-4">Login to your account</h2>

            <div class="card card-md" style="border-radius: 0.75rem">
                <div class="card-body">
                    <form action="" class="form-method">
                        <input type="hidden" name="action" value="login">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Email or Username</label>
                            <input type="text" class="form-control" name="username" placeholder="Enter email or username" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">
                                Password
                                <span class="form-label-description">
                                    <a href="{{ url('forgot') }}">I forgot password</a>
                                </span>
                            </label>
                            <div class="input-group input-group-flat">
                                <input type="password" class="form-control password-input" name="password" placeholder="Enter password" required>

                                <span class="input-group-text password-toggle" onclick="togglePassword(this)">
                                    <a href="javascript:void(0)" class="link-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="Show password">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-eye">
                                            <path d="M10 12a2 2 0 1 0 4 0"></path>
                                            <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"></path>
                                        </svg>
                                    </a>
                                </span>
                            </div>
                        </div>
                        <div class="">
                            <label class="form-check">
                                <input type="checkbox" class="form-check-input">
                                <span class="form-check-label">Remember me</span>
                            </label>
                        </div>
                        <div class="form-footer" style="margin-top: 1.5rem">
                            <button type="submit" class="btn btn-primary w-100">Sign in</button>
                        </div>
                    </form>

                    <!-- Social login section -->
                    <div class="text-center" style="margin-top:1.5rem;">
                        <div class="d-flex align-items-center mb-3" style="gap:12px;">
                            <div style="flex:1;height:1px;background:#ececec;"></div>
                            <div style="padding:0 12px;color:#bbb;font-size:0.95rem;">Or continue with</div>
                            <div style="flex:1;height:1px;background:#ececec;"></div>
                        </div>
                        <div class="d-flex justify-content-center gap-3 mb-2" style="gap:16px;">
                            <button type="button" class="btn" style="background:#f5f5f5;border-radius:0.5rem;min-width:120px;display:flex;align-items:center;justify-content:center;font-weight:500;color:#222;border:1px solid #eee;gap:8px;">
                                <svg viewBox="0 0 24 24" aria-hidden="true" class="aegn aegu" width="20" height="20">
                                    <path d="M12.0003 4.75C13.7703 4.75 15.3553 5.36002 16.6053 6.54998L20.0303 3.125C17.9502 1.19 15.2353 0 12.0003 0C7.31028 0 3.25527 2.69 1.28027 6.60998L5.27028 9.70498C6.21525 6.86002 8.87028 4.75 12.0003 4.75Z" fill="#EA4335"></path>
                                    <path d="M23.49 12.275C23.49 11.49 23.415 10.73 23.3 10H12V14.51H18.47C18.18 15.99 17.34 17.25 16.08 18.1L19.945 21.1C22.2 19.01 23.49 15.92 23.49 12.275Z" fill="#4285F4"></path>
                                    <path d="M5.26498 14.2949C5.02498 13.5699 4.88501 12.7999 4.88501 11.9999C4.88501 11.1999 5.01998 10.4299 5.26498 9.7049L1.275 6.60986C0.46 8.22986 0 10.0599 0 11.9999C0 13.9399 0.46 15.7699 1.28 17.3899L5.26498 14.2949Z" fill="#FBBC05"></path>
                                    <path d="M12.0004 24.0001C15.2404 24.0001 17.9654 22.935 19.9454 21.095L16.0804 18.095C15.0054 18.82 13.6204 19.245 12.0004 19.245C8.8704 19.245 6.21537 17.135 5.2654 14.29L1.27539 17.385C3.25539 21.31 7.3104 24.0001 12.0004 24.0001Z" fill="#34A853"></path>
                                </svg> Google
                            </button>
                            <button type="button" class="btn" style="background:#f5f5f5;border-radius:0.5rem;min-width:120px;display:flex;align-items:center;justify-content:center;font-weight:500;color:#222;border:1px solid #eee;gap:8px;">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="affx afhm" width="20" height="20">
                                    <path d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z" clip-rule="evenodd" fill-rule="evenodd"></path>
                                </svg> GitHub
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <div class="text-center text-muted mt-5">
                Not a member? <a href="{{ route('home') }}" tabindex="-1">Start a free trial</a>
            </div>
        </div>
    </div>


    <script src="{{ asset('assets/js/tabler.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-3.6.4.min.js') }}"></script>
    <script src="{{ asset('assets/js/custom-toast.js') }}?v=1.2"></script>

    <script data-cfasync="false">
        function togglePassword(el) {
            const inputGroup = el.closest('.input-group') || el.parentElement;
            const passwordInput = inputGroup.querySelector('.password-input');
            const tooltipEl = el.querySelector('[data-bs-toggle="tooltip"]');

            if (!passwordInput) return;

            const isPassword = passwordInput.type === "password";

            // Toggle input type
            passwordInput.type = isPassword ? "text" : "password";

            // Update tooltip text
            const newTitle = isPassword ? "Hide password" : "Show password";
            tooltipEl.setAttribute("title", newTitle);
            tooltipEl.setAttribute("data-bs-original-title", newTitle);

            // Re-init Bootstrap tooltip (important)
            const tooltip = bootstrap.Tooltip.getInstance(tooltipEl);
            if (tooltip) {
                tooltip.dispose();
            }
            new bootstrap.Tooltip(tooltipEl);
        }

        $('.form-method').submit(function(e) {
            e.preventDefault();

            var btn = document.querySelector(".btn-primary").innerHTML;
            document.querySelector(".btn-primary").innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';

            var formData = $(this).serialize();

            $.ajax({
                type: 'POST'
                , url: 'login'
                , data: formData
                , dataType: 'json'
                , success: function(response) {
                    document.querySelector(".btn-primary").innerHTML = btn;

                    $('input[name="csrf_token"]').val(response.csrf_token);

                    if (response.status === 'true') {
                        location.href = response.target;
                    } else {
                        createToast({
                            title: response.title
                            , description: response.message
                            , svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`
                            , timeout: 6000
                        });
                    }
                }
                , error: function(xhr, status, error) {
                    createToast({
                        title: 'Something Wrong!'
                        , description: error
                        , svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`
                        , timeout: 6000
                    });
                }
            });
        });

    </script>
</body>
</html>
