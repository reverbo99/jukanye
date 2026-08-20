<section class="jk-profile-section">
    <header class="jk-profile-section__head">
        <h2 class="jk-profile-section__title">
            {{ __('Profile Information') }}
        </h2>

        <p class="jk-profile-section__desc">
            {{ __("Update your account's profile information, photo, and contact details.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="jk-profile-form">
        @csrf
        @method('patch')

        <div class="jk-profile-avatar">
            <div class="jk-profile-avatar__preview">
                @if ($user->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="jk-profile-avatar__img">
                @else
                    <div class="jk-profile-avatar__fallback" aria-hidden="true">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="jk-profile-avatar__fields">
                <x-input-label for="avatar" :value="__('Profile photo')" />
                <input
                    id="avatar"
                    name="avatar"
                    type="file"
                    accept="image/*"
                    class="jk-profile-file"
                />
                <p class="jk-profile-hint">{{ __('JPG, PNG, or WebP. Square photos work best.') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div class="jk-profile-field">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="jk-profile-field">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="jk-profile-verify">
                    <p class="text-sm" style="color: var(--jk-ink)">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" type="submit" class="jk-profile-link">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm" style="color: #1b5e20">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="jk-profile-field">
            <x-input-label for="phone" :value="__('Phone')" />
            <x-text-input id="phone" name="phone" type="text" class="block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div class="jk-profile-actions">
            <x-primary-button class="jk-app-btn">{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="jk-profile-saved"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
