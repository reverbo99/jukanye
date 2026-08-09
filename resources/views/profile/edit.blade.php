<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight" style="color:#14221f; font-family:'DM Serif Display',Georgia,serif;">
            {{ __('Profile') }}
        </h2>
        <p class="mt-1 text-sm" style="color:#6b7874">{{ __('Manage your JuKaNye CMS account.') }}</p>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="jk-app-card max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="jk-app-card max-w-xl">
                @include('profile.partials.update-password-form')
            </div>

            <div class="jk-app-card max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
