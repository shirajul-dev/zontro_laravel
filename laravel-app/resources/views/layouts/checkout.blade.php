<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $brand['name'] ?? 'Secure Payment')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ $brand['favicon'] ?? '' }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Core Styles -->
    <style>
        :root {
            --primary-color: {{ $options['primary_color'] ?? '#4F46E5' }};
            --text-color: {{ $options['text_color'] ?? '#1F2937' }};
            --btn-color: {{ $options['btn_color'] ?? '#4F46E5' }};
            --btn-text-color: {{ $options['btn_text_color'] ?? '#FFFFFF' }};
            --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gradient);
            color: var(--text-color);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        h1, h2, h3, .font-outfit {
            font-family: 'Outfit', sans-serif;
        }

        .checkout-container {
            width: 100%;
            max-width: 500px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .checkout-header {
            padding: 40px 30px;
            text-align: center;
            background: white;
            border-bottom: 1px solid #f1f5f9;
        }

        .brand-logo {
            max-height: 50px;
            margin-bottom: 20px;
        }

        .checkout-content {
            padding: 30px;
        }

        .btn-primary {
            background: var(--btn-color);
            color: var(--btn-text-color);
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        @yield('extra_css')
    </style>

    @stack('head')
</head>
<body>
    <div class="checkout-container">
        <header class="checkout-header">
            @if(isset($brand['logo']))
                <img src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }}" class="brand-logo">
            @endif
            <h1 class="text-xl font-bold">@yield('header_title')</h1>
        </header>

        <main class="checkout-content">
            @yield('content')
        </main>

        <footer class="checkout-footer" style="padding: 20px; text-align: center; font-size: 0.875rem; color: #64748b;">
            <p>&copy; {{ date('Y') }} {{ $brand['name'] }}. Secured by PipraPay.</p>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
