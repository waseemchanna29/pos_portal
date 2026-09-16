<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function superadmin()
    {
        $stats = [
            'total_outlets'  => \App\Models\Outlet::count(),
            'active_outlets' => \App\Models\Outlet::where('is_active', true)->count(),
            'total_admins'   => \App\Models\User::where('role', 'admin')->count(),
            'unassigned_admins' => \App\Models\User::where('role', 'admin')->whereNull('outlet_id')->count(),
        ];

        $recentOutlets = \App\Models\Outlet::with('admin')
            ->withCount('users')
            ->latest()
            ->take(5)
            ->get();

        return view('superadmin.dashboard', compact('stats', 'recentOutlets'));
    }

    public function admin()
    {
        $outletId = auth()->user()->outlet_id;
        $outlet   = auth()->user()->outlet;

        $stats = [
            'salesmen'   => User::where('role', 'salesman')->where('outlet_id', $outletId)->count(),
            'categories' => \App\Models\Category::where('outlet_id', $outletId)->count(),
            'products'   => \App\Models\Product::where('outlet_id', $outletId)->count(),
            'active_products' => \App\Models\Product::where('outlet_id', $outletId)->where('is_active', true)->count(),
        ];

        $recentProducts = \App\Models\Product::with('category')
            ->where('outlet_id', $outletId)
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'outlet', 'recentProducts'));
    }

    public function salesman()
    {
        $outletId = auth()->user()->outlet_id;
        $today    = now()->toDateString();

        $stats = [
            'pos_today'       => \App\Models\Order::where('outlet_id', $outletId)
                ->where('order_type', 'pos')
                ->where('order_date', $today)
                ->count(),

            'sales_today'     => \App\Models\Order::where('outlet_id', $outletId)
                ->where('order_type', 'pos')
                ->where('order_date', $today)
                ->sum('net_amount'),

            'bookings_active' => \App\Models\Order::where('outlet_id', $outletId)
                ->where('order_type', 'booking')
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),

            'outstanding'     => \App\Models\Order::where('outlet_id', $outletId)
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->whereNotIn('status', ['cancelled'])
                ->sum('balance_amount'),
        ];

        $recentOrders = \App\Models\Order::where('outlet_id', $outletId)
            ->latest()
            ->take(5)
            ->get();

        return view('salesman.dashboard', compact('stats', 'recentOrders'));
    }
}
