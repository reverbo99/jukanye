<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StubController extends Controller
{
    public function show(Request $request): View
    {
        $titles = [
            'admin.home.stub' => 'Home / Homeb',
            'admin.about.stub' => 'About Us',
            'admin.products.stub' => 'Event Products',
            'admin.donate.stub' => 'Donate',
            'admin.sponsors.stub' => 'Sponsors',
            'admin.register.stub' => 'Register submissions',
            'admin.download.stub' => 'Download',
            'admin.contacts.stub' => 'Contacts submissions',
            'admin.settings.stub' => 'Site settings',
        ];

        $route = $request->route()?->getName() ?? '';

        return view('admin.stub', [
            'title' => $titles[$route] ?? 'Coming soon',
        ]);
    }
}
