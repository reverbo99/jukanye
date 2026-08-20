<x-app-layout>
    <x-slot name="header">
        <h2 class="jk-profile-title">
            {{ __('Profile') }}
        </h2>
        <p class="jk-profile-lead">{{ __('Manage your JuKaNye CMS account.') }}</p>
    </x-slot>

    <div class="jk-profile-page">
        <div class="jk-profile-column space-y-5">
            <div class="jk-app-card">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="jk-app-card">
                @include('profile.partials.update-password-form')
            </div>

            <div class="jk-app-card jk-app-card--danger">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
