<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        $query = Order::query()->with('ticketTier')->orderByDesc('id');
        if (in_array($type, ['donation', 'ticket'], true)) {
            $query->where('type', $type);
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(30)->withQueryString(),
            'type' => $type,
        ]);
    }
}
