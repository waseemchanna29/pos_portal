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
            'total_outlets'  => Outlet::count(),
            'active_outlets' => Outlet::where('is_active', true)->count(),
            'total_admins'   => User::where('role', 'admin')->count(),
            'total_users'    => User::whereIn('role', ['admin', 'salesman'])->count(),
        ];

        $recentOutlets = Outlet::with('createdBy')->latest()->take(5)->get();
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
        return view('salesman.dashboard');
    }
}
