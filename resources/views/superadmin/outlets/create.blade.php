{{-- Project path: resources/views/superadmin/outlets/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Add Shop Owner')
@section('page-title', 'Add New Shop Owner')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Add New Shop Owner</div>
        <div class="page-header-sub">Creates the outlet, the admin login, and the subscription plan together</div>
    </div>
    <a href="{{ route('superadmin.outlets.index') }}" class="btn-outline btn">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('superadmin.outlets.store') }}" method="POST">
    @csrf

    {{-- Outlet Details --}}
    <div class="card" style="max-width:720px; margin-bottom:1.5rem;">
        <div class="card-header">
            <div class="card-header-title"><i class="fas fa-store"></i> Shop / Outlet Details</div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Outlet Name <span>*</span></label>
                <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                       value="{{ old('name') }}" placeholder="e.g. DHA Branch Lahore">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Full Address <span>*</span></label>
                <input type="text" name="address" class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                       value="{{ old('address') }}" placeholder="Street, Block, Area">
                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">City <span>*</span></label>
                        <select name="city" class="form-select {{ $errors->has('city') ? 'is-invalid' : '' }}">
                            <option value="">— Select City —</option>
                            @foreach(['Karachi','Lahore','Larkana','Islamabad','Rawalpindi','Faisalabad','Multan','Peshawar','Quetta','Sialkot','Gujranwala','Hyderabad','Sukkur','Bahawalpur','Sargodha','Abbottabad'] as $city)
                                <option value="{{ $city }}" {{ old('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
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
                                <option value="{{ $province }}" {{ old('province') == $province ? 'selected' : '' }}>{{ $province }}</option>
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
                        <input type="text" name="phone" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                               value="{{ old('phone') }}" placeholder="e.g. 042-35761234">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">NTN <span class="form-text" style="display:inline">(National Tax Number)</span></label>
                        <input type="text" name="ntn" class="form-control {{ $errors->has('ntn') ? 'is-invalid' : '' }}"
                               value="{{ old('ntn') }}" placeholder="e.g. 1234567-8">
                        @error('ntn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">STRN <span class="form-text" style="display:inline">(Sales Tax Registration Number)</span></label>
                <input type="text" name="strn" class="form-control {{ $errors->has('strn') ? 'is-invalid' : '' }}"
                       value="{{ old('strn') }}" placeholder="e.g. 03-01-9999-999-99">
                @error('strn') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    {{-- Admin Login --}}
    <div class="card" style="max-width:720px; margin-bottom:1.5rem;">
        <div class="card-header">
            <div class="card-header-title"><i class="fas fa-user-shield"></i> Shop Owner Login</div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Owner Name <span>*</span></label>
                <input type="text" name="admin_name" class="form-control {{ $errors->has('admin_name') ? 'is-invalid' : '' }}"
                       value="{{ old('admin_name') }}" placeholder="e.g. Ahmed Raza">
                @error('admin_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Email <span>*</span></label>
                        <input type="email" name="admin_email" class="form-control {{ $errors->has('admin_email') ? 'is-invalid' : '' }}"
                               value="{{ old('admin_email') }}" placeholder="owner@example.com">
                        @error('admin_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="admin_phone" class="form-control {{ $errors->has('admin_phone') ? 'is-invalid' : '' }}"
                               value="{{ old('admin_phone') }}" placeholder="e.g. 0300-1234567">
                        @error('admin_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Password <span>*</span></label>
                        <input type="password" name="admin_password" class="form-control {{ $errors->has('admin_password') ? 'is-invalid' : '' }}"
                               placeholder="Min. 8 characters">
                        @error('admin_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Confirm Password <span>*</span></label>
                        <input type="password" name="admin_password_confirmation" class="form-control"
                               placeholder="Re-enter password">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Subscription Plan --}}
    <div class="card" style="max-width:720px; margin-bottom:1.5rem;">
        <div class="card-header">
            <div class="card-header-title"><i class="fas fa-crown"></i> Subscription Plan</div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Plan Type <span>*</span></label>
                <select name="plan_type" id="planType" class="form-select {{ $errors->has('plan_type') ? 'is-invalid' : '' }}">
                    <option value="trial" {{ old('plan_type', 'trial') == 'trial' ? 'selected' : '' }}>Trial</option>
                    <option value="paid" {{ old('plan_type') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
                @error('plan_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group" id="trialEndsGroup">
                <label class="form-label">Trial Ends On <span>*</span></label>
                <input type="date" name="trial_ends_at" class="form-control {{ $errors->has('trial_ends_at') ? 'is-invalid' : '' }}"
                       value="{{ old('trial_ends_at') }}">
                <span class="form-text">Defaults to 14 days from today if left blank.</span>
                @error('trial_ends_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Monthly Amount (PKR)</label>
                <input type="number" step="0.01" min="0" name="monthly_amount"
                       class="form-control {{ $errors->has('monthly_amount') ? 'is-invalid' : '' }}"
                       value="{{ old('monthly_amount') }}" placeholder="e.g. 5000">
                @error('monthly_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Max Salesman Accounts</label>
                        <input type="number" min="0" name="max_salesmen"
                               class="form-control {{ $errors->has('max_salesmen') ? 'is-invalid' : '' }}"
                               value="{{ old('max_salesmen') }}" placeholder="Leave blank for unlimited">
                        @error('max_salesmen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Max Booker Accounts</label>
                        <input type="number" min="0" name="max_bookers"
                               class="form-control {{ $errors->has('max_bookers') ? 'is-invalid' : '' }}"
                               value="{{ old('max_bookers') }}" placeholder="Leave blank for unlimited">
                        @error('max_bookers') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:1.5rem; display:flex; gap:0.8rem; max-width:720px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Shop Owner</button>
        <a href="{{ route('superadmin.outlets.index') }}" class="btn-outline btn">Cancel</a>
    </div>
</form>

<script>
// Trial-ends-on field only makes sense for a trial plan
(function () {
    var planSelect = document.getElementById('planType');
    var trialGroup = document.getElementById('trialEndsGroup');

    function toggleTrialField() {
        trialGroup.style.display = planSelect.value === 'trial' ? '' : 'none';
    }

    planSelect.addEventListener('change', toggleTrialField);
    toggleTrialField();
})();
</script>
@endsection