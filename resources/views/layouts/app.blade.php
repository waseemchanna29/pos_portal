<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — POS System</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="dashboard-wrapper">

    {{-- Sidebar --}}
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon"><i class="fas fa-cash-register"></i></div>
            <div>
                <div class="sidebar-brand-text">POS System</div>
                <div class="sidebar-brand-sub">
                    @if(auth()->user()->outlet)
                        {{ auth()->user()->outlet->name }}
                    @else
                        All Outlets
                    @endif
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">

            {{-- Super Admin Navigation --}}
            @if(auth()->user()->isSuperAdmin())
                <div class="sidebar-section-label">Super Admin</div>
                <a href="{{ route('superadmin.dashboard') }}"
                   class="sidebar-nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <div class="sidebar-section-label">Management</div>
                <a href="{{ route('superadmin.outlets.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('superadmin.outlets.*') ? 'active' : '' }}">
                    <i class="fas fa-store"></i> Outlets
                </a>
                <a href="{{ route('superadmin.users.index') }}"
                   class="sidebar-nav-link {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Users
                </a>
            @endif

            {{-- Admin Navigation --}}
            @if(auth()->user()->isAdmin())
                <div class="sidebar-section-label">Admin Panel</div>
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            @endif

            {{-- Salesman Navigation --}}
            @if(auth()->user()->isSalesman())
                <div class="sidebar-section-label">My Panel</div>
                <a href="{{ route('salesman.dashboard') }}"
                   class="sidebar-nav-link {{ request()->routeIs('salesman.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            @endif

        </nav>

        <div class="sidebar-footer">POS System &copy; {{ date('Y') }}</div>
    </aside>

    {{-- Topbar --}}
    <header class="topbar">
        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        <div class="topbar-right">
            <div class="topbar-user">
                <div class="topbar-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="topbar-user-info">
                    <div class="topbar-user-name">{{ auth()->user()->name }}</div>
                    <div class="topbar-user-role">{{ auth()->user()->role_label }}</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-outline btn btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="main-content">

        @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <div>
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin-top:0.4rem; padding-left:1.2rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

</div>
</body>
</html>