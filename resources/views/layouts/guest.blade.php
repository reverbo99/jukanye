<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'JuKaNye')) — {{ __('Sign in') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-serif-display:400|source-sans-3:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --jk-bg: #0d0404;
                --jk-bg-2: #1a0c0c;
                --jk-gold: #dfc91b;
                --jk-teal: #0ca3a6;
                --jk-orange: #e87a2e;
                --jk-text: #f5efe6;
                --jk-muted: rgba(245, 239, 230, 0.62);
                --jk-line: rgba(223, 201, 27, 0.28);
            }

            body.jk-auth-body {
                font-family: "Source Sans 3", ui-sans-serif, system-ui, sans-serif;
                color: var(--jk-text);
                background:
                    radial-gradient(ellipse 80% 50% at 50% -10%, rgba(12, 163, 166, 0.18), transparent 55%),
                    radial-gradient(ellipse 60% 40% at 100% 100%, rgba(232, 122, 46, 0.12), transparent 50%),
                    linear-gradient(165deg, var(--jk-bg) 0%, var(--jk-bg-2) 48%, #120808 100%);
                min-height: 100vh;
            }

            .jk-auth-shell {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 2rem 1rem 3rem;
            }

            .jk-auth-brand {
                text-align: center;
                margin-bottom: 1.75rem;
                text-decoration: none;
            }

            .jk-auth-brand__mark {
                font-family: "DM Serif Display", Georgia, serif;
                font-size: 2.35rem;
                letter-spacing: 0.04em;
                color: var(--jk-gold);
                line-height: 1;
            }

            .jk-auth-brand__sub {
                margin-top: 0.45rem;
                font-size: 0.72rem;
                font-weight: 600;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: var(--jk-muted);
            }

            .jk-auth-card {
                width: 100%;
                max-width: 26rem;
                padding: 1.75rem 1.5rem 1.5rem;
                background: rgba(13, 4, 4, 0.72);
                border: 1px solid var(--jk-line);
                box-shadow:
                    0 24px 48px rgba(0, 0, 0, 0.45),
                    inset 0 1px 0 rgba(255, 255, 255, 0.04);
                backdrop-filter: blur(8px);
            }

            .jk-auth-card h1 {
                font-family: "DM Serif Display", Georgia, serif;
                font-size: 1.55rem;
                color: var(--jk-text);
                margin: 0 0 0.35rem;
            }

            .jk-auth-card .jk-auth-lead {
                color: var(--jk-muted);
                font-size: 0.92rem;
                margin: 0 0 1.35rem;
                line-height: 1.45;
            }

            .jk-auth .block.font-medium {
                color: rgba(245, 239, 230, 0.82) !important;
            }

            .jk-auth input[type="email"],
            .jk-auth input[type="password"],
            .jk-auth input[type="text"] {
                background: rgba(0, 0, 0, 0.35) !important;
                border-color: rgba(245, 239, 230, 0.16) !important;
                color: var(--jk-text) !important;
                box-shadow: none !important;
            }

            .jk-auth input:focus {
                border-color: var(--jk-teal) !important;
                --tw-ring-color: rgba(12, 163, 166, 0.45) !important;
            }

            .jk-auth input[type="checkbox"] {
                border-color: rgba(245, 239, 230, 0.3) !important;
                background-color: rgba(0, 0, 0, 0.35) !important;
                color: var(--jk-gold) !important;
            }

            .jk-auth .text-gray-600,
            .jk-auth .text-sm.text-gray-600 {
                color: var(--jk-muted) !important;
            }

            .jk-auth a.underline,
            .jk-auth a.text-sm {
                color: var(--jk-teal) !important;
                text-decoration-color: rgba(12, 163, 166, 0.45);
            }

            .jk-auth a.underline:hover {
                color: var(--jk-gold) !important;
            }

            .jk-auth button[type="submit"],
            .jk-auth .jk-auth-submit {
                background: linear-gradient(135deg, var(--jk-gold), var(--jk-orange)) !important;
                color: #1a1000 !important;
                border: none !important;
                font-weight: 700 !important;
                letter-spacing: 0.06em !important;
                text-transform: uppercase !important;
                box-shadow: none !important;
            }

            .jk-auth button[type="submit"]:hover,
            .jk-auth .jk-auth-submit:hover {
                filter: brightness(1.05);
            }

            .jk-auth-footer {
                margin-top: 1.25rem;
                text-align: center;
                font-size: 0.8rem;
                color: var(--jk-muted);
            }

            .jk-auth-footer a {
                color: var(--jk-gold);
                text-decoration: none;
            }

            .jk-auth-footer a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body class="jk-auth-body antialiased">
        <div class="jk-auth-shell">
            <a href="{{ url('/site') }}" class="jk-auth-brand">
                <div class="jk-auth-brand__mark">JuKaNye</div>
                <div class="jk-auth-brand__sub">International Festival</div>
            </a>

            <div class="jk-auth-card jk-auth">
                {{ $slot }}
            </div>

            <p class="jk-auth-footer">
                <a href="{{ url('/site') }}">{{ __('Back to festival site') }}</a>
            </p>
        </div>
    </body>
</html>
