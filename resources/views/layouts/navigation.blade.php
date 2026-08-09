<nav x-data="{ open: false }" class="jk-app-top">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <a href="{{ route('admin.dashboard') }}" class="jk-app-brand shrink-0">
                    JuKaNye
                    <span>Festival CMS</span>
                </a>

                <div class="hidden sm:flex jk-app-nav items-center gap-6">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        {{ __('Admin') }}
                    </a>
                    <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        {{ __('Profile') }}
                    </a>
                    <a href="{{ url('/site') }}">{{ __('Festival site') }}</a>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:gap-3">
                <span class="text-sm" style="color: rgba(245,239,230,0.7)">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="jk-app-btn jk-app-btn-ghost">{{ __('Log Out') }}</button>
                </form>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md" style="color: rgba(245,239,230,0.8)">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t" style="border-color: rgba(223,201,27,0.2)">
        <div class="pt-2 pb-3 space-y-1 px-4 jk-app-nav">
            <a href="{{ route('admin.dashboard') }}" class="block py-2">{{ __('Admin') }}</a>
            <a href="{{ route('profile.edit') }}" class="block py-2">{{ __('Profile') }}</a>
            <a href="{{ url('/site') }}" class="block py-2">{{ __('Festival site') }}</a>
        </div>
        <div class="pt-3 pb-4 border-t px-4" style="border-color: rgba(223,201,27,0.2)">
            <div class="font-medium text-base" style="color:#f5efe6">{{ Auth::user()->name }}</div>
            <div class="font-medium text-sm" style="color:rgba(245,239,230,0.55)">{{ Auth::user()->email }}</div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="jk-app-btn jk-app-btn-ghost">{{ __('Log Out') }}</button>
            </form>
        </div>
    </div>
</nav>
