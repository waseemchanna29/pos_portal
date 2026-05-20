@extends('layouts.app')
@section('title', 'Edit Outlet')
@section('page-title', 'Edit Outlet')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Edit Outlet</div>
        <div class="page-header-sub">{{ $outlet->name }}</div>
    </div>
    <a href="{{ route('superadmin.outlets.index') }}" class="btn-outline btn">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div class="card" style="max-width:720px;">
    <div class="card-body">
        <form action="{{ route('superadmin.outlets.update', $outlet) }}" method="POST">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label">Outlet Name <span>*</span></label>
                <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                       value="{{ old('name', $outlet->name) }}" placeholder="e.g. DHA Branch Lahore">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Full Address <span>*</span></label>
                <input type="text" name="address" class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                       value="{{ old('address', $outlet->address) }}" placeholder="Street, Block, Area">
                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">City <span>*</span></label>
                        <select name="city" class="form-select {{ $errors->has('city') ? 'is-invalid' : '' }}">
                            <option value="">— Select City —</option>
                            @foreach(['Karachi','Lahore','Islamabad','Rawalpindi','Faisalabad','Multan','Peshawar','Quetta','Sialkot','Gujranwala','Hyderabad','Sukkur','Bahawalpur','Sargodha','Abbottabad'] as $city)
                                <option value="{{ $city }}" {{ old('city', $outlet->city) == $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                        @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Province <span>*</span></label>
                        <select name="province" class="form-select {{ $errors->has('province') ? 'is-invalid' : '' }}">
                            <option value="">— Select Province —</option>
                            @foreach(['Punjab','Sindh','Khyber Pakhtunkhwa','Balochistan','Gilgit-Baltistan','Azad Kashmir','Islamabad Capital Territory'] as $province)
                                <option value="{{ $province }}" {{ old('province', $outlet->province) == $province ? 'selected' : '' }}>{{ $province }}</option>
                            @endforeach
                        </select>
                        @error('province') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $outlet->phone) }}" placeholder="e.g. 042-35761234">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">NTN</label>
                        <input type="text" name="ntn" class="form-control"
                               value="{{ old('ntn', $outlet->ntn) }}" placeholder="e.g. 1234567-8">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">STRN</label>
                <input type="text" name="strn" class="form-control"
                       value="{{ old('strn', $outlet->strn) }}" placeholder="e.g. 03-01-9999-999-99">
            </div>

            <div style="margin-top:1.5rem; display:flex; gap:0.8rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Outlet</button>
                <a href="{{ route('superadmin.outlets.index') }}" class="btn-outline btn">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection