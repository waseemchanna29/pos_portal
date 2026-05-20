<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>404 — Page Not Found</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--light);">
<div style="text-align:center; max-width:400px; padding:2rem;">
    <i class="fas fa-search" style="font-size:4rem; color:var(--accent); opacity:0.5; display:block; margin-bottom:1rem;"></i>
    <h2 style="color:var(--primary); margin-bottom:0.5rem;">404 — Page Not Found</h2>
    <p style="color:var(--text-muted); margin-bottom:1.5rem;">The page you are looking for does not exist.</p>
    <a href="{{ route('login') }}" class="btn btn-primary"><i class="fas fa-home"></i> Go Home</a>
</div>
</body>
</html>