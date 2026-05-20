<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 — Access Denied</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh; background:var(--light);">
<div style="text-align:center; max-width:400px; padding:2rem;">
    <i class="fas fa-lock" style="font-size:4rem; color:var(--danger); opacity:0.5; display:block; margin-bottom:1rem;"></i>
    <h2 style="color:var(--primary); margin-bottom:0.5rem;">403 — Access Denied</h2>
    <p style="color:var(--text-muted); margin-bottom:1.5rem;">
        {{ $exception->getMessage() ?: 'You do not have permission to access this page.' }}
    </p>
    <a href="{{ url()->previous() }}" class="btn btn-primary"><i class="fa-arrow-left fas"></i> Go Back</a>
</div>
</body>
</html>