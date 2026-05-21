@extends('layouts.app')
@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Edit Product</div>
        <div class="page-header-sub">{{ $product->name }}</div>
    </div>
    <a href="{{ route('superadmin.products.index') }}" class="btn-outline btn">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div class="card" style="max-width:760px;">
    <div class="card-body">
        <form action="{{ route('superadmin.products.update', $product) }}" method="POST">
            @csrf @method('PUT')

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Outlet</label>
                        <input type="text" class="form-control"
                               value="{{ $product->outlet->name ?? '—' }}" disabled>
                        <span class="form-text">Outlet cannot be changed.</span>
                        <input type="hidden" name="outlet_id" value="{{ $product->outlet_id }}">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Category <span>*</span></label>
                        <select name="category_id" class="form-select {{ $errors->has('category_id') ? 'is-invalid' : '' }}">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Product Name <span>*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name', $product->name) }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">SKU <span>*</span></label>
                        <input type="text" name="sku" class="form-control {{ $errors->has('sku') ? 'is-invalid' : '' }}"
                               value="{{ old('sku', $product->sku) }}">
                        @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Purchase Price (PKR) <span>*</span></label>
                        <input type="number" step="0.01" name="purchase_price" class="form-control"
                               value="{{ old('purchase_price', $product->purchase_price) }}">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Sale Price (PKR) <span>*</span></label>
                        <input type="number" step="0.01" name="sale_price" class="form-control"
                               value="{{ old('sale_price', $product->sale_price) }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Stock Quantity <span>*</span></label>
                        <input type="number" name="stock_quantity" class="form-control"
                               value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Unit <span>*</span></label>
                        <select name="unit" class="form-select">
                            @foreach(['Piece','Pack','Box','Carton','Kg','Gram','Litre','Ml','Dozen','Set','Pair','Bottle','Bag','Sachet'] as $unit)
                                <option value="{{ $unit }}" {{ old('unit', $product->unit) == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div style="display:flex; gap:0.8rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Product</button>
                <a href="{{ route('superadmin.products.index') }}" class="btn-outline btn">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection