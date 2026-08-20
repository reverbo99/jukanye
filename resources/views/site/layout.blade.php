<!DOCTYPE html>

<html lang="{{ $locale === 'sw' ? 'sw' : 'en' }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') — Jukanye Festival</title>
    <link rel="icon" type="image/png" href="{{ \App\Support\SiteTheme::faviconUrl() }}">
    <link rel="apple-touch-icon" href="{{ \App\Support\SiteTheme::faviconUrl() }}">
    {!! \App\Support\SiteTheme::fontsLink() !!}
    <link rel="stylesheet" href="{{ \App\Support\SiteTheme::cssUrl() }}?v=10">

</head>

<body class="jk-app-site">

{!! \App\Support\SiteTheme::appBarHtml($locale, $currentLeaf ?? '') !!}

{!! \App\Support\SiteTheme::topNavHtml($locale, $currentLeaf ?? '') !!}



<main class="jk-cms">

    @yield('content')

</main>



<footer class="jk-cms" style="padding-top:0;text-align:center;color:var(--jk-text-muted);font-size:.85rem">

    <a href="{{ url('/site') }}" style="color:var(--jk-gold)">Jukanye Festival</a>

</footer>



<script>
(function () {
    var nav = document.getElementById('jk-top-nav');
    var toggle = document.getElementById('jk-nav-toggle');
    if (!nav || !toggle) return;

    function setOpen(open) {
        nav.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('jk-nav-open', open);
    }

    toggle.addEventListener('click', function () {
        setOpen(!nav.classList.contains('is-open'));
    });

    nav.addEventListener('click', function (e) {
        var trigger = e.target.closest('.jk-top-nav__trigger');
        if (trigger) {
            e.preventDefault();
            var group = trigger.closest('.jk-top-nav__group');
            if (!group) return;
            var open = group.classList.contains('is-open');
            nav.querySelectorAll('.jk-top-nav__group.is-open').forEach(function (g) {
                if (g !== group) {
                    g.classList.remove('is-open');
                    var btn = g.querySelector('.jk-top-nav__trigger');
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                }
            });
            group.classList.toggle('is-open', !open);
            trigger.setAttribute('aria-expanded', !open ? 'true' : 'false');
            return;
        }
        if (e.target.closest('a')) setOpen(false);
    });

    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });

    window.matchMedia('(min-width: 900px)').addEventListener('change', function (e) {
        if (e.matches) setOpen(false);
    });
})();
</script>

</body>

</html>

