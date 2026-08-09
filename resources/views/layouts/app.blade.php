<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? __('Account') }} — {{ config('app.name', 'JuKaNye') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-serif-display:400|source-sans-3:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --jk-ink: #14221f;
                --jk-panel: #1c2f2b;
                --jk-accent: #dfc91b;
                --jk-teal: #0ca3a6;
                --jk-muted: #8aa39c;
                --jk-bg: #f3f1ea;
            }
            body.jk-app-body {
                font-family: "Source Sans 3", ui-sans-serif, system-ui, sans-serif;
                background: var(--jk-bg);
                color: var(--jk-ink);
                min-height: 100vh;
            }
            .jk-app-top {
                background: linear-gradient(90deg, #0d0404, #14221f 55%, #1c2f2b);
                border-bottom: 1px solid rgba(223, 201, 27, 0.28);
            }
            .jk-app-brand {
                font-family: "DM Serif Display", Georgia, serif;
                color: var(--jk-accent);
                font-size: 1.35rem;
                letter-spacing: 0.04em;
                text-decoration: none;
            }
            .jk-app-brand span {
                display: block;
                font-family: "Source Sans 3", sans-serif;
                font-size: 0.65rem;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: rgba(245, 239, 230, 0.55);
                margin-top: 0.1rem;
            }
            .jk-app-nav a {
                color: rgba(245, 239, 230, 0.78);
                text-decoration: none;
                font-size: 0.9rem;
                font-weight: 600;
            }
            .jk-app-nav a:hover,
            .jk-app-nav a.active {
                color: var(--jk-accent);
            }
            .jk-app-main-head {
                background: #fff;
                border-bottom: 1px solid #e4e0d6;
            }
            .jk-app-card {
                background: #fff;
                border: 1px solid #e4e0d6;
                border-radius: 0.6rem;
                padding: 1.25rem;
            }
            .jk-app-btn {
                display: inline-flex;
                align-items: center;
                border-radius: 0.4rem;
                padding: 0.45rem 0.85rem;
                font-size: 0.8rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                background: linear-gradient(135deg, #dfc91b, #e87a2e);
                color: #1a1000;
                border: 0;
                cursor: pointer;
                text-decoration: none;
            }
            .jk-app-btn-ghost {
                background: transparent;
                color: rgba(245, 239, 230, 0.85);
                border: 1px solid rgba(245, 239, 230, 0.2);
            }
            .jk-app-body button[type="submit"]:not(.jk-app-btn-ghost) {
                background: linear-gradient(135deg, #dfc91b, #e87a2e) !important;
                color: #1a1000 !important;
                border: none !important;
                font-weight: 700 !important;
                letter-spacing: 0.04em !important;
                text-transform: uppercase !important;
            }
        </style>
    </head>
    <body class="jk-app-body antialiased">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @isset($header)
                <header class="jk-app-main-head">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
