@extends('layouts.app')
@section('title', 'Add Product')
@section('page-title', 'Add Product')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Add New Product</div>
        <div class="page-header-sub">Fill in the product details below</div>
    </div>
    <a href="{{ route('admin.products.index') }}" class="btn-outline btn">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div class="card" style="max-width:760px;">
    <div class="card-body">
        <form action="{{ route('admin.products.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Product Name <span>*</span></label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               value="{{ old('name') }}" placeholder="e.g. Mineral Water 500ml">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">SKU <span>*</span></label>
                        <input type="text" name="sku" class="form-control {{ $errors->has('sku') ? 'is-invalid' : '' }}"
                               value="{{ old('sku') }}" placeholder="e.g. BEV-MW-500">
                        <span class="form-text">Unique product code / barcode</span>
                        @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Category <span>*</span></label>
                <select name="category_id" class="form-select {{ $errors->has('category_id') ? 'is-invalid' : '' }}">
                    <option value="">— Select Category —</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                          rows="3" placeholder="Product description...">{{ old('description') }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Purchase Price (PKR) <span>*</span></label>
                        <input type="number" step="0.01" name="purchase_price"
                               class="form-control {{ $errors->has('purchase_price') ? 'is-invalid' : '' }}"
                               value="{{ old('purchase_price', '0.00') }}" placeholder="0.00">
                        @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Sale Price (PKR) <span>*</span></label>
                        <input type="number" step="0.01" name="sale_price"
                               class="form-control {{ $errors->has('sale_price') ? 'is-invalid' : '' }}"
                               value="{{ old('sale_price') }}" placeholder="0.00">
                        @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Opening Stock <span>*</span></label>
                        <input type="number" name="stock_quantity"
                               class="form-control {{ $errors->has('stock_quantity') ? 'is-invalid' : '' }}"
                               value="{{ old('stock_quantity', 0) }}" placeholder="0" min="0">
                        @error('stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Unit <span>*</span></label>
                        <select name="unit" class="form-select {{ $errors->has('unit') ? 'is-invalid' : '' }}">
                            @foreach(['Piece','Pack','Box','Carton','Kg','Gram','Litre','Ml','Dozen','Set','Pair','Bottle','Bag','Sachet'] as $unit)
                                <option value="{{ $unit }}" {{ old('unit', 'Piece') == $unit ? 'selected' : '' }}>
                                    {{ $unit }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', '1') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active (available for sale)</label>
                </div>
            </div>

            <div style="display:flex; gap:0.8rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Product</button>
                <a href="{{ route('admin.products.index') }}" class="btn-outline btn">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection