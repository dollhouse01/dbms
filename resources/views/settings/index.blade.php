@extends('layouts.app')
@section('page-title')
    {{ __('System Settings') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('System Settings') }}</li>
@endsection
@php
    if (\Auth::user()->type == 'super admin') {
        $main_logo = getSettingsValByName('logo');
        $main_favicon = getSettingsValByName('favicon');
        $main_light_logo = getSettingsValByName('light_logo');
    } else {
        $main_logo = getSettingsValByName('company_logo');
        $main_favicon = getSettingsValByName('company_favicon');
        $main_light_logo = getSettingsValByName('company_light_logo');
    }
    $activeTab = session('tab', 'user_profile_settings');
    $profile = asset(Storage::url('upload/profile/avatar.png'));
    $default_logo = asset(Storage::url('upload/logo/logo.png'));
    $default_favicon = asset(Storage::url('upload/logo/favicon.png'));
    $default_light_logo = asset(Storage::url('upload/logo/light_logo.png'));
    $subscriptionData = currentSubscription();

@endphp
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <ul class="nav flex-column nav-tabs account-tabs mb-3" id="myTab" role="tablist">
                                @if (Gate::check('manage account settings'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ empty($activeTab) || $activeTab == 'user_profile_settings' ? ' active ' : '' }}"
                                            id="profile-tab-1" data-bs-toggle="tab" href="#user_profile_settings"
                                            role="tab" aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ti ti-user-check me-2 f-20"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="mb-0">{{ __('User Profile') }}</h5>
                                                    <small
                                                        class="text-muted">{{ __('User Account Profile Settings') }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if (Gate::check('manage password settings'))
                                    <li class="nav-item">
                                        <a class="nav-link  {{ empty($activeTab) || $activeTab == 'password_settings' ? ' active ' : '' }}"
                                            id="profile-tab-2" data-bs-toggle="tab" href="#password_settings" role="tab"
                                            aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ti ti-key me-2 f-20"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="mb-0">{{ __('Password') }}</h5>
                                                    <small class="text-muted">{{ __('Password Settings') }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if (Gate::check('manage general settings'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ empty($activeTab) || $activeTab == 'general_settings' ? ' active ' : '' }}"
                                            id="profile-tab-3" data-bs-toggle="tab" href="#general_settings" role="tab"
                                            aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ti ti-settings me-2 f-20"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="mb-0">{{ __('General') }}</h5>
                                                    <small class="text-muted">{{ __('General Settings') }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if (Gate::check('manage company settings'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ empty($activeTab) || $activeTab == 'company_settings' ? ' active ' : '' }}"
                                            id="profile-tab-4" data-bs-toggle="tab" href="#company_settings" role="tab"
                                            aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ti ti-building me-2 f-20"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="mb-0">{{ __('Company') }}</h5>
                                                    <small class="text-muted">{{ __('Company Settings') }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if (Gate::check('manage email settings'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ empty($activeTab) || $activeTab == 'email_SMTP_settings' ? ' active ' : '' }} "
                                            id="profile-tab-5" data-bs-toggle="tab" href="#email_SMTP_settings"
                                            role="tab" aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ti ti-mail me-2 f-20"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="mb-0">{{ __('Email') }}</h5>
                                                    <small class="text-muted">{{ __('Email SMTP Settings') }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if (Gate::check('manage payment settings'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ empty($activeTab) || $activeTab == 'payment_settings' ? ' active ' : '' }}"
                                            id="profile-tab-6" data-bs-toggle="tab" href="#payment_settings" role="tab"
                                            aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ti ti-credit-card me-2 f-20"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="mb-0">{{ __('Payment') }}</h5>
                                                    <small class="text-muted">{{ __('Payment Settings') }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if (Gate::check('manage seo settings'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ empty($activeTab) || $activeTab == 'site_SEO_settings' ? ' active ' : '' }} "
                                            id="profile-tab-7" data-bs-toggle="tab" href="#site_SEO_settings" role="tab"
                                            aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ti ti-sitemap me-2 f-20"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="mb-0">{{ __('Site SEO') }}</h5>
                                                    <small class="text-muted">{{ __('Site SEO Settings') }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if (Gate::check('manage google recaptcha settings'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ empty($activeTab) || $activeTab == 'google_recaptcha_settings' ? ' active ' : '' }} "
                                            id="profile-tab-8" data-bs-toggle="tab" href="#google_recaptcha_settings"
                                            role="tab" aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ti ti-brand-google me-2 f-20"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="mb-0">{{ __('Google Recaptcha') }}</h5>
                                                    <small
                                                        class="text-muted">{{ __('Google Recaptcha Settings') }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if (Gate::check('manage 2FA settings'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ empty($activeTab) || $activeTab == '2FA' ? ' active ' : '' }} "
                                            id="profile-tab-9" data-bs-toggle="tab" href="#2FA" role="tab"
                                            aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ti ti-barcode me-2 f-20"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="mb-0">{{ __('2 Factors Authentication') }}</h5>
                                                    <small
                                                        class="text-muted">{{ __('2 Factors Authentication Settings') }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if (Gate::check('manage storage settings'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ empty($activeTab) || $activeTab == 'storage' ? ' active ' : '' }} "
                                            id="profile-tab-9" data-bs-toggle="tab" href="#storage" role="tab"
                                            aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ti ti-disc me-2 f-20"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="mb-0">{{ __('Cloud Storage') }}</h5>
                                                    <small
                                                        class="text-muted">{{ __('Cloud Storage Integration Settings') }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if (Gate::check('manage twilio settings'))
                                    <li class="nav-item">
                                        <a class="nav-link {{ empty($activeTab) || $activeTab == 'twilio' ? ' active ' : '' }} "
                                            id="profile-tab-9" data-bs-toggle="tab" href="#twilio" role="tab"
                                            aria-selected="true">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="ti ti-message me-2 f-20"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="mb-0">{{ __('Twilio') }}</h5>
                                                    <small class="text-muted">{{ __('Twilio Settings') }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->type === 'super admin' ||
                                        ($subscriptionData['pricing_feature_settings'] === 'off' ||
                                            $subscriptionData['subscription']->enabled_openai == 1))
                                    @if (Gate::check('manage openai settings'))
                                        <li class="nav-item">
                                            <a class="nav-link {{ empty($activeTab) || $activeTab == 'openai' ? ' active ' : '' }} "
                                                id="profile-tab-9" data-bs-toggle="tab" href="#openai" role="tab"
                                                aria-selected="true">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <i class="ti ti-message-dots me-2 f-20"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-2">
                                                        <h5 class="mb-0">{{ __('Openai Settings') }}</h5>
                                                        <small class="text-muted">{{ __('openai Settings') }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    @endif
                                @endif
                            </ul>
                        </div>
                        <div class="col-lg-8">
                            <div class="tab-content">
                                @if (Gate::check('manage account settings'))
                                    <div class="tab-pane {{ empty($activeTab) || $activeTab == 'user_profile_settings' ? ' active show ' : '' }}"
                                        id="user_profile_settings" role="tabpanel"
                                        aria-labelledby="user_profile_settings">
                                        {{ Form::model($loginUser, ['route' => ['setting.account'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-shrink-0">
                                                <img src="{{ !empty($loginUser->profile) ? fetch_file($loginUser->profile, 'upload/profile/') : $profile }}"
                                                    alt="user-image" class="img-fluid rounded-circle wid-80" />
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter your name'), 'required' => 'required']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('email', __('Email Address'), ['class' => 'form-label']) }}
                                                    {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Enter your email'), 'required' => 'required']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('phone_number', __('Phone Number'), ['class' => 'form-label']) }}
                                                    {{ Form::text('phone_number', null, ['class' => 'form-control', 'placeholder' => __('Enter your Phone Number')]) }}
                                                    <small class="form-text text-muted">
                                                        {{ __('Please enter the number with country code. e.g., +91XXXXXXXXXX') }}
                                                    </small>

                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('profile', __('Profile'), ['class' => 'form-label']) }}
                                                    {{ Form::file('profile', ['class' => 'form-control']) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-6"></div>
                                            <div class="col-6 text-end">
                                                {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                            </div>
                                        </div>
                                        {{ Form::close() }}
                                    </div>
                                @endif
                                @if (Gate::check('manage password settings'))
                                    <div class="tab-pane {{ !empty($activeTab) && $activeTab == 'password_settings' ? ' active show ' : '' }}"
                                        id="password_settings" role="tabpanel" aria-labelledby="password_settings">
                                        {{ Form::model($loginUser, ['route' => ['setting.password'], 'method' => 'post']) }}
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    {{ Form::label('current_password', __('Current Password'), ['class' => 'form-label']) }}
                                                    {{ Form::password('current_password', ['class' => 'form-control', 'placeholder' => __('Enter your current password'), 'required' => 'required']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    {{ Form::label('new_password', __('New Password'), ['class' => 'form-label']) }}
                                                    {{ Form::password('new_password', ['class' => 'form-control', 'placeholder' => __('Enter your new password'), 'required' => 'required']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    {{ Form::label('confirm_password', __('Confirm New Password'), ['class' => 'form-label']) }}
                                                    {{ Form::password('confirm_password', ['class' => 'form-control', 'placeholder' => __('Enter your confirm new password'), 'required' => 'required']) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-6"></div>
                                            <div class="col-6 text-end">
                                                {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                            </div>
                                        </div>
                                        {{ Form::close() }}
                                    </div>
                                @endif
                                @if (Gate::check('manage general settings'))
                                    <div class="tab-pane {{ !empty($activeTab) && $activeTab == 'general_settings' ? ' active show ' : '' }}"
                                        id="general_settings" role="tabpanel" aria-labelledby="general_settings">
                                        {{ Form::model($settings, ['route' => ['setting.general'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('application_name', __('Application Name'), ['class' => 'form-label']) }}
                                                    {{ Form::text('application_name', !empty($settings['app_name']) ? $settings['app_name'] : env('APP_NAME'), ['class' => 'form-control', 'placeholder' => __('Enter your application name'), 'required' => 'required']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {{ Form::label('copyright', __('Copyright'), ['class' => 'form-label']) }}
                                                    {{ Form::text('copyright', $settings['copyright'], ['class' => 'form-control', 'placeholder' => __('Enter copyright ')]) }}
                                                </div>
                                            </div>


                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('logo', __('Logo'), ['class' => 'form-label']) }}
                                                    <a href="{{ !empty($main_logo) ? fetch_file($main_logo, 'upload/logo/') : $default_logo }}"
                                                        target="_blank"><i class="ti ti-eye ms-2 f-15"></i></a>
                                                    {{ Form::file('logo', ['class' => 'form-control', 'accept' => 'image/png']) }}
                                                </div>

                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('favicon', __('Favicon'), ['class' => 'form-label']) }}
                                                    <a href="{{ !empty($main_favicon) ? fetch_file($main_favicon, 'upload/logo/') : $default_favicon }}"
                                                        target="_blank"><i class="ti ti-eye ms-2 f-15"></i></a>
                                                    {{ Form::file('favicon', ['class' => 'form-control', 'accept' => 'image/png']) }}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('light_logo', __('Light Logo'), ['class' => 'form-label']) }}
                                                    <a href="{{ !empty($main_light_logo) ? fetch_file($main_light_logo, 'upload/logo/') : $default_light_logo }}"
                                                        target="_blank"><i class="ti ti-eye ms-2 f-15"></i></a>
                                                    {{ Form::file('light_logo', ['class' => 'form-control', 'accept' => 'image/png']) }}
                                                </div>
                                            </div>
                                            @if (\Auth::user()->type == 'super admin')
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        {{ Form::label('landing_logo', __('Landing Page Logo'), ['class' => 'form-label']) }}
                                                        <a href="{{ !empty($settings['landing_logo']) ? fetch_file($settings['landing_logo'], 'upload/logo/') : $default_logo }}"
                                                            target="_blank"><i class="ti ti-eye ms-2 f-15"></i></a>
                                                        {{ Form::file('landing_logo', ['class' => 'form-control', 'accept' => 'image/png']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        {{ Form::label('landing_logo', __('Owner Email Verification'), ['class' => 'form-label']) }}
                                                        <div class="flex-shrink-0">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="owner_email_verification"
                                                                    name="owner_email_verification"
                                                                    {{ $settings['owner_email_verification'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        {{ Form::label('landing_logo', __('Registration Page'), ['class' => 'form-label']) }}
                                                        <div class="flex-shrink-0">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="register_page" name="register_page"
                                                                    {{ $settings['register_page'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        {{ Form::label('landing_logo', __('Landing Page'), ['class' => 'form-label']) }}
                                                        <div class="flex-shrink-0">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="landing_page" name="landing_page"
                                                                    {{ $settings['landing_page'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        {{ Form::label('pricing_feature', __('Pricing Feature'), ['class' => 'form-label']) }}
                                                        <div class="flex-shrink-0">
                                                            <div class="form-check form-switch">
                                                                <input type="hidden" name="pricing_feature"
                                                                    value="off">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="pricing_feature" name="pricing_feature"
                                                                    value="on"
                                                                    {{ $settings['pricing_feature'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-6"></div>
                                            <div class="col-6 text-end">
                                                {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                            </div>
                                        </div>
                                        {{ Form::close() }}
                                    </div>
                                @endif
                                @if (Gate::check('manage company settings'))
                                    <div class="tab-pane {{ !empty($activeTab) && $activeTab == 'company_settings' ? ' active show ' : '' }}"
                                        id="company_settings" role="tabpanel" aria-labelledby="company_settings">
                                        {{ Form::model($settings, ['route' => ['setting.company'], 'method' => 'post']) }}
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                {{ Form::label('company_name', __('Name'), ['class' => 'form-label']) }}
                                                {{ Form::text('company_name', $settings['company_name'], ['class' => 'form-control', 'placeholder' => __('Enter company name')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('company_email', __('Email'), ['class' => 'form-label']) }}
                                                {{ Form::text('company_email', $settings['company_email'], ['class' => 'form-control', 'placeholder' => __('Enter company email')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('company_phone', __('Phone Number'), ['class' => 'form-label']) }}
                                                {{ Form::text('company_phone', $settings['company_phone'], ['class' => 'form-control', 'placeholder' => __('Enter company phone')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('company_address', __('Address'), ['class' => 'form-label']) }}
                                                {{ Form::textarea('company_address', $settings['company_address'], ['class' => 'form-control', 'rows' => '2']) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('CURRENCY_SYMBOL', __('Currency Icon'), ['class' => 'form-label']) }}
                                                {{ Form::text('CURRENCY_SYMBOL', $settings['CURRENCY_SYMBOL'], ['class' => 'form-control', 'placeholder' => __('Enter currency symbol')]) }}
                                            </div>
                                            <div class="col-md-6">
                                                {{ Form::label('timezone', __('Timezone'), ['class' => 'form-label text-dark']) }}
                                                <select type="text" name="timezone" class="form-control select2"
                                                    id="timezone">
                                                    <option value="">{{ __('Select Timezone') }}</option>
                                                    @foreach ($timezones as $k => $timezone)
                                                        <option value="{{ $k }}"
                                                            {{ $settings['timezone'] == $k ? 'selected' : '' }}>
                                                            {{ $timezone }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('document_number_prefix', __('Document Number Prefix'), ['class' => 'form-label']) }}
                                                {{ Form::text('document_number_prefix', $settings['document_number_prefix'], ['class' => 'form-control', 'placeholder' => __('Enter invoice number prefix')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('reminder_number_prefix', __('Reminder Number Prefix'), ['class' => 'form-label']) }}
                                                {{ Form::text('reminder_number_prefix', $settings['reminder_number_prefix'], ['class' => 'form-control', 'placeholder' => __('Enter expense number prefix')]) }}
                                            </div>


                                            <div class="form-group col-md-3">
                                                {{ Form::label('company_zipcode', __('System Date Format'), ['class' => 'form-label']) }}
                                                <div class="">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="company_date_format1"
                                                            name="company_date_format" class="custom-control-input"
                                                            value="M j, Y"
                                                            {{ $settings['company_date_format'] == 'M j, Y' ? 'checked' : '' }}>
                                                        <label class="custom-control-label"
                                                            for="company_date_format1">{{ date('M d,Y') }}</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="company_date_format2"
                                                            name="company_date_format" class="custom-control-input"
                                                            value="y-m-d"
                                                            {{ $settings['company_date_format'] == 'y-m-d' ? 'checked' : '' }}>
                                                        <label class="custom-control-label"
                                                            for="company_date_format2">{{ date('y-m-d') }}</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="company_date_format3"
                                                            name="company_date_format" class="custom-control-input"
                                                            value="d-m-y"
                                                            {{ $settings['company_date_format'] == 'd-m-y' ? 'checked' : '' }}>
                                                        <label class="custom-control-label"
                                                            for="company_date_format3">{{ date('d-m-y') }}</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="company_date_format4"
                                                            name="company_date_format" class="custom-control-input"
                                                            value="m-d-y"
                                                            {{ $settings['company_date_format'] == 'm-d-y' ? 'checked' : '' }}>
                                                        <label class="custom-control-label"
                                                            for="company_date_format4">{{ date('m-d-y') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-3">
                                                {{ Form::label('company_zipcode', __('System Time Format'), ['class' => 'form-label']) }}
                                                <div class="">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="company_time_format1"
                                                            name="company_time_format" class="custom-control-input"
                                                            value="H:i"
                                                            {{ $settings['company_time_format'] == 'H:i' ? 'checked' : '' }}>
                                                        <label class="custom-control-label"
                                                            for="company_time_format1">{{ date('H:i') }}</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="company_time_format2"
                                                            name="company_time_format" class="custom-control-input"
                                                            value="g:i A"
                                                            {{ $settings['company_time_format'] == 'g:i A' ? 'checked' : '' }}>
                                                        <label class="custom-control-label"
                                                            for="company_time_format2">{{ date('g:i A') }}</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="company_time_format3"
                                                            name="company_time_format" class="custom-control-input"
                                                            value="g:i a"
                                                            {{ $settings['company_time_format'] == 'g:i a' ? 'checked' : '' }}>
                                                        <label class="custom-control-label"
                                                            for="company_time_format3">{{ date('g:i a') }}</label>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-6"></div>
                                            <div class="col-6 text-end">
                                                {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                            </div>
                                        </div>
                                        {{ Form::close() }}
                                    </div>
                                @endif
                                @if (Gate::check('manage email settings'))
                                    <div class="tab-pane {{ !empty($activeTab) && $activeTab == 'email_SMTP_settings' ? ' active show ' : '' }}"
                                        id="email_SMTP_settings" role="tabpanel" aria-labelledby="email_SMTP_settings">
                                        {{ Form::model($settings, ['route' => ['setting.smtp'], 'method' => 'post']) }}
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                {{ Form::label('sender_name', __('Sender Name'), ['class' => 'form-label']) }}
                                                {{ Form::text('sender_name', $settings['FROM_NAME'], ['class' => 'form-control', 'placeholder' => __('Enter sender name')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('sender_email', __('Sender Email'), ['class' => 'form-label']) }}
                                                {{ Form::text('sender_email', $settings['FROM_EMAIL'], ['class' => 'form-control', 'placeholder' => __('Enter sender email')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('server_driver', __('SMTP Driver'), ['class' => 'form-label']) }}
                                                {{ Form::text('server_driver', $settings['SERVER_DRIVER'], ['class' => 'form-control', 'placeholder' => __('Enter smtp driver')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('server_host', __('SMTP Host'), ['class' => 'form-label']) }}
                                                {{ Form::text('server_host', $settings['SERVER_HOST'], ['class' => 'form-control ', 'placeholder' => __('Enter smtp host')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('server_username', __('SMTP Username'), ['class' => 'form-label']) }}
                                                {{ Form::text('server_username', $settings['SERVER_USERNAME'], ['class' => 'form-control', 'placeholder' => __('Enter smtp username')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('server_password', __('SMTP Password'), ['class' => 'form-label']) }}
                                                {{ Form::text('server_password', $settings['SERVER_PASSWORD'], ['class' => 'form-control', 'placeholder' => __('Enter smtp password')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('server_encryption', __('SMTP Encryption'), ['class' => 'form-label']) }}
                                                {{ Form::text('server_encryption', $settings['SERVER_ENCRYPTION'], ['class' => 'form-control', 'placeholder' => __('Enter smtp encryption')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('server_port', __('SMTP Port'), ['class' => 'form-label']) }}
                                                {{ Form::text('server_port', $settings['SERVER_PORT'], ['class' => 'form-control', 'placeholder' => __('Enter smtp port')]) }}
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-6"></div>
                                            <div class="col-6  text-end">
                                                <a href="#" data-size="md"
                                                    data-url="{{ route('setting.smtp.test') }}"
                                                    data-title="{{ __('Add Email') }}"
                                                    class='btn btn-primary btn-rounded customModal me-1'>
                                                    {{ __('Test Mail') }} </a>
                                                {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                            </div>
                                        </div>
                                        {{ Form::close() }}
                                    </div>
                                @endif
                                @if (Gate::check('manage payment settings'))
                                    <div class="tab-pane {{ !empty($activeTab) && $activeTab == 'payment_settings' ? ' active show ' : '' }}"
                                        id="payment_settings" role="tabpanel" aria-labelledby="payment_settings">

                                        {{ Form::model($settings, ['route' => ['setting.payment'], 'method' => 'post']) }}
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                {{ Form::label('CURRENCY_SYMBOL', __('Currency Icon'), ['class' => 'form-label']) }}
                                                {{ Form::text('CURRENCY_SYMBOL', $settings['CURRENCY_SYMBOL'], ['class' => 'form-control', 'placeholder' => __('Enter currency icon'), 'required']) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('CURRENCY', __('Currency Code'), ['class' => 'form-label']) }}
                                                {{ Form::text('CURRENCY', $settings['CURRENCY'], ['class' => 'form-control font-style', 'placeholder' => __('Enter currency code'), 'required']) }}
                                            </div>
                                        </div>
                                        <hr>

                                        {{-- ------------------------Stripe Payment settings------------------------------- --}}
                                        <div class="row mt-2">
                                            <div class="col-auto">
                                                {{ Form::label('stripe_payment', __('Stripe Payment'), ['class' => 'form-label']) }}
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="form-check custom-chek">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="stripe_payment" id="stripe_payment"
                                                            {{ $settings['STRIPE_PAYMENT'] == 'on' ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                {{ Form::label('stripe_key', __('Account Key'), ['class' => 'form-label']) }}
                                                {{ Form::text('stripe_key', $settings['STRIPE_KEY'], ['class' => 'form-control', 'placeholder' => __('Enter stripe key')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('stripe_secret', __('Account Secret Key'), ['class' => 'form-label']) }}
                                                {{ Form::text('stripe_secret', $settings['STRIPE_SECRET'], ['class' => 'form-control ', 'placeholder' => __('Enter stripe secret')]) }}
                                            </div>
                                        </div>
                                        <hr>
                                        {{-- ------------------------Paypal Payment settings------------------------------- --}}
                                        <div class="row mt-2">
                                            <div class="col-auto">
                                                {{ Form::label('paypal_payment', __('Paypal Payment'), ['class' => 'form-label']) }}
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="form-check custom-chek">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="paypal_payment" id="paypal_payment"
                                                            {{ $settings['paypal_payment'] == 'on' ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-12">
                                                {{ Form::label('paypal_mode', __('Account Mode'), ['class' => 'form-label me-2']) }}
                                                <div class="form-check custom-chek form-check-inline">
                                                    <input class="form-check-input" type="radio" value="sandbox"
                                                        id="sandbox" name="paypal_mode"
                                                        {{ $settings['paypal_mode'] == 'sandbox' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="sandbox">{{ __('Sandbox') }}
                                                    </label>
                                                </div>
                                                <div class="form-check custom-chek form-check-inline">
                                                    <input class="form-check-input" type="radio" value="live"
                                                        id="live" name="paypal_mode"
                                                        {{ $settings['paypal_mode'] == 'live' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="live">{{ __('Live') }}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('paypal_client_id', __('Account Client ID'), ['class' => 'form-label']) }}
                                                {{ Form::text('paypal_client_id', $settings['paypal_client_id'], ['class' => 'form-control', 'placeholder' => __('Enter client id')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('paypal_secret_key', __('Account Secret Key'), ['class' => 'form-label']) }}
                                                {{ Form::text('paypal_secret_key', $settings['paypal_secret_key'], ['class' => 'form-control ', 'placeholder' => __('Enter secret key')]) }}
                                            </div>
                                        </div>
                                        <hr>
                                        {{-- ------------------------Bank Transfer settings------------------------------- --}}
                                        <div class="row mt-2">
                                            <div class="col-auto">
                                                {{ Form::label('bank_transfer_payment', __('Bank Transfer Payment'), ['class' => 'form-label']) }}
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="form-check custom-chek">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="bank_transfer_payment" id="bank_transfer_payment"
                                                            {{ $settings['bank_transfer_payment'] == 'on' ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                {{ Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']) }}
                                                {{ Form::text('bank_name', $settings['bank_name'], ['class' => 'form-control', 'placeholder' => __('Enter bank name')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('bank_holder_name', __('Bank Holder Name'), ['class' => 'form-label']) }}
                                                {{ Form::text('bank_holder_name', $settings['bank_holder_name'], ['class' => 'form-control', 'placeholder' => __('Enter bank holder name')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('bank_account_number', __('Bank Account Number'), ['class' => 'form-label']) }}
                                                {{ Form::text('bank_account_number', $settings['bank_account_number'], ['class' => 'form-control', 'placeholder' => __('Enter bank account number')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('bank_ifsc_code', __('Bank IFSC'), ['class' => 'form-label']) }}
                                                {{ Form::text('bank_ifsc_code', $settings['bank_ifsc_code'], ['class' => 'form-control', 'placeholder' => __('Enter bank ifsc code')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('bank_other_details', __('Other Details'), ['class' => 'form-label']) }}
                                                {{ Form::textarea('bank_other_details', $settings['bank_other_details'], ['class' => 'form-control', 'rows' => 1, 'placeholder' => __('Enter bank other details')]) }}
                                            </div>
                                        </div>

                                        <hr>
                                        {{-- ------------------------Flutterwave settings------------------------------- --}}
                                        <div class="row mt-2">
                                            <div class="col-auto">
                                                {{ Form::label('flutterwave_payment', __('Flutterwave Payment'), ['class' => 'form-label']) }}
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="form-check custom-chek">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="flutterwave_payment" id="flutterwave_payment"
                                                            {{ $settings['flutterwave_payment'] == 'on' ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                {{ Form::label('flutterwave_public_key', __('Public Key'), ['class' => 'form-label']) }}
                                                {{ Form::text('flutterwave_public_key', $settings['flutterwave_public_key'], ['class' => 'form-control', 'placeholder' => __('Enter flutterwave public key')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('flutterwave_secret_key', __('Secret Key'), ['class' => 'form-label']) }}
                                                {{ Form::text('flutterwave_secret_key', $settings['flutterwave_secret_key'], ['class' => 'form-control', 'placeholder' => __('Enter flutterwave secret key')]) }}
                                            </div>

                                        </div>

                                        <hr>
                                        {{-- ------------------------Paystack settings------------------------------- --}}
                                        <div class="row mt-2">
                                            <div class="col-auto">
                                                {{ Form::label('paystack_payment', __('Paystack Payment'), ['class' => 'form-label']) }}
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="form-check custom-chek">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="paystack_payment" id="paystack_payment"
                                                            {{ $settings['paystack_payment'] == 'on' ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                {{ Form::label('paystack_public_key', __('Public Key'), ['class' => 'form-label']) }}
                                                {{ Form::text('paystack_public_key', $settings['paystack_public_key'], ['class' => 'form-control', 'placeholder' => __('Enter Paystack public key')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('paystack_secret_key', __('Secret Key'), ['class' => 'form-label']) }}
                                                {{ Form::text('paystack_secret_key', $settings['paystack_secret_key'], ['class' => 'form-control', 'placeholder' => __('Enter Paystack secret key')]) }}
                                            </div>

                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-6"></div>
                                            <div class="col-6 text-end">
                                                {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                            </div>
                                        </div>

                                        {{ Form::close() }}
                                    </div>
                                @endif
                                @if (Gate::check('manage seo settings'))
                                    <div class="tab-pane {{ !empty($activeTab) && $activeTab == 'site_SEO_settings' ? ' active show ' : '' }}"
                                        id="site_SEO_settings" role="tabpanel" aria-labelledby="site_SEO_settings">
                                        {{ Form::model($settings, ['route' => ['setting.site.seo'], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                                        <div class="row">

                                            <div class="col-xl-12 col-lg-12">
                                                <div class="card">
                                                    <div class="card-body">

                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    {{ Form::label('meta_seo_title', __('Meta Title'), ['class' => 'form-label']) }}
                                                                    {{ Form::text('meta_seo_title', $settings['meta_seo_title'], ['class' => 'form-control', 'placeholder' => __('Enter meta SEO title'), 'required' => 'required']) }}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    {{ Form::label('meta_seo_keyword', __('Meta Keyword'), ['class' => 'form-label']) }}
                                                                    {{ Form::text('meta_seo_keyword', $settings['meta_seo_keyword'], ['class' => 'form-control', 'placeholder' => __('Enter meta SEO keyword'), 'required' => 'required']) }}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    {{ Form::label('meta_seo_description', __('Meta Description'), ['class' => 'form-label']) }}
                                                                    {{ Form::textarea('meta_seo_description', $settings['meta_seo_description'], ['class' => 'form-control', 'placeholder' => __('Enter meta SEO description'), 'required' => 'required', 'rows' => '2']) }}
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    {{ Form::label('meta_seo_image', __('Meta Image'), ['class' => 'form-label']) }}
                                                                    {{ Form::file('meta_seo_image', ['class' => 'form-control']) }}
                                                                </div>
                                                            </div>
                                                            @if (!empty($settings['meta_seo_image']))
                                                                <div class="col-12 mt-20">
                                                                    <img src="{{ asset(Storage::url('upload/seo')) . '/' . $settings['meta_seo_image'] }}"
                                                                        class="setting-logo" alt="">
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="row mt-3">
                                                            <div class="col-6"></div>
                                                            <div class="col-6 text-end">
                                                                {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{ Form::close() }}
                                    </div>
                                @endif
                                @if (Gate::check('manage google recaptcha settings'))
                                    <div class="tab-pane {{ !empty($activeTab) && $activeTab == 'google_recaptcha_settings' ? ' active show ' : '' }}"
                                        id="google_recaptcha_settings" role="tabpanel"
                                        aria-labelledby="google_recaptcha_settings">

                                        {{ Form::model($settings, ['route' => ['setting.google.recaptcha'], 'method' => 'post']) }}
                                        <div class="row mt-2">
                                            <div class="col-auto">
                                                {{ Form::label('google_recaptcha', __('Google ReCaptch Enable'), ['class' => 'form-label']) }}
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="form-check custom-chek">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="google_recaptcha" id="google_recaptcha"
                                                            {{ $settings['google_recaptcha'] == 'on' ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                {{ Form::label('recaptcha_key', __('Recaptcha Key'), ['class' => 'form-label']) }}
                                                {{ Form::text('recaptcha_key', $settings['recaptcha_key'], ['class' => 'form-control', 'placeholder' => __('Enter recaptcha key')]) }}
                                            </div>
                                            <div class="form-group col-md-6">
                                                {{ Form::label('recaptcha_secret', __('Recaptcha Secret'), ['class' => 'form-label']) }}
                                                {{ Form::text('recaptcha_secret', $settings['recaptcha_secret'], ['class' => 'form-control ', 'placeholder' => __('Enter recaptcha secret')]) }}
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-6"></div>
                                            <div class="col-6 text-end">
                                                {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                            </div>
                                        </div>
                                        {{ Form::close() }}

                                    </div>
                                @endif
                                @if (Gate::check('manage 2FA settings'))
                                    <div class="tab-pane {{ !empty($activeTab) && $activeTab == '2FA' ? ' active show ' : '' }}"
                                        id="2FA" role="tabpanel" aria-labelledby="2FA">

                                        {{ Form::model($settings, ['route' => ['setting.twofa.enable'], 'method' => 'post']) }}
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                @if (empty(\Auth::user()->twofa_secret))
                                                    <label class="form-label">
                                                        {{ __('2-factors authentication is currently') }}
                                                        <span class='badge bg-warning'>{{ __('disabled') }}</span>.
                                                        {{ __('To enable') }}:
                                                    </label>
                                                @else
                                                    <h5>
                                                        {{ __('2-factors authentication is currently enable.') }}
                                                        <a href="{{ route('2fa.disable') }}" class="ms-2">
                                                            <span
                                                                class='btn btn-danger btn-rounded'>{{ __('click to disabled') }}</span>
                                                        </a>
                                                    </h5>
                                                @endif
                                            </div>
                                            @if (empty(\Auth::user()->twofa_secret))
                                                <div class="col-12">
                                                    <ol class="list-left-align mt-10">
                                                        <li>
                                                            {!! __('Open your OTP app and <b>scan the following QR-code') !!}</b>
                                                            <p class="text-center">
                                                                <img src="{!! QrCode2FA() !!}" alt="2FA">
                                                            </p>
                                                        </li>

                                                        <li>
                                                            {{ __('Generate a One Time Password (OTP) and enter the value below.') }}
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <input name="otp"
                                                                        class="form-control mr-1{{ $errors->has('otp') ? ' is-invalid' : '' }}"
                                                                        type="number" min="0" max="999999"
                                                                        step="1" required autocomplete="off">
                                                                    @if ($errors->has('otp'))
                                                                        <span class="invalid-feedback text-left">
                                                                            <strong>{{ $errors->first('otp') }}</strong>
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ol>
                                                </div>
                                            @endif
                                        </div>
                                        @if (empty(\Auth::user()->twofa_secret))
                                            <div class="row mt-3">
                                                <div class="col-12 text-end">
                                                    {{ Form::submit(__('Verify'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                                </div>
                                            </div>
                                        @endif
                                        {{ Form::close() }}

                                    </div>
                                @endif
                                @if (Gate::check('manage storage settings'))
                                    <div class="tab-pane {{ !empty($activeTab) && $activeTab == 'storage' ? ' active show ' : '' }}"
                                        id="storage" role="tabpanel" aria-labelledby="storage">
                                        {{ Form::model($settings, ['route' => ['storage.setting'], 'method' => 'post']) }}
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <div class="accordion" id="accordionExample">
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="headingOne">
                                                            <button
                                                                class="accordion-button {{ empty($settings['storage_type']) || $settings['storage_type'] == 'local' ? '' : 'collapsed' }}"
                                                                type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#collapseOne" aria-expanded="true"
                                                                aria-controls="collapseOne">
                                                                <div class="form-check form-switch onoff_storage">
                                                                    <input type="radio"
                                                                        class="form-check-input input-primary storage_switch"
                                                                        id="customswitchv1" name="storage_type"
                                                                        value="local"
                                                                        {{ empty($settings['storage_type']) || $settings['storage_type'] == 'local' ? 'checked' : '' }}>
                                                                </div>
                                                                <h4 class="mb-0 acc_title">
                                                                    <b>{{ __('Local Storage') }}</b>
                                                                </h4>
                                                            </button>
                                                        </h2>
                                                        <div id="collapseOne"
                                                            class="accordion-collapse collapse {{ empty($settings['storage_type']) || $settings['storage_type'] == 'local' ? 'show' : '' }}"
                                                            aria-labelledby="headingOne"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div class="row">
                                                                    <div class="col-md-12 form-group">
                                                                        {{ Form::label('local_file_type', __('File Type'), ['class' => 'form-label']) }}
                                                                        {!! Form::select(
                                                                            'local_file_type[]',
                                                                            FilesExtension(),
                                                                            !empty($settings['local_file_type']) ? explode(',', $settings['local_file_type']) : null,
                                                                            [
                                                                                'class' => 'form-control select2',
                                                                                'multiple' => true,
                                                                            ],
                                                                        ) !!}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header position-relative" id="headingTwo">
                                                            <button
                                                                class="accordion-button {{ !empty($settings['storage_type']) && $settings['storage_type'] == 's3' ? '' : 'collapsed' }}"
                                                                type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#collapseTwo" aria-expanded="false"
                                                                aria-controls="collapseTwo">
                                                                <div class="form-check form-switch onoff_storage">
                                                                    <input type="radio"
                                                                        class="form-check-input input-primary storage_switch"
                                                                        id="customswitchv2" name="storage_type"
                                                                        value="s3"
                                                                        {{ !empty($settings['storage_type']) && $settings['storage_type'] == 's3' ? 'checked' : '' }}>
                                                                </div>

                                                                <h4 class="mb-0"><b>{{ __('AWS S3 Storage') }}</b>
                                                                </h4>
                                                            </button>
                                                        </h2>
                                                        <div id="collapseTwo"
                                                            class="accordion-collapse collapse  {{ !empty($settings['storage_type']) && $settings['storage_type'] == 's3' ? 'show' : '' }}"
                                                            aria-labelledby="headingTwo"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div class="row">
                                                                    <div class="col-md-6 form-group">
                                                                        {{ Form::label('aws_s3_key', __('S3 Key'), ['class' => 'form-label']) }}
                                                                        {{ Form::text('aws_s3_key', $settings['aws_s3_key'], ['class' => 'form-control', 'placeholder' => __('Enter key')]) }}
                                                                    </div>
                                                                    <div class="col-md-6 form-group">
                                                                        {{ Form::label('aws_s3_secret', __('S3 Secret'), ['class' => 'form-label']) }}
                                                                        {{ Form::text('aws_s3_secret', $settings['aws_s3_secret'], ['class' => 'form-control', 'placeholder' => __('Enter Secret')]) }}
                                                                    </div>
                                                                    <div class="col-md-6 form-group">
                                                                        {{ Form::label('aws_s3_region', __('S3 region'), ['class' => 'form-label']) }}
                                                                        {{ Form::text('aws_s3_region', $settings['aws_s3_region'], ['class' => 'form-control', 'placeholder' => __('Enter region')]) }}
                                                                    </div>
                                                                    <div class="col-md-6 form-group">
                                                                        {{ Form::label('aws_s3_bucket', __('S3 bucket'), ['class' => 'form-label']) }}
                                                                        {{ Form::text('aws_s3_bucket', $settings['aws_s3_bucket'], ['class' => 'form-control', 'placeholder' => __('Enter bucket')]) }}
                                                                    </div>
                                                                    <div class="col-md-6 form-group">
                                                                        {{ Form::label('aws_s3_url', __('S3 URL'), ['class' => 'form-label']) }}
                                                                        {{ Form::text('aws_s3_url', $settings['aws_s3_url'], ['class' => 'form-control', 'placeholder' => __('Enter URL')]) }}
                                                                    </div>
                                                                    <div class="col-md-6 form-group">
                                                                        {{ Form::label('aws_s3_endpoint', __('S3 Endpoint'), ['class' => 'form-label']) }}
                                                                        {{ Form::text('aws_s3_endpoint', $settings['aws_s3_endpoint'], ['class' => 'form-control', 'placeholder' => __('Enter Endpoint')]) }}
                                                                    </div>
                                                                    <div class="col-md-12 form-group">
                                                                        {{ Form::label('aws_s3_file_type', __('File Type'), ['class' => 'form-label']) }}
                                                                        {!! Form::select(
                                                                            'aws_s3_file_type[]',
                                                                            FilesExtension(),
                                                                            !empty($settings['aws_s3_file_type']) ? explode(',', $settings['aws_s3_file_type']) : null,
                                                                            [
                                                                                'class' => 'form-control select2',
                                                                                'multiple' => true,
                                                                            ],
                                                                        ) !!}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header position-relative" id="headingThree">
                                                            <button
                                                                class="accordion-button {{ !empty($settings['storage_type']) && $settings['storage_type'] == 'wasabi' ? '' : 'collapsed' }}"
                                                                type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#collapseThree" aria-expanded="false"
                                                                aria-controls="collapseThree">
                                                                <div class="form-check form-switch onoff_storage">
                                                                    <input type="radio"
                                                                        class="form-check-input input-primary storage_switch"
                                                                        id="customswitchv3" name="storage_type"
                                                                        value="wasabi"
                                                                        {{ !empty($settings['storage_type']) && $settings['storage_type'] == 'wasabi' ? 'checked' : '' }}>
                                                                </div>
                                                                <h4 class="mb-0"><b>{{ __('Wasabi Storage') }}</b>
                                                                </h4>
                                                            </button>
                                                        </h2>
                                                        <div id="collapseThree"
                                                            class="accordion-collapse collapse {{ !empty($settings['storage_type']) && $settings['storage_type'] == 'wasabi' ? 'show' : '' }}"
                                                            aria-labelledby="headingThree"
                                                            data-bs-parent="#accordionExample">
                                                            <div class="accordion-body">
                                                                <div class="row">
                                                                    <div class="col-md-6 form-group">
                                                                        {{ Form::label('wasabi_key', __('Wasabi Key'), ['class' => 'form-label']) }}
                                                                        {{ Form::text('wasabi_key', $settings['wasabi_key'], ['class' => 'form-control', 'placeholder' => __('Enter key')]) }}
                                                                    </div>
                                                                    <div class="col-md-6 form-group">
                                                                        {{ Form::label('wasabi_secret', __('Wasabi Secret'), ['class' => 'form-label']) }}
                                                                        {{ Form::text('wasabi_secret', $settings['wasabi_secret'], ['class' => 'form-control', 'placeholder' => __('Enter Secret')]) }}
                                                                    </div>
                                                                    <div class="col-md-6 form-group">
                                                                        {{ Form::label('wasabi_region', __('Wasabi region'), ['class' => 'form-label']) }}
                                                                        {{ Form::text('wasabi_region', $settings['wasabi_region'], ['class' => 'form-control', 'placeholder' => __('Enter region')]) }}
                                                                    </div>
                                                                    <div class="col-md-6 form-group">
                                                                        {{ Form::label('wasabi_bucket', __('Wasabi bucket'), ['class' => 'form-label']) }}
                                                                        {{ Form::text('wasabi_bucket', $settings['wasabi_bucket'], ['class' => 'form-control', 'placeholder' => __('Enter bucket')]) }}
                                                                    </div>
                                                                    <div class="col-md-12 form-group">
                                                                        {{ Form::label('wasabi_url', __('Wasabi URL'), ['class' => 'form-label']) }}
                                                                        {{ Form::text('wasabi_url', $settings['wasabi_url'], ['class' => 'form-control', 'placeholder' => __('Enter URL')]) }}
                                                                    </div>

                                                                    <div class="col-md-12 form-group">
                                                                        {{ Form::label('wasabi_file_type', __('File Type'), ['class' => 'form-label']) }}
                                                                        {!! Form::select(
                                                                            'wasabi_file_type[]',
                                                                            FilesExtension(),
                                                                            !empty($settings['wasabi_file_type']) ? explode(',', $settings['wasabi_file_type']) : null,
                                                                            [
                                                                                'class' => 'form-control select2',
                                                                                'multiple' => true,
                                                                            ],
                                                                        ) !!}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 text-end mt-3">
                                                {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                            </div>
                                        </div>
                                        {{ Form::close() }}

                                    </div>
                                @endif
                                @if (Gate::check('manage twilio settings'))
                                    <div class="tab-pane {{ !empty($activeTab) && $activeTab == 'twilio' ? ' active show ' : '' }}"
                                        id="twilio" role="tabpanel" aria-labelledby="twilio">

                                        {{ Form::model($settings, ['route' => ['twilio.settings'], 'method' => 'post']) }}

                                        <div class="row">
                                            <div class="form-group col-md-12">
                                                {{ Form::label('twilio_sid', __('twilio SID'), ['class' => 'form-label']) }}
                                                {{ Form::text('twilio_sid', $settings['twilio_sid'], ['class' => 'form-control  mb-2', 'placeholder' => __('Enter SID')]) }}
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-12">
                                                {{ Form::label('twilio_token', __('twilio Token'), ['class' => 'form-label']) }}
                                                {{ Form::text('twilio_token', $settings['twilio_token'], ['class' => 'form-control  mb-2', 'placeholder' => __('Enter Token')]) }}
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-12">
                                                {{ Form::label('twilio_from_number', __('twilio From Number'), ['class' => 'form-label']) }}
                                                {{ Form::text('twilio_from_number', $settings['twilio_from_number'], ['class' => 'form-control  mb-2', 'placeholder' => __('Enter From Numner')]) }}
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-6"></div>
                                            <div class="col-6 text-end">
                                                {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                            </div>
                                        </div>
                                        {{ Form::close() }}
                                    </div>
                                @endif
                                @can('manage openai settings')
                                    <div class="tab-pane {{ !empty($activeTab) && $activeTab == 'openai' ? ' active show ' : '' }}"
                                        id="openai" role="tabpanel" aria-labelledby="openai">

                                        {{ Form::model($settings, ['route' => ['openai.settings'], 'method' => 'POST']) }}

                                        <div class="row">
                                            <div class="form-group col-md-12">
                                                {{ Form::label('openai_secret_key', __('Open ai secret key'), ['class' => 'form-label']) }}

                                                {{ Form::text('openai_secret_key', $settings['openai_secret_key'] ?? null, [
                                                    'class' => 'form-control mb-2',
                                                    'placeholder' => __('Enter open ai secret key'),
                                                    'id' => 'openai_secret_key',
                                                ]) }}
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    {{ Form::label('openai_module', __('Enable OpenAI Module'), ['class' => 'form-label']) }}
                                                    <div class="flex-shrink-0">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="openai_module" name="openai_module"
                                                                {{ $settings['openai_module'] == 'on' ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-6"></div>
                                            <div class="col-6 text-end">
                                                {{ Form::submit(__('Save'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                            </div>
                                        </div>

                                        {{ Form::close() }}
                                    </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $('.storage_switch').on('click mousedown', function(e) {
            e.stopPropagation();
        });
    </script>
@endpush
