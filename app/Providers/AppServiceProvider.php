<?php

namespace App\Providers;

use App\Support\Bilingual;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Included partials don't leak @php locals to parent forms.
        View::composer('admin.*', function ($view): void {
            $view->with([
                'writeLocale' => Bilingual::writeLocale(),
                'translateLocale' => Bilingual::translateLocale(),
            ]);
        });
    }
}
