@extends('layouts.auth')
@section('title', 'Login')

@section('content')
    <h2>Welcome Back</h2>
    <p>Sign in to your POS System account</p>

    <form action="{{ route('login.post') }}" method="POST" novalidate>
        @csrf

        <div class="form-group">
            <label class="form-label">Email Address <span>*</span></label>
            <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   value="{{ old('email') }}" placeholder="you@example.com" autofocus>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Password <span>*</span></label>
            <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                   placeholder="Enter your password">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg">
            <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
    </form>
@endsection