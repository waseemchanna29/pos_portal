@extends('layouts.app')
@section('title', 'Edit Category')
@section('page-title', 'Edit Category')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Edit Category</div>
        <div class="page-header-sub">{{ $category->name }} — {{ $category->outlet->name ?? '' }}</div>
    </div>
    <a href="{{ route('superadmin.categories.index') }}" class="btn-outline btn">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form action="{{ route('superadmin.categories.update', $category) }}" method="POST">
            @csrf @method('PUT')

            <div class="form-group">
                <label class="form-label">Outlet</label>
                <input type="text" class="form-control" value="{{ $category->outlet->name ?? '—' }}" disabled>
                <span class="form-text">Outlet cannot be changed after creation.</span>
            </div>

            <div class="form-group">
                <label class="form-label">Category Name <span>*</span></label>
                <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                       value="{{ old('name', $category->name) }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div style="display:flex; gap:0.8rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Category</button>
                <a href="{{ route('superadmin.categories.index') }}" class="btn-outline btn">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection