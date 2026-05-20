@extends('layouts.app')
@section('title', 'Add User')
@section('page-title', 'Add New User')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Add New User</div>
        <div class="page-header-sub">Create an admin or salesman account</div>
    </div>
    <a href="{{ route('superadmin.users.index') }}" class="btn-outline btn">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div class="card" style="max-width:720px;">
    <div class="card-body">
        <form action="{{ route('superadmin.users.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Full Name <span>*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name') }}" placeholder="e.g. Muhammad Ali">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone') }}" placeholder="e.g. 0300-1234567">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address <span>*</span></label>
                <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       value="{{ old('email') }}" placeholder="user@example.com">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Role <span>*</span></label>
                        <select name="role" class="form-select {{ $errors->has('role') ? 'is-invalid' : '' }}">
                            <option value="">— Select Role —</option>
                            <option value="admin"    {{ old('role') == 'admin'    ? 'selected' : '' }}>Admin</option>
                            <option value="salesman" {{ old('role') == 'salesman' ? 'selected' : '' }}>Salesman</option>
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Assign Outlet <span>*</span></label>
                        <select name="outlet_id" class="form-select {{ $errors->has('outlet_id') ? 'is-invalid' : '' }}">
                            <option value="">— Select Outlet —</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}" {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                    {{ $outlet->name }} — {{ $outlet->city }}
                                </option>
                            @endforeach
                        </select>
                        @error('outlet_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Password <span>*</span></label>
                        <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Min. 8 characters">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Confirm Password <span>*</span></label>
                        <input type="password" name="password_confirmation" class="form-control"
                               placeholder="Repeat password">
                    </div>
                </div>
            </div>

            <div style="margin-top:1.5rem; display:flex; gap:0.8rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create User</button>
                <a href="{{ route('superadmin.users.index') }}" class="btn-outline btn">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection