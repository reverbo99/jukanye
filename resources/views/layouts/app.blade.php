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
                --jk-gold: #dfc91b;
                --jk-orange: #e87a2e;
                --jk-teal: #0ca3a6;
                --jk-muted: #6b7874;
                --jk-line: #e4e0d6;
                --jk-bg: #f3f1ea;
                --jk-focus: #0ca3a6;
                --jk-focus-ring: rgba(12, 163, 166, 0.35);
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
                border-bottom: 1px solid var(--jk-line);
            }
            .jk-app-main-head > div {
                padding-top: 1rem;
                padding-bottom: 1rem;
            }
            .jk-app-card {
                background: #fff;
                border: 1px solid var(--jk-line);
                border-radius: 0.65rem;
                padding: 1.5rem 1.4rem;
                box-shadow: 0 1px 0 rgba(20, 34, 31, 0.03);
            }
            .jk-app-card--danger {
                border-color: #ead4d0;
                background: #fffaf9;
            }
            .jk-app-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.4rem;
                padding: 0.55rem 1.1rem;
                font-size: 0.8rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                background: linear-gradient(135deg, #dfc91b 0%, #e6b423 55%, #e0872a 100%);
                color: #1a1000;
                border: 0;
                cursor: pointer;
                text-decoration: none;
                transition: filter 0.15s ease, box-shadow 0.15s ease;
            }
            .jk-app-btn:hover {
                filter: brightness(1.03);
            }
            .jk-app-btn:focus {
                outline: none;
                box-shadow: 0 0 0 3px var(--jk-focus-ring);
            }
            .jk-app-btn-ghost {
                background: transparent;
                color: rgba(245, 239, 230, 0.9);
                border: 1px solid rgba(223, 201, 27, 0.35);
            }
            .jk-app-btn-ghost:hover {
                background: rgba(223, 201, 27, 0.1);
                border-color: rgba(223, 201, 27, 0.55);
                color: var(--jk-accent);
                filter: none;
            }
            /* Primary form submits only — keep danger / ghost distinct */
            .jk-app-body .jk-app-btn:not(.jk-app-btn-ghost):not(.jk-profile-danger-btn),
            .jk-app-body button.jk-app-btn[type="submit"] {
                background: linear-gradient(135deg, #dfc91b 0%, #e6b423 55%, #e0872a 100%) !important;
                color: #1a1000 !important;
                border: none !important;
                font-weight: 700 !important;
                letter-spacing: 0.04em !important;
                text-transform: uppercase !important;
            }
            .jk-app-body .jk-profile-danger-btn {
                background: #b42318 !important;
                color: #fff !important;
                border: none !important;
            }
            .jk-app-body .jk-profile-danger-btn:focus {
                box-shadow: 0 0 0 3px rgba(180, 35, 24, 0.3);
            }

            /* Profile page layout */
            .jk-profile-title {
                margin: 0;
                font-family: "DM Serif Display", Georgia, serif;
                font-size: 1.45rem;
                font-weight: 400;
                line-height: 1.25;
                color: var(--jk-ink);
            }
            .jk-profile-lead {
                margin: 0.3rem 0 0;
                font-size: 0.9rem;
                color: var(--jk-muted);
            }
            .jk-profile-page {
                padding: 1.25rem 1rem 2.5rem;
            }
            .jk-profile-column {
                width: 100%;
                max-width: 40rem;
                margin: 0 auto;
            }
            .jk-profile-section__head {
                margin-bottom: 0.15rem;
            }
            .jk-profile-section__title {
                margin: 0;
                font-size: 1.05rem;
                font-weight: 700;
                color: var(--jk-ink);
                letter-spacing: 0.01em;
            }
            .jk-profile-section__desc {
                margin: 0.4rem 0 0;
                font-size: 0.9rem;
                line-height: 1.45;
                color: var(--jk-muted);
            }
            .jk-profile-form {
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
                margin-top: 1.35rem;
            }
            .jk-profile-field label,
            .jk-profile-avatar__fields label {
                margin-bottom: 0.45rem;
                color: var(--jk-ink);
                font-weight: 600;
            }
            .jk-profile-field input,
            .jk-profile-avatar__fields input[type="file"] {
                margin-top: 0;
            }
            .jk-app-body .jk-profile-field input:focus,
            .jk-app-body .jk-profile-field input:focus-visible {
                border-color: var(--jk-focus) !important;
                --tw-ring-color: var(--jk-focus-ring) !important;
                box-shadow: 0 0 0 3px var(--jk-focus-ring);
                outline: none;
            }
            .jk-profile-avatar {
                display: flex;
                align-items: flex-start;
                gap: 1.1rem;
                padding: 1rem;
                border: 1px dashed rgba(20, 34, 31, 0.16);
                border-radius: 0.55rem;
                background: #faf8f3;
            }
            .jk-profile-avatar__preview {
                flex-shrink: 0;
            }
            .jk-profile-avatar__img,
            .jk-profile-avatar__fallback {
                width: 5rem;
                height: 5rem;
                border-radius: 999px;
                object-fit: cover;
                border: 2px solid var(--jk-accent);
            }
            .jk-profile-avatar__fallback {
                display: flex;
                align-items: center;
                justify-content: center;
                background: #e4e0d6;
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--jk-muted);
            }
            .jk-profile-avatar__fields {
                flex: 1;
                min-width: 0;
            }
            .jk-profile-file {
                display: block;
                width: 100%;
                font-size: 0.875rem;
                color: var(--jk-muted);
            }
            .jk-profile-file::file-selector-button {
                margin-right: 0.85rem;
                padding: 0.45rem 0.85rem;
                border: 0;
                border-radius: 0.4rem;
                background: var(--jk-ink);
                color: #f5efe6;
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                cursor: pointer;
            }
            .jk-profile-file::file-selector-button:hover {
                background: var(--jk-panel);
            }
            .jk-profile-hint {
                margin: 0.45rem 0 0;
                font-size: 0.8rem;
                color: var(--jk-muted);
            }
            .jk-profile-verify {
                margin-top: 0.65rem;
            }
            .jk-profile-link {
                background: none !important;
                border: 0 !important;
                padding: 0 !important;
                color: var(--jk-teal) !important;
                text-decoration: underline;
                font-size: inherit;
                font-weight: 600 !important;
                letter-spacing: normal !important;
                text-transform: none !important;
                cursor: pointer;
            }
            .jk-profile-link:focus {
                outline: none;
                box-shadow: 0 0 0 3px var(--jk-focus-ring);
                border-radius: 0.2rem;
            }
            .jk-profile-actions {
                display: flex;
                align-items: center;
                gap: 0.85rem;
                padding-top: 0.25rem;
            }
            .jk-profile-saved {
                margin: 0;
                font-size: 0.875rem;
                font-weight: 600;
                color: #1b5e20;
            }
            @media (max-width: 640px) {
                .jk-profile-page {
                    padding: 1rem 0.75rem 2rem;
                }
                .jk-app-card {
                    padding: 1.2rem 1rem;
                    border-radius: 0.5rem;
                }
                .jk-profile-avatar {
                    flex-direction: column;
                    align-items: center;
                    text-align: center;
                }
                .jk-profile-avatar__fields {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body class="jk-app-body antialiased">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @isset($header)
                <header class="jk-app-main-head">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
