@extends('layouts.app')
@section('title', 'Edit Salesman')
@section('page-title', 'Edit Salesman')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Edit Salesman</div>
        <div class="page-header-sub">{{ $salesman->name }}</div>
    </div>
    <a href="{{ route('admin.salesmen.index') }}" class="btn-outline btn">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form action="{{ route('admin.salesmen.update', $salesman) }}" method="POST">
            @csrf @method('PUT')

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Full Name <span>*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name', $salesman->name) }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $salesman->phone) }}" placeholder="0300-1234567">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address <span>*</span></label>
                <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       value="{{ old('email', $salesman->email) }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">New Password <span class="form-text" style="display:inline">(leave blank to keep)</span></label>
                        <input type="password" name="password"
                               class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Min. 8 characters">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control"
                               placeholder="Repeat new password">
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:0.8rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Salesman</button>
                <a href="{{ route('admin.salesmen.index') }}" class="btn-outline btn">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection