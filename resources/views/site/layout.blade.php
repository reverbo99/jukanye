<!DOCTYPE html>
<html lang="{{ $locale === 'sw' ? 'sw' : 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — Jukanye Festival</title>
    <style>
        :root {
            --jk-ink: #14221f;
            --jk-teal: #0ca3a6;
            --jk-gold: #c9a227;
            --jk-bg: #f3f1ea;
            --jk-card: #ffffff;
            --jk-muted: #5d6b67;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--jk-ink);
            background: linear-gradient(180deg, #e8f4f3 0%, var(--jk-bg) 36%, #efe8d8 100%);
            min-height: 100vh;
        }
        a { color: var(--jk-teal); }
        .jk-top {
            background: linear-gradient(90deg, #0e2a27, #163f3a 55%, #1a4a36);
            color: #fff;
            padding: .85rem 1.25rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .75rem 1.25rem;
            justify-content: space-between;
        }
        .jk-brand {
            font-weight: 700;
            letter-spacing: .06em;
            color: var(--jk-gold);
            text-decoration: none;
            font-size: 1.05rem;
        }
        .jk-nav {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem .85rem;
            list-style: none;
            margin: 0;
            padding: 0;
            max-width: 920px;
            justify-content: flex-end;
        }
        .jk-nav a {
            color: #dcece9;
            text-decoration: none;
            font-size: .82rem;
            white-space: nowrap;
        }
        .jk-nav a:hover, .jk-nav .active a { color: var(--jk-gold); }
        .jk-langs a {
            color: #fff;
            text-decoration: none;
            font-size: .8rem;
            margin-left: .5rem;
            opacity: .85;
        }
        .jk-langs a.active { color: var(--jk-gold); opacity: 1; font-weight: 700; }
        .jk-wrap { max-width: 1000px; margin: 0 auto; padding: 2rem 1.25rem 3rem; }
        .jk-wrap h1 {
            margin: 0 0 1.25rem;
            font-size: 1.85rem;
            color: var(--jk-teal);
        }
        .jk-lead { color: var(--jk-muted); margin: -.5rem 0 1.5rem; line-height: 1.5; }
        .jk-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        .jk-card {
            background: var(--jk-card);
            border: 1px solid #e4e0d6;
            border-radius: .55rem;
            padding: 1rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .jk-card img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: .35rem;
            margin-bottom: .65rem;
            display: block;
            background: #dde8e6;
        }
        .jk-card h3 { margin: 0 0 .35rem; font-size: 1.05rem; }
        .jk-card p { margin: .35rem 0 0; line-height: 1.45; color: var(--jk-muted); font-size: .92rem; }
        .jk-meta { color: var(--jk-teal); font-size: .88rem; margin-top: .25rem; }
        .jk-empty { color: var(--jk-muted); }
        .jk-list article {
            background: var(--jk-card);
            border-left: 4px solid var(--jk-gold);
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            padding: 1rem 1.1rem;
            margin-bottom: 1rem;
        }
        .jk-list h3 { margin: 0 0 .4rem; }
        .jk-footer {
            text-align: center;
            padding: 1.5rem;
            color: var(--jk-muted);
            font-size: .85rem;
        }
        .jk-footer a { color: var(--jk-ink); }
        .jk-more { display:inline-block; margin-top:.5rem; font-weight:700; color: var(--jk-teal); text-decoration:none; }
        .jk-more:hover { text-decoration:underline; }
    </style>
</head>
<body>
<header class="jk-top">
    <a class="jk-brand" href="{{ $locale === 'sw' ? url('/site/sw') : url('/site') }}">JUKANYE FESTIVAL</a>
    <ul class="jk-nav">
        @foreach($nav as $item)
            <li class="{{ ($item['leaf'] ?? '') === ($currentLeaf ?? '') ? 'active' : '' }}">
                <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
            </li>
        @endforeach
    </ul>
    <div class="jk-langs">
        <a href="{{ $enUrl }}" class="{{ $locale === 'en' ? 'active' : '' }}">English</a>
        <a href="{{ $swUrl }}" class="{{ $locale === 'sw' ? 'active' : '' }}">Kiswahili</a>
    </div>
</header>
<main class="jk-wrap">
    @yield('content')
</main>
<footer class="jk-footer">
    <a href="{{ url('/site') }}">Jukanye Festival</a>
</footer>
</body>
</html>
