<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nominee;
use App\Models\Post;
use App\Models\ScheduleItem;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'postsCount' => Post::count(),
            'publishedPosts' => Post::published()->count(),
            'nomineesCount' => Nominee::count(),
            'scheduleCount' => ScheduleItem::count(),
        ]);
    }
}
