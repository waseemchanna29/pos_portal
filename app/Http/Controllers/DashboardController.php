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
        $outlet = auth()->user()->outlet;

        $stats = [
            'salesmen' => User::where('role', 'salesman')
                              ->where('outlet_id', auth()->user()->outlet_id)
                              ->count(),
        ];

        return view('admin.dashboard', compact('stats', 'outlet'));
    }

    public function salesman()
    {
        return view('salesman.dashboard');
    }
}