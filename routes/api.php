<?php

use App\Http\Controllers\Api\V1\AwardCategoryController;
use App\Http\Controllers\Api\V1\HomeSectionController;
use App\Http\Controllers\Api\V1\MapPlaceController;
use App\Http\Controllers\Api\V1\NomineeController;
use App\Http\Controllers\Api\V1\PersonController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ScheduleController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\SponsorController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\TicketTierController;
use App\Http\Controllers\Api\V1\TourController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{slug}', [PostController::class, 'show']);
    Route::get('award-categories', [AwardCategoryController::class, 'index']);
    Route::get('nominees', [NomineeController::class, 'index']);
    Route::get('schedule', [ScheduleController::class, 'index']);

    Route::get('settings', [SettingController::class, 'show']);
    Route::get('sponsors', [SponsorController::class, 'index']);
    Route::get('team', [TeamController::class, 'index']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('home-sections', [HomeSectionController::class, 'index']);
    Route::get('people', [PersonController::class, 'index']);
    Route::get('people/{id}', [PersonController::class, 'show'])->whereNumber('id');
    Route::get('tours', [TourController::class, 'index']);
    Route::get('ticket-tiers', [TicketTierController::class, 'index']);
    Route::get('map-places', [MapPlaceController::class, 'index']);
});
