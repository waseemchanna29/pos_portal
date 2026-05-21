@extends('layouts.app')
@section('title', 'Add Category')
@section('page-title', 'Add Category')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Add New Category</div>
        <div class="page-header-sub">Create a category and assign it to an outlet</div>
    </div>
    <a href="{{ route('superadmin.categories.index') }}" class="btn-outline btn">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form action="{{ route('superadmin.categories.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Outlet <span>*</span></label>
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

            <div class="form-group">
                <label class="form-label">Category Name <span>*</span></label>
                <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                       value="{{ old('name') }}" placeholder="e.g. Beverages">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Brief description...">{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', '1') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div style="display:flex; gap:0.8rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Category</button>
                <a href="{{ route('superadmin.categories.index') }}" class="btn-outline btn">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection