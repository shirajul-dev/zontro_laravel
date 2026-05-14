<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        $piprapay_current_version = config('zontropay.current_version');
        $csrfToken = $csrfToken ?? ($csrf_token ?? '');
        $isSuperAdmin = ($global_user_response['response'][0]['user_type'] ?? '') === 'superadmin';
    ?>
    <meta charset="utf-8">
    <meta name="author" content="ZontroPay">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ config('app.name') }} | {{ $title ?? '' }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon-light.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tabler.min.css?v=1.7') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/choices.min.css') }}" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-flags.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-payments.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-social.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-vendors.min.css" />

    <style>
      @import url("{{ asset('assets/css/inter.css') }}");
    </style>
    <style>
        :root{
            --tblr-font-monospace: Monaco, Consolas, Liberation Mono, Courier New, monospace;
            --tblr-font-sans-serif: Inter Var, Inter, -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
            --tblr-font-serif: Georgia, Times New Roman, times, serif;
            --tblr-font-comic: Comic Sans MS, Comic Sans, Chalkboard SE, Comic Neue, sans-serif, cursive;
        }
        #sidebarMenu {
            max-width: 310px;
            width: 100%;
        }
        #sidebarMenu ul{
            padding: 15px;
        }
        #sidebarMenu li.nav-item a{
            height: 40px;
        }
        #sidebarMenu li.nav-item a .nav-link-icon{
            width: 1.45rem;
            min-width: 1.45rem;
            height: 1.45rem;
            margin-right: .2rem;
        }
        #sidebarMenu li.nav-item a .nav-link-icon svg{
            width: 1.45rem;
            min-width: 1.45rem;
            height: 1.45rem;
        }
        #sidebarMenu li.card-title{
            margin-left: 15px;
            font-size: .875rem;
            margin-bottom: 0px;
        }
        .page-wrapper{
            margin: 15px;
        }

        .choices {
            font-size: .875rem;
            font-weight: 400;
        }
        .choices__inner {
            display: inline-block;
            vertical-align: top;
            width: 100%;
            background-color: #FFFFFF;
            padding: .5625rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: calc(6px * 1);
            font-size: .875rem;
            font-weight: 400;
            min-height: 0;
            overflow: hidden;
        }
        .choices__list--single{
            padding: 0.8px;
        }
        .choices__list--multiple .choices__item {
            display: inline-block;
            vertical-align: middle;
            border-radius: 15px;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 400;
            margin-right: 1.75px;
            margin-bottom: 1.75px;
            background-color: var(--tblr-primary);
            border: 1px solid var(--tblr-primary);
            color: #fff;
            word-break: break-all;
            box-sizing: border-box;
        }
        .choices__input {
            display: inline-block;
            vertical-align: baseline;
            background-color: #FFFFFF;
            font-size: .875rem;
            font-weight: 400;
            margin-bottom: 0;
            border: 0;
            border-radius: 0;
            max-width: 100%;
            padding: 0;
        }
        .is-focused .choices__inner, .is-open .choices__inner{
            border: 1px solid #e5e7eb;
            border-radius: calc(6px * 1);
            color: var(--tblr-body-color);
            background-color: var(--tblr-bg-forms);
            border-color: rgb(126, 94, 255);
            outline: 0;
            box-shadow: var(--tblr-shadow-input), 0 0 0 .25rem rgba(var(--tblr-primary-rgb), .25)
        }
        .is-open .choices__list--dropdown, .is-open .choices__list[aria-expanded]{
            border: 1px solid #e5e7eb;
            border-radius: calc(6px * 1);
            box-shadow: 0 0 4px rgba(31, 41, 55, 0.04);
        }
        .choices__list--dropdown, .choices__list[aria-expanded]{
            z-index: 3;
        }

        @media (min-width: 768px) {
            #sidebarMenu {
                height: calc(100vh - 64px); /* full viewport minus header height */
                overflow-y: auto;          /* scroll inside sidebar */
                position: fixed;
                top: 64px;                  /* below header */
                left: 0;
                background: #f8f9fa;
            }
            .page-wrapper{
                margin-left: 325px;
            }
        }
    </style>
</head>
<body class="layout-fluid" cz-shortcut-listen="true">

    @include('admin.partials._top_progress')

    <div class="page">
        <!-- BEGIN NAVBAR  -->
        @include('admin.partials.header')

        <!-- SIDEBAR -->
        @include('admin.partials.sidebar')

        <div class="page-wrapper">
            <div class="root-print" style="max-width: 1200px; width: 100%; margin: auto; margin-top: 0px;">
                <center><div class="spinner-border text-primary" style="margin-top: 150px;">  <span class="visually-hidden">Loading...</span></div></center>
            </div>

            @include('admin.partials.footer')

        </div>
    </div>


    @include('admin.partials.modal')

    <input type="hidden" name="csrf_token_default" value="{{ $csrfToken }}">

    @include('admin.partials._scripts')
    @stack('scripts')
</body>
</html>
