@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>

    <title>{{ filled($title) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link href="{{ asset('vendor/cooladmin/css/font-face.css') }}" rel="stylesheet" media="all"/>
    <link rel="preconnect" href="https://rsms.me/"/>
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css"/>
    <link href="{{ asset('vendor/cooladmin/vendor/fontawesome-7.3.1/css/all.min.css') }}" rel="stylesheet" media="all"/>
    <link href="{{ asset('vendor/cooladmin/vendor/bootstrap-5.3.8.min.css') }}" rel="stylesheet" media="all"/>
    <link href="{{ asset('vendor/cooladmin/vendor/css-hamburgers/hamburgers.min.css') }}" rel="stylesheet" media="all"/>
    <link href="{{ asset('vendor/cooladmin/css/theme.css') }}" rel="stylesheet" media="all"/>
    <link href="{{ asset('vendor/cooladmin/css/app.css') }}" rel="stylesheet" media="all"/>
</head>
<body class="app auth-page">
    <a class="visually-hidden-focusable skip-link" href="#auth-form">Skip to sign-in form</a>
    <main class="login-wrap" id="auth-form">
        <div class="login-content">
            <a href="{{ route('home') }}" class="auth-brand" aria-label="{{ config('app.name', 'Laravel') }} home">
                <img class="brand-logo" src="{{ asset('images/cropped-Tech-Key-Monk-Colour-Logo-1-1-1536x1536.png') }}" alt="{{ config('app.name', 'Laravel') }}">
            </a>

            {{ $slot }}
        </div>
    </main>

    <script src="{{ asset('vendor/cooladmin/js/vanilla-utils.js') }}"></script>
    <script src="{{ asset('vendor/cooladmin/vendor/bootstrap-5.3.8.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/cooladmin/js/bootstrap5-init.js') }}"></script>
    <script src="{{ asset('vendor/cooladmin/js/main-vanilla.js') }}"></script>
    <script src="{{ asset('vendor/cooladmin/js/modern-plugins.js') }}"></script>
</body>
</html>
