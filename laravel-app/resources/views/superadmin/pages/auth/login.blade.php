@extends('superadmin.layouts.auth')

@section('title', 'SuperAdmin - Sign In')

@section('content')
<div class="flex flex-col items-center justify-center grow bg-center bg-no-repeat page-bg">
    <div class="m-5"><a href="/metronic/tailwind/react/demo1/" data-discover="true"><img class="h-[35px] max-w-none" alt="" src="{{ asset('assets/images/logo-light.png') }}"></a></div>
    <div data-slot="card" class="flex flex-col items-stretch text-card-foreground rounded-xl bg-card border border-border shadow-xs black/5 w-full max-w-[400px]">
        <div data-slot="card-content" class="grow p-6">
            <form id="loginForm" class="block w-full space-y-5" method="POST" action="{{ route('superadmin.login.submit') }}">
                @csrf
                <div class="text-center space-y-1 pb-3">
                    <h1 class="text-2xl font-semibold tracking-tight">SuperAdmin</h1>
                    <p class="text-sm text-muted-foreground">Welcome back! Log in with your credentials.</p>
                </div>
                {{-- <div data-slot="alert" role="alert" class="flex items-stretch w-full group-[.toaster]:w-(--width) rounded-md px-3 py-2.5 gap-2 text-xs [&amp;&gt;[data-slot=alert-icon]&gt;svg]:size-4 *:data-alert-icon:mt-0.5 [&amp;_[data-slot=alert-close]]:mt-0.25 [&amp;_[data-slot=alert-close]_svg]:size-3.5 bg-muted border border-border text-foreground">
                    <div data-slot="alert-icon" class="shrink-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-alert text-primary" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" x2="12" y1="8" y2="12"></line>
                            <line x1="12" x2="12.01" y1="16" y2="16"></line>
                        </svg></div>
                    <div data-slot="alert-title" class="grow tracking-tight text-accent-foreground">Use <strong>demo@kt.com</strong> username and <strong>demo123</strong> password for demo access.</div>
                </div> --}}
                {{-- <div class="flex flex-col gap-3.5"><button data-slot="button" class="cursor-pointer group focus-visible:outline-hidden inline-flex items-center justify-center has-data-[arrow=true]:justify-between whitespace-nowrap font-medium ring-offset-background transition-[color,box-shadow] disabled:pointer-events-none disabled:opacity-60 [&amp;_svg]:shrink-0 bg-background text-accent-foreground border border-input hover:bg-accent data-[state=open]:bg-accent h-8.5 rounded-md px-3 gap-1.5 text-[0.8125rem] leading-(--text-sm--line-height) [&amp;_svg:not([class*=size-])]:size-4 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 [&amp;_svg:not([role=img]):not([class*=text-]):not([class*=opacity-])]:opacity-60 shadow-xs shadow-black/5" type="button"><svg viewBox="0 0 32 32" fill="none" role="img" class="size-5!">
                            <path d="M16.2449 13.8184V18.4657H22.8349C22.5455 19.9602 21.6771 21.2257 20.3747 22.0766L24.3487 25.0985C26.6642 23.004 28 19.9276 28 16.273C28 15.4221 27.9221 14.6039 27.7773 13.8185L16.2449 13.8184Z" fill="#4285F4"></path>
                            <path d="M5.3137 10.6221C4.47886 12.2366 4.00024 14.0584 4.00024 16.0002C4.00024 17.942 4.47886 19.7639 5.3137 21.3784C5.3137 21.3892 9.388 18.2802 9.388 18.2802C9.14311 17.5602 8.99835 16.7966 8.99835 16.0001C8.99835 15.2036 9.14311 14.44 9.388 13.72L5.3137 10.6221Z" fill="#FBBC05"></path>
                            <path d="M16.2448 8.77821C18.0482 8.77821 19.6511 9.3891 20.9313 10.5673L24.4378 7.13097C22.3116 5.18917 19.551 4 16.2448 4C11.4582 4 7.32833 6.69456 5.31348 10.6219L9.38766 13.7201C10.3561 10.8837 13.0611 8.77821 16.2448 8.77821Z" fill="#EA4335"></path>
                            <path d="M9.38238 18.2842L8.48609 18.9566L5.31348 21.3784C7.32833 25.2947 11.4579 28.0002 16.2445 28.0002C19.5506 28.0002 22.3224 26.9311 24.3484 25.0984L20.3744 22.0766C19.2835 22.7966 17.892 23.233 16.2445 23.233C13.0609 23.233 10.3559 21.1275 9.38739 18.2911L9.38238 18.2842Z" fill="#34A853"></path>
                        </svg> Sign in with Google</button></div> --}}
                {{-- <div class="relative py-1.5">
                    <div class="absolute inset-0 flex items-center"><span class="w-full border-t"></span></div>
                    <div class="relative flex justify-center text-xs uppercase"><span class="bg-background px-2 text-muted-foreground">or</span></div>
                </div> --}}
                <div data-slot="form-item" class="flex flex-col gap-2.5" data-invalid="false"><label data-slot="form-label" class="text-sm leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-50 font-medium text-foreground" for="emailInput">Email</label><input data-slot="form-control" id="emailInput" class="flex w-full bg-background border border-input shadow-xs shadow-black/5 transition-[color,box-shadow] text-foreground placeholder:text-muted-foreground/80 focus-visible:ring-ring/30 focus-visible:border-ring focus-visible:outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-60 [&amp;[readonly]]:bg-muted/80 [&amp;[readonly]]:cursor-not-allowed file:h-full [&amp;[type=file]]:py-0 file:border-solid file:border-input file:bg-transparent file:font-medium file:not-italic file:text-foreground file:p-0 file:border-0 file:border-e aria-invalid:border-destructive/60 aria-invalid:ring-destructive/10 dark:aria-invalid:border-destructive dark:aria-invalid:ring-destructive/20 h-8.5 px-3 text-[0.8125rem] leading-(--text-sm--line-height) rounded-md file:pe-3 file:me-3" placeholder="Your email" aria-describedby="_r_0_-form-item-description" aria-invalid="false" value="root@zontropay.com" name="email" required></div>
                <div data-slot="form-item" class="flex flex-col gap-2.5" data-invalid="false">
                    <div class="flex justify-between items-center gap-2.5"><label data-slot="form-label" class="text-sm leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-50 font-medium text-foreground" for="_r_1_-form-item">Password</label></div>
                    <div class="relative"><input data-slot="input" id="passwordInput" class="flex w-full bg-background border border-input shadow-xs shadow-black/5 transition-[color,box-shadow] text-foreground placeholder:text-muted-foreground/80 focus-visible:ring-ring/30 focus-visible:border-ring focus-visible:outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-60 [&amp;[readonly]]:bg-muted/80 [&amp;[readonly]]:cursor-not-allowed file:h-full [&amp;[type=file]]:py-0 file:border-solid file:border-input file:bg-transparent file:font-medium file:not-italic file:text-foreground file:p-0 file:border-0 file:border-e aria-invalid:border-destructive/60 aria-invalid:ring-destructive/10 dark:aria-invalid:border-destructive dark:aria-invalid:ring-destructive/20 h-8.5 px-3 text-[0.8125rem] leading-(--text-sm--line-height) rounded-md file:pe-3 file:me-3" placeholder="Your password" type="password" value="12345678" name="password" required><button id="togglePassword" data-slot="button" class="cursor-pointer group focus-visible:outline-hidden inline-flex items-center justify-center has-data-[arrow=true]:justify-between whitespace-nowrap font-medium ring-offset-background transition-[color,box-shadow] disabled:pointer-events-none disabled:opacity-60 [&amp;_svg]:shrink-0 hover:text-accent-foreground data-[state=open]:bg-accent data-[state=open]:text-accent-foreground rounded-md gap-1.5 text-[0.8125rem] leading-(--text-sm--line-height) focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 shrink-0 text-muted-foreground w-8.5 p-0 [&amp;_svg:not([class*=size-])]:size-4 absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye text-muted-foreground" aria-hidden="true">
                                <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg></button></div>
                </div>
                <div data-slot="form-item" class="gap-2.5 flex flex-col space-y-2" data-invalid="false">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2"><button type="button" role="checkbox" aria-checked="true" data-state="checked" value="on" data-slot="form-control" class="group peer bg-background shrink-0 rounded-md border border-input ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive/60 aria-invalid:ring-destructive/10 dark:aria-invalid:border-destructive dark:aria-invalid:ring-destructive/20 [[data-invalid=true]_&amp;]:border-destructive/60 [[data-invalid=true]_&amp;]:ring-destructive/10 dark:[[data-invalid=true]_&amp;]:border-destructive dark:[[data-invalid=true]_&amp;]:ring-destructive/20, data-[state=checked]:bg-primary data-[state=checked]:border-primary data-[state=checked]:text-primary-foreground data-[state=indeterminate]:bg-primary data-[state=indeterminate]:border-primary data-[state=indeterminate]:text-primary-foreground size-5 [&amp;_svg]:size-3.5" id="_r_2_-form-item" aria-describedby="_r_2_-form-item-description" aria-invalid="false"><span data-state="checked" class="flex items-center justify-center text-current" style="pointer-events: none;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check group-data-[state=indeterminate]:hidden" aria-hidden="true">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minus hidden group-data-[state=indeterminate]:block" aria-hidden="true">
                                        <path d="M5 12h14"></path>
                                    </svg></span></button><input aria-hidden="true" tabindex="-1" type="checkbox" value="on" checked="" style="position: absolute; pointer-events: none; opacity: 0; margin: 0px; transform: translateX(-100%); width: 20px; height: 20px;"><label data-slot="form-label" class="peer-disabled:cursor-not-allowed peer-disabled:opacity-50 text-foreground text-sm font-normal cursor-pointer" for="_r_2_-form-item">Remember me</label></div>
                                    {{-- <a class="text-sm font-semibold text-foreground hover:text-primary" href="/metronic/tailwind/react/demo1/auth/reset-password" data-discover="true">Forgot Password?</a> --}}
                    </div>
                <button id="submitBtn" data-slot="button" class="cursor-pointer group focus-visible:outline-hidden inline-flex items-center justify-center has-data-[arrow=true]:justify-between whitespace-nowrap font-medium ring-offset-background transition-[color,box-shadow] disabled:pointer-events-none disabled:opacity-60 [&amp;_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 data-[state=open]:bg-primary/90 h-8.5 rounded-md px-3 gap-1.5 text-[0.8125rem] leading-(--text-sm--line-height) [&amp;_svg:not([class*=size-])]:size-4 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 shadow-xs shadow-black/5 w-full" type="submit">Sign In</button>
                {{-- <div class="text-center text-sm text-muted-foreground">Don't have an account? <a class="text-sm font-semibold text-foreground hover:text-primary" href="/metronic/tailwind/react/demo1/auth/signup" data-discover="true">Sign Up</a></div> --}}
            </form>
        </div>
    </div>
</div>

<script>

    // Password toggle functionality
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');

    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
        });
    }

    // Form submission with Notyf notifications
    function initializeForm() {
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');

        if (loginForm && window.notyf) {
            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Disable button and show loading state
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="inline-flex items-center gap-2"><span class="animate-spin inline-block size-3.5 border-2 border-current border-t-transparent rounded-full"></span>Signing in...</span>';

                try {
                    const formData = new FormData(loginForm);
                    const response = await fetch(loginForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok && data.status === true) {
                        // Success - show toast and redirect
                        window.notyf.success(data.message || 'Login successful');
                        setTimeout(() => {
                            window.location.href = data.redirect || '{{ route("superadmin.dashboard") }}';
                        }, 1500);
                    } else {
                        // Error response
                        window.notyf.error(data.message || 'Login failed');
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    window.notyf.error('An error occurred during login. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        }
    }

    // Start initialization once DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeForm);
    } else {
        initializeForm();
    }
</script>
@endsection
