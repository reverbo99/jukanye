<aside class="admin-sidebar">
    <div class="admin-brand">JUKANYE ADMIN</div>
    <nav class="admin-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>

        <div class="nav-section">App &amp; website content</div>
        <a href="{{ route('admin.home-sections.index') }}" class="{{ request()->routeIs('admin.home-sections.*') ? 'active' : '' }}">Home sections</a>
        <a href="{{ route('admin.site-media.index') }}" class="{{ request()->routeIs('admin.site-media.*') ? 'active' : '' }}">Media &amp; Sliders</a>
        <a href="{{ route('admin.posts.index') }}" class="{{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">News / Habari</a>
        <a href="{{ route('admin.team.index') }}" class="{{ request()->routeIs('admin.team.*') ? 'active' : '' }}">About / Team</a>
        <a href="{{ route('admin.schedule.index') }}" class="{{ request()->routeIs('admin.schedule.*') ? 'active' : '' }}">Programme / Schedule</a>
        <a href="{{ route('admin.people.index') }}" class="{{ request()->routeIs('admin.people.*') ? 'active' : '' }}">People (Speakers · Artists · Heroes · Exhibitions · Friends)</a>
        <a href="{{ route('admin.tours.index') }}" class="{{ request()->routeIs('admin.tours.*') ? 'active' : '' }}">Tourism</a>
        <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Merchandise</a>
        <a href="{{ route('admin.ticket-tiers.index') }}" class="{{ request()->routeIs('admin.ticket-tiers.*') ? 'active' : '' }}">Tickets</a>
        <a href="{{ route('admin.award-categories.index') }}" class="{{ request()->routeIs('admin.award-categories.*') ? 'active' : '' }}">Award Categories</a>
        <a href="{{ route('admin.nominees.index') }}" class="{{ request()->routeIs('admin.nominees.*') ? 'active' : '' }}">Awards / Nominees</a>
        <a href="{{ route('admin.sponsors.index') }}" class="{{ request()->routeIs('admin.sponsors.*') ? 'active' : '' }}">Sponsors</a>
        <a href="{{ route('admin.map-places.index') }}" class="{{ request()->routeIs('admin.map-places.*') ? 'active' : '' }}">Festival Map</a>
        <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">Orders / Payments</a>

        <div class="nav-section">Inbox &amp; settings</div>
        <a href="{{ route('admin.submissions.index', ['form' => 'register']) }}" class="{{ request()->routeIs('admin.submissions.*') && request('form','register')==='register' ? 'active' : '' }}">Register inbox</a>
        <a href="{{ route('admin.submissions.index', ['form' => 'contact']) }}" class="{{ request()->routeIs('admin.submissions.*') && request('form')==='contact' ? 'active' : '' }}">Contact inbox</a>
        <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">Site settings (Donate · About · Contact)</a>
        <a href="{{ route('admin.artisan.index') }}" class="{{ request()->routeIs('admin.artisan.*') ? 'active' : '' }}">Artisan Commands</a>
    </nav>
</aside>
