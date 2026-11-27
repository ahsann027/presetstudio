<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $status  = (string) $request->query('status', '');
        $keyword = trim((string) $request->query('q', ''));

        $orders = Order::query()
            ->when(in_array($status, [Order::PENDING, Order::PAID, Order::FAILED], true),
                fn ($q) => $q->where('status', $status))
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($q) use ($keyword) {
                    $q->where('order_number', 'like', "%{$keyword}%")
                      ->orWhere('buyer_name', 'like', "%{$keyword}%")
                      ->orWhere('buyer_email', 'like', "%{$keyword}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'paid'    => Order::where('status', Order::PAID)->count(),
            'pending' => Order::where('status', Order::PENDING)->count(),
            'failed'  => Order::where('status', Order::FAILED)->count(),
            'revenue' => (float) Order::where('status', Order::PAID)->sum('amount'),
        ];

        return view('admin.orders.index', compact('orders', 'stats', 'status', 'keyword'));
    }
}
