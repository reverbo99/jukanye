<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('app.name', 'Jukanye') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --jk-ink: #14221f;
            --jk-panel: #1c2f2b;
            --jk-accent: #c9a227;
            --jk-muted: #8aa39c;
            --jk-bg: #f3f1ea;
        }
        body.admin-body { font-family: Figtree, ui-sans-serif, system-ui, sans-serif; background: var(--jk-bg); color: var(--jk-ink); }
        .admin-shell { min-height: 100vh; display: grid; grid-template-columns: 260px 1fr; }
        .admin-sidebar { background: linear-gradient(180deg, var(--jk-ink), var(--jk-panel)); color: #f5f7f6; padding: 1.25rem 0.85rem; }
        .admin-brand { font-weight: 700; letter-spacing: 0.04em; padding: 0.5rem 0.75rem 1.25rem; color: var(--jk-accent); }
        .admin-nav a { display: block; padding: 0.55rem 0.75rem; border-radius: 0.4rem; color: #d7e3df; text-decoration: none; font-size: 0.92rem; margin-bottom: 0.15rem; }
        .admin-nav a:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .admin-nav a.active { background: rgba(201,162,39,0.18); color: var(--jk-accent); }
        .admin-nav .nav-section { margin: 1rem 0.75rem 0.4rem; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--jk-muted); }
        .admin-main { display: flex; flex-direction: column; min-width: 0; }
        .admin-top { background: #fff; border-bottom: 1px solid #e4e0d6; padding: 0.9rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .admin-content { padding: 1.5rem; }
        .admin-card { background: #fff; border: 1px solid #e4e0d6; border-radius: 0.6rem; padding: 1.25rem; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { text-align: left; padding: 0.7rem 0.5rem; border-bottom: 1px solid #ece7db; font-size: 0.92rem; vertical-align: top; }
        .admin-table th { color: #5d6b67; font-weight: 600; }
        .btn { display: inline-flex; align-items: center; gap: 0.35rem; border-radius: 0.4rem; padding: 0.45rem 0.85rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; border: 0; cursor: pointer; }
        .btn-primary { background: var(--jk-ink); color: #fff; }
        .btn-accent { background: var(--jk-accent); color: #1a1608; }
        .btn-danger { background: #b42318; color: #fff; }
        .btn-ghost { background: transparent; color: var(--jk-ink); border: 1px solid #d7d0c2; }
        .form-grid { display: grid; gap: 1rem; }
        .form-grid.two { grid-template-columns: 1fr 1fr; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; }
        input[type=text], input[type=email], input[type=number], input[type=datetime-local], input[type=file], select, textarea {
            width: 100%; border: 1px solid #d7d0c2; border-radius: 0.4rem; padding: 0.55rem 0.7rem; background: #fff;
        }
        textarea { min-height: 110px; }
        .flash { padding: 0.75rem 1rem; border-radius: 0.4rem; margin-bottom: 1rem; background: #e8f5e9; color: #1b5e20; }
        .badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-published { background: #dcfce7; color: #166534; }
        .badge-draft { background: #fef3c7; color: #92400e; }
        .page-head { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .page-head h1 { font-size: 1.4rem; font-weight: 700; margin: 0; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat { background: #fff; border: 1px solid #e4e0d6; border-radius: 0.6rem; padding: 1rem; }
        .stat strong { display: block; font-size: 1.5rem; }
        .muted { color: #6b7874; font-size: 0.9rem; }
        .actions { display: flex; gap: 0.4rem; flex-wrap: wrap; }
        @media (max-width: 960px) {
            .admin-shell { grid-template-columns: 1fr; }
            .form-grid.two, .stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="admin-body">
<div class="admin-shell">
    @include('admin.partials.sidebar')
    <div class="admin-main">
        <div class="admin-top">
            <div>@yield('heading', 'Dashboard')</div>
            <div class="actions">
                <a class="btn btn-ghost" href="{{ route('profile.edit') }}">{{ Auth::user()->name }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost">Logout</button>
                </form>
            </div>
        </div>
        <div class="admin-content">
            @if (session('success'))
                <div class="flash">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="flash" style="background:#fee2e2;color:#991b1b;">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="flash" style="background:#fee2e2;color:#991b1b;">
                    <ul style="margin:0;padding-left:1.1rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
