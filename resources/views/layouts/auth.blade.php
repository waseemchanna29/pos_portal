<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') — POS System</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="auth-wrapper">

    {{-- Brand Panel --}}
    <div class="auth-brand-panel">
        <div class="auth-brand-logo"><i class="fas fa-cash-register"></i></div>
        <div class="auth-brand-title">POS System</div>
        <div class="auth-brand-sub">Point of Sale Management for your business outlets</div>
        <div class="auth-brand-features">
            <div class="auth-brand-feature"><i class="fas fa-store"></i> Multi-outlet management</div>
            <div class="auth-brand-feature"><i class="fas fa-users"></i> Role-based access control</div>
            <div class="auth-brand-feature"><i class="fas fa-chart-bar"></i> Sales tracking & reports</div>
            <div class="auth-brand-feature"><i class="fas fa-box"></i> Product & category management</div>
        </div>
    </div>

    {{-- Form Panel --}}
    <div class="auth-form-panel">
        <div class="auth-form-box">

            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </div>

</div>
</body>
</html>