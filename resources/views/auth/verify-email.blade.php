<x-guest-layout>
    <h1>{{ __('Verify email') }}</h1>
    <p class="jk-auth-lead">{{ __('Thanks for signing up! Please verify your email address using the link we sent you.') }}</p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm" style="color: var(--jk-teal)">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between gap-3 flex-wrap">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="jk-auth-submit">
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="underline text-sm rounded-md">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
