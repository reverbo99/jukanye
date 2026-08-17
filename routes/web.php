<?php

use App\Http\Controllers\Admin\ArtisanCommandController;
use App\Http\Controllers\Admin\AwardCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FormSubmissionController;
use App\Http\Controllers\Admin\HomeSectionController;
use App\Http\Controllers\Admin\MapPlaceController;
use App\Http\Controllers\Admin\NomineeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PersonController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ScheduleItemController;
use App\Http\Controllers\Admin\SiteMediaItemController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\TicketTierController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\LegacySiteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Old Jukanye UI → Laravel (/site)
|--------------------------------------------------------------------------
*/

$legacyPages = [
    'home' => '',
    'homeb' => 'Homeb',
    'about' => 'About-Us',
    'awards' => 'Award-Nominees',
    'schedule' => 'Schedule',
    'products' => 'Event-Products',
    'donate' => 'Donate',
    'sponsors' => 'Sponsors',
    'register' => 'Register',
    'download' => 'Download',
    'contacts' => 'Contacts',
    'unlisted' => 'Unlisted',
    // App-sidebar parity pages (CMS-backed via LegacySiteController → CmsPublicController)
    'news' => 'News',
    'speakers' => 'Speakers',
    'artists' => 'Artists',
    'heroes' => 'Heroes',
    'exhibitions' => 'Exhibitions',
    'friends' => 'Friends',
    'tourism' => 'Tourism',
    'tickets' => 'Tickets',
    'map' => 'Festival-Map',
];

$legacySwAliases = [
    'home' => '',
    'homeb' => 'Mwanzo',
    'about' => 'Shughuli-Zetu',
    'awards' => 'Waliopendekezwa-kupewa-Tuzo',
    'schedule' => 'Schedule',
    'products' => 'Bidhaa-za-Tamasha',
    'donate' => 'Changia',
    'sponsors' => 'Wadhamini',
    'register' => 'Jisajiri',
    'download' => 'Pakua',
    'contacts' => 'Mawasiliano',
    'unlisted' => 'Unlisted',
    'news' => 'News',
    'speakers' => 'Speakers',
    'artists' => 'Artists',
    'heroes' => 'Heroes',
    'exhibitions' => 'Exhibitions',
    'friends' => 'Friends',
    'tourism' => 'Tourism',
    'tickets' => 'Tickets',
    'map' => 'Festival-Map',
];

Route::redirect('/', '/site');

Route::get('/site/checkout/ticket', [\App\Http\Controllers\CmsPublicController::class, 'buyTicket'])
    ->name('site.checkout.ticket');

foreach ($legacyPages as $name => $alias) {
    $uri = $alias === '' ? '/site' : '/site/'.$alias;
    Route::match(['GET', 'HEAD', 'POST'], $uri, [LegacySiteController::class, 'handle'])
        ->defaults('path', $alias)
        ->withoutMiddleware([ValidateCsrfToken::class])
        ->name('legacy.'.$name);
}

foreach ($legacySwAliases as $name => $alias) {
    $uri = $alias === '' ? '/site/sw' : '/site/sw/'.$alias;
    $path = $alias === '' ? 'sw' : 'sw/'.$alias;
    Route::match(['GET', 'HEAD', 'POST'], $uri, [LegacySiteController::class, 'handle'])
        ->defaults('path', $path)
        ->withoutMiddleware([ValidateCsrfToken::class])
        ->name('legacy.sw.'.$name);
}

Route::match(['GET', 'HEAD', 'POST'], '/site/{path}', [LegacySiteController::class, 'handle'])
    ->where('path', '.*')
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('legacy.catch');

foreach ($legacyPages as $alias) {
    if ($alias === '') {
        continue;
    }
    Route::redirect('/'.$alias, '/site/'.$alias, 301);
}

foreach ($legacySwAliases as $alias) {
    if ($alias === '') {
        continue;
    }
    Route::redirect('/sw/'.$alias, '/site/sw/'.$alias, 301);
}

/*
|--------------------------------------------------------------------------
| Admin CMS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', \App\Http\Middleware\EnsureAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('posts', PostController::class)->except(['show']);
    Route::resource('award-categories', AwardCategoryController::class)->except(['show']);
    Route::resource('nominees', NomineeController::class)->except(['show']);
    Route::resource('schedule', ScheduleItemController::class)
        ->parameters(['schedule' => 'scheduleItem'])
        ->except(['show']);

    Route::resource('sponsors', SponsorController::class)->except(['show']);
    Route::resource('team', TeamMemberController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('home-sections', HomeSectionController::class)->except(['show']);
    Route::resource('site-media', SiteMediaItemController::class)
        ->parameters(['site-media' => 'siteMediaItem'])
        ->except(['show']);
    Route::resource('people', PersonController::class)->except(['show']);
    Route::resource('tours', TourController::class)->except(['show']);
    Route::resource('ticket-tiers', TicketTierController::class)->except(['show']);
    Route::resource('map-places', MapPlaceController::class)->except(['show']);
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');

    Route::get('settings', [SiteSettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SiteSettingController::class, 'update'])->name('settings.update');

    Route::get('artisan', [ArtisanCommandController::class, 'index'])->name('artisan.index');
    Route::post('artisan/migrate', [ArtisanCommandController::class, 'migrate'])->name('artisan.migrate');
    Route::post('artisan/migrate-force', [ArtisanCommandController::class, 'migrateForce'])->name('artisan.migrate-force');

    Route::get('submissions', [FormSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('submissions/{submission}', [FormSubmissionController::class, 'show'])->name('submissions.show');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user && $user->is_admin) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('legacy.home');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
