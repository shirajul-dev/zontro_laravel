@extends('superadmin.layouts.app')

@section('title', 'Edit Merchant')

@section('content')

    <div class="kt-container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-medium leading-none text-mono">
                    Edit Merchant: {{ $merchant->brand_name }}
                </h1>
                <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                    Update account settings and brand identity for this merchant
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <a class="kt-btn kt-btn-outline" href="{{ route('superadmin.merchants.show', $merchant->a_id) }}">
                    <i class="ki-filled ki-eye"></i>
                    View Profile
                </a>
                <a class="kt-btn kt-btn-outline" href="{{ route('superadmin.merchants.index') }}">
                    <i class="ki-filled ki-arrow-left"></i>
                    Back
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

        @if (session('success'))
            <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-5 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <div class="kt-container-fixed">
        <form action="{{ route('superadmin.merchants.update', $merchant->a_id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
                <!-- Merchant Account Settings -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Merchant Account</h3>
                    </div>
                    <div class="kt-card-content grid gap-5">
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Full Name <span class="text-danger">*</span></label>
                            <div class="grow">
                                <input class="kt-input w-full" name="full_name" value="{{ old('full_name', $merchant->full_name) }}" type="text" required>
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Username <span class="text-danger">*</span></label>
                            <div class="grow">
                                <input class="kt-input w-full" name="username" value="{{ old('username', $merchant->username) }}" type="text" required>
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Email Address <span class="text-danger">*</span></label>
                            <div class="grow">
                                <input class="kt-input w-full" name="email" value="{{ old('email', $merchant->email) }}" type="email" required>
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">New Password</label>
                            <div class="grow">
                                <input class="kt-input w-full" name="password" placeholder="Leave blank to keep current" type="password">
                                <div class="text-xs text-muted-foreground mt-1">Only fill this if you want to change the password.</div>
                            </div>
                        </div>
                        <div class="flex items-baseline flex-wrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Account Status</label>
                            <div class="grow">
                                <select class="kt-input w-full" name="status">
                                    <option value="active" {{ old('status', $merchant->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspend" {{ old('status', $merchant->status) == 'suspend' ? 'selected' : '' }}>Suspend</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Brand Identity Settings -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Brand Identity</h3>
                    </div>
                    <div class="kt-card-content grid gap-5">
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Brand Name <span class="text-danger">*</span></label>
                            <div class="grow">
                                <input class="kt-input w-full" name="brand_name" value="{{ old('brand_name', $merchant->brand_name) }}" type="text" required>
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Support Email <span class="text-danger">*</span></label>
                            <div class="grow">
                                <input class="kt-input w-full" name="support_email" value="{{ old('support_email', $merchant->support_email) }}" type="email" required>
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Support Phone</label>
                            <div class="grow">
                                <input class="kt-input w-full" name="support_phone" value="{{ old('support_phone', $merchant->support_phone) }}" type="text">
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Website</label>
                            <div class="grow">
                                <input class="kt-input w-full" name="support_website" value="{{ old('support_website', $merchant->support_website) }}" type="url">
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Country</label>
                            <div class="grow">
                                <input class="kt-input w-full" name="country" value="{{ old('country', $merchant->brand_country) }}" type="text">
                            </div>
                        </div>
                        <div class="flex items-baseline flex-wrap gap-2.5">
                            <label class="kt-form-label max-w-48 w-full">Brand Logo</label>
                            <div class="grow">
                                <div class="flex items-center gap-5 mb-2">
                                    @if($merchant->brand_logo)
                                        <div class="size-16 rounded-lg border border-border overflow-hidden bg-muted">
                                            <img src="{{ asset($merchant->brand_logo) }}" class="size-full object-contain" alt="Current Logo">
                                        </div>
                                    @endif
                                    <input class="kt-input w-full" name="logo" type="file" accept="image/*">
                                </div>
                                <div class="text-xs text-muted-foreground mt-1">Upload new logo to replace current one. Recommended: 250x250px.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="lg:col-span-2 flex justify-end gap-2.5 mt-5">
                    <a href="{{ route('superadmin.merchants.index') }}" class="kt-btn kt-btn-outline">Cancel</a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check-circle"></i>
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>

@endsection
