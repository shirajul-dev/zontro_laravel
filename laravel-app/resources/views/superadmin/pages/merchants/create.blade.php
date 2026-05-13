@extends('superadmin.layouts.app')

@section('title', 'Create Merchant')

@section('content')

    <div class="kt-container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-medium leading-none text-mono">
                    Create Merchant Wizard
                </h1>
                <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                    Follow the steps to onboard a new merchant and their brand
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <a class="kt-btn kt-btn-outline" href="{{ route('superadmin.merchants.index') }}">
                    <i class="ki-filled ki-arrow-left"></i>
                    Back to Merchants
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-danger/10 border border-danger/20 text-danger p-4 rounded-lg mb-5">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-danger/10 border border-danger/20 text-danger p-4 rounded-lg mb-5 text-sm">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <div class="kt-container-fixed">
        <!-- begin: Stepper Nav -->
        <div class="flex items-center justify-center flex-wrap lg:flex-nowrap gap-8 lg:gap-1.5 pt-5 mb-12" id="merchant_stepper_nav">
            <!-- Step 1 Button -->
            <div class="stepper-item active text-2sm leading-none relative flex items-center gap-1.5 px-3 h-8.5 rounded-full border font-medium border-primary/10 bg-primary/10 text-primary [&_.kt-step-icon]:text-primary" data-step="1">
                <span class="kt-step-icon">
                    <i class="ki-filled ki-profile-circle text-base"></i>
                </span>
                Merchant Account
            </div>
            
            <div class="hidden lg:block w-12 h-px border-t border-dashed border-zinc-300 dark:border-zinc-600"></div>
            
            <!-- Step 2 Button -->
            <div class="stepper-item text-2sm leading-none relative flex items-center gap-1.5 px-3 h-8.5 rounded-full border border-border text-foreground [&_.kt-step-icon]:text-muted-foreground" data-step="2">
                <span class="kt-step-icon">
                    <i class="ki-filled ki-shop text-base"></i>
                </span>
                Brand Identity
            </div>
            
            <div class="hidden lg:block w-12 h-px border-t border-dashed border-zinc-300 dark:border-zinc-600"></div>
            
            <!-- Step 3 Button -->
            <div class="stepper-item text-2sm leading-none relative flex items-center gap-1.5 px-3 h-8.5 rounded-full border border-border text-foreground [&_.kt-step-icon]:text-muted-foreground" data-step="3">
                <span class="kt-step-icon">
                    <i class="ki-filled ki-check-circle text-base"></i>
                </span>
                Review & Confirm
            </div>
        </div>
        <!-- end: Stepper Nav -->

        <form action="{{ route('superadmin.merchants.store') }}" method="POST" enctype="multipart/form-data" id="merchant_create_form">
            @csrf
            
            <!-- Step 1: Merchant Account -->
            <div class="step-content" id="step_1_content">
                <div class="kt-card max-w-[800px] mx-auto">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Step 1: Merchant User Details</h3>
                    </div>
                    <div class="kt-card-content grid gap-5">
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Full Name <span class="text-danger">*</span></label>
                            <div class="grow">
                                <input class="kt-input w-full" name="full_name" value="{{ old('full_name') }}" placeholder="Enter merchant's full name" type="text" required>
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Username <span class="text-danger">*</span></label>
                            <div class="grow">
                                <input class="kt-input w-full" name="username" value="{{ old('username') }}" placeholder="Unique username" type="text" required>
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Email Address <span class="text-danger">*</span></label>
                            <div class="grow">
                                <input class="kt-input w-full" name="email" value="{{ old('email') }}" placeholder="email@example.com" type="email" required>
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Password <span class="text-danger">*</span></label>
                            <div class="grow">
                                <input class="kt-input w-full" name="password" placeholder="Enter secure password" type="password" required>
                            </div>
                        </div>
                        <div class="flex items-baseline flex-wrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Account Status</label>
                            <div class="grow">
                                <select class="kt-input w-full" name="status">
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspend" {{ old('status') == 'suspend' ? 'selected' : '' }}>Suspend</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="kt-card-footer justify-end">
                        <button type="button" class="kt-btn kt-btn-primary btn-next" data-next="2">
                            Next: Brand Identity
                            <i class="ki-filled ki-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Brand Identity -->
            <div class="step-content hidden" id="step_2_content">
                <div class="kt-card max-w-[800px] mx-auto">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Step 2: Brand & Shop Details</h3>
                    </div>
                    <div class="kt-card-content grid gap-5">
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Brand Name <span class="text-danger">*</span></label>
                            <div class="grow">
                                <input class="kt-input w-full" name="brand_name" value="{{ old('brand_name') }}" placeholder="Enter brand or shop name" type="text" required>
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Support Email <span class="text-danger">*</span></label>
                            <div class="grow">
                                <input class="kt-input w-full" name="support_email" value="{{ old('support_email') }}" placeholder="support@brand.com" type="email" required>
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Support Phone</label>
                            <div class="grow">
                                <input class="kt-input w-full" name="support_phone" value="{{ old('support_phone') }}" placeholder="+123456789" type="text">
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Website</label>
                            <div class="grow">
                                <input class="kt-input w-full" name="support_website" value="{{ old('support_website') }}" placeholder="https://brand.com" type="url">
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Country</label>
                            <div class="grow">
                                <input class="kt-input w-full" name="country" value="{{ old('country') }}" placeholder="United States" type="text">
                            </div>
                        </div>
                        <div class="flex items-baseline flex-wrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Brand Logo</label>
                            <div class="grow">
                                <input class="kt-input w-full" name="logo" type="file" accept="image/*">
                                <div class="text-xs text-muted-foreground mt-1">Recommended size: 250x250px. Max 2MB.</div>
                            </div>
                        </div>
                    </div>
                    <div class="kt-card-footer justify-between">
                        <button type="button" class="kt-btn kt-btn-outline btn-prev" data-prev="1">
                            <i class="ki-filled ki-arrow-left"></i>
                            Back
                        </button>
                        <button type="button" class="kt-btn kt-btn-primary btn-next" data-next="3">
                            Next: Review
                            <i class="ki-filled ki-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Review & Confirm -->
            <div class="step-content hidden" id="step_3_content">
                <div class="kt-card max-w-[800px] mx-auto">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Step 3: Review & Finalize</h3>
                    </div>
                    <div class="kt-card-content grid gap-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <span class="text-xs font-semibold text-muted-foreground uppercase">Merchant Account</span>
                                <div class="bg-muted/30 p-4 rounded-lg border border-border">
                                    <div class="text-sm font-medium" id="summary_full_name">---</div>
                                    <div class="text-xs text-muted-foreground" id="summary_email">---</div>
                                    <div class="text-xs text-muted-foreground" id="summary_username">---</div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <span class="text-xs font-semibold text-muted-foreground uppercase">Brand Identity</span>
                                <div class="bg-muted/30 p-4 rounded-lg border border-border">
                                    <div class="text-sm font-medium" id="summary_brand_name">---</div>
                                    <div class="text-xs text-muted-foreground" id="summary_support_email">---</div>
                                    <div class="text-xs text-muted-foreground" id="summary_website">---</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-900/30">
                            <div class="flex gap-2 items-start">
                                <i class="ki-filled ki-information text-yellow-600"></i>
                                <div class="text-xs text-yellow-800 dark:text-yellow-200/80">
                                    By clicking "Create Merchant", you will create a new administrative user and a linked brand entity. Credentials will be active immediately.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="kt-card-footer justify-between">
                        <button type="button" class="kt-btn kt-btn-outline btn-prev" data-prev="2">
                            <i class="ki-filled ki-arrow-left"></i>
                            Back
                        </button>
                        <button type="submit" class="kt-btn kt-btn-primary">
                            <i class="ki-filled ki-check-circle"></i>
                            Create Merchant & Brand
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <style>
        .stepper-item.active {
            @apply border-primary/10 bg-primary/10 text-primary;
        }
        .stepper-item.active .kt-step-icon {
            @apply text-primary;
        }
        .stepper-item.completed {
            @apply border-green-500/10 bg-green-500/10 text-green-600;
        }
        .stepper-item.completed .kt-step-icon {
            @apply text-green-600;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('merchant_create_form');
            const nextBtns = document.querySelectorAll('.btn-next');
            const prevBtns = document.querySelectorAll('.btn-prev');
            const contents = document.querySelectorAll('.step-content');
            const navItems = document.querySelectorAll('.stepper-item');

            function updateSummary() {
                const formData = new FormData(form);
                document.getElementById('summary_full_name').textContent = formData.get('full_name') || '---';
                document.getElementById('summary_email').textContent = formData.get('email') || '---';
                document.getElementById('summary_username').textContent = formData.get('username') || '---';
                document.getElementById('summary_brand_name').textContent = formData.get('brand_name') || '---';
                document.getElementById('summary_support_email').textContent = formData.get('support_email') || '---';
                document.getElementById('summary_website').textContent = formData.get('support_website') || '---';
            }

            function goToStep(step) {
                contents.forEach(content => content.classList.add('hidden'));
                document.getElementById(`step_${step}_content`).classList.remove('hidden');

                navItems.forEach(item => {
                    const itemStep = parseInt(item.dataset.step);
                    item.classList.remove('active', 'border-primary/10', 'bg-primary/10', 'text-primary');
                    item.querySelector('.kt-step-icon').classList.remove('text-primary');
                    item.classList.add('border-border', 'text-foreground');
                    item.querySelector('.kt-step-icon').classList.add('text-muted-foreground');

                    if (itemStep === step) {
                        item.classList.add('active', 'border-primary/10', 'bg-primary/10', 'text-primary');
                        item.classList.remove('border-border', 'text-foreground');
                        item.querySelector('.kt-step-icon').classList.add('text-primary');
                        item.querySelector('.kt-step-icon').classList.remove('text-muted-foreground');
                    } else if (itemStep < step) {
                        item.classList.add('completed');
                    }
                });

                if (step === 3) {
                    updateSummary();
                }
            }

            nextBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const nextStep = parseInt(btn.dataset.next);
                    // Simple validation
                    const currentStep = nextStep - 1;
                    const inputs = document.getElementById(`step_${currentStep}_content`).querySelectorAll('input[required]');
                    let valid = true;
                    inputs.forEach(input => {
                        if (!input.value) {
                            input.classList.add('border-danger');
                            valid = false;
                        } else {
                            input.classList.remove('border-danger');
                        }
                    });

                    if (valid) {
                        goToStep(nextStep);
                    }
                });
            });

            prevBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const prevStep = parseInt(btn.dataset.prev);
                    goToStep(prevStep);
                });
            });
        });
    </script>

@endsection
