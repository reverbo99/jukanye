<x-guest-layout>
    <h1>{{ __('Forgot password') }}</h1>
    <p class="jk-auth-lead">{{ __('Enter your email and we will send a reset link.') }}</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6 gap-3 flex-wrap">
            <a class="underline text-sm rounded-md" href="{{ route('login') }}">
                {{ __('Back to login') }}
            </a>

            <x-primary-button class="jk-auth-submit ms-auto">
                {{ __('Email reset link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
