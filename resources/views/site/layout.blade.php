<!DOCTYPE html>

<html lang="{{ $locale === 'sw' ? 'sw' : 'en' }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') — Jukanye Festival</title>

    {!! \App\Support\SiteTheme::fontsLink() !!}

    <link rel="stylesheet" href="{{ \App\Support\SiteTheme::cssUrl() }}?v=8">

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

