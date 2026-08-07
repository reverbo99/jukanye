<aside class="admin-sidebar">
    <div class="admin-brand">JUKANYE ADMIN</div>
    <nav class="admin-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>

        <div class="nav-section">Content</div>
        <a href="{{ route('admin.posts.index') }}" class="{{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">Posts / Habari</a>
        <a href="{{ route('admin.nominees.index') }}" class="{{ request()->routeIs('admin.nominees.*') ? 'active' : '' }}">Award Nominees</a>
        <a href="{{ route('admin.award-categories.index') }}" class="{{ request()->routeIs('admin.award-categories.*') ? 'active' : '' }}">Award Categories</a>
        <a href="{{ route('admin.schedule.index') }}" class="{{ request()->routeIs('admin.schedule.*') ? 'active' : '' }}">Schedule</a>
        <a href="{{ route('admin.home-sections.index') }}" class="{{ request()->routeIs('admin.home-sections.*') ? 'active' : '' }}">Home sections</a>
        <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Products</a>
        <a href="{{ route('admin.sponsors.index') }}" class="{{ request()->routeIs('admin.sponsors.*') ? 'active' : '' }}">Sponsors</a>
        <a href="{{ route('admin.team.index') }}" class="{{ request()->routeIs('admin.team.*') ? 'active' : '' }}">Team / About</a>
        <a href="{{ route('admin.people.index') }}" class="{{ request()->routeIs('admin.people.*') ? 'active' : '' }}">People</a>
        <a href="{{ route('admin.tours.index') }}" class="{{ request()->routeIs('admin.tours.*') ? 'active' : '' }}">Tours</a>
        <a href="{{ route('admin.ticket-tiers.index') }}" class="{{ request()->routeIs('admin.ticket-tiers.*') ? 'active' : '' }}">Ticket tiers</a>
        <a href="{{ route('admin.map-places.index') }}" class="{{ request()->routeIs('admin.map-places.*') ? 'active' : '' }}">Map places</a>

        <div class="nav-section">Inbox & settings</div>
        <a href="{{ route('admin.submissions.index', ['form' => 'register']) }}" class="{{ request()->routeIs('admin.submissions.*') && request('form','register')==='register' ? 'active' : '' }}">Register inbox</a>
        <a href="{{ route('admin.submissions.index', ['form' => 'contact']) }}" class="{{ request()->routeIs('admin.submissions.*') && request('form')==='contact' ? 'active' : '' }}">Contacts inbox</a>
        <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">Site settings</a>
    </nav>
</aside>
