@extends('layouts.app')
@section('title', 'Add Product')
@section('page-title', 'Add Product')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Add New Product</div>
        <div class="page-header-sub">Assign to any outlet</div>
    </div>
    <a href="{{ route('superadmin.products.index') }}" class="btn-outline btn">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<div class="card" style="max-width:760px;">
    <div class="card-body">
        <form action="{{ route('superadmin.products.store') }}" method="POST" id="productForm">
            @csrf

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Outlet <span>*</span></label>
                        <select name="outlet_id" id="outlet_id"
                                class="form-select {{ $errors->has('outlet_id') ? 'is-invalid' : '' }}">
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
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Category <span>*</span></label>
                        <select name="category_id" id="category_id"
                                class="form-select {{ $errors->has('category_id') ? 'is-invalid' : '' }}">
                            <option value="">— Select Outlet First —</option>
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
                               value="{{ old('name') }}" placeholder="e.g. Mineral Water 500ml">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">SKU <span>*</span></label>
                        <input type="text" name="sku" class="form-control {{ $errors->has('sku') ? 'is-invalid' : '' }}"
                               value="{{ old('sku') }}" placeholder="e.g. BEV-MW-500">
                        @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Purchase Price (PKR) <span>*</span></label>
                        <input type="number" step="0.01" name="purchase_price"
                               class="form-control {{ $errors->has('purchase_price') ? 'is-invalid' : '' }}"
                               value="{{ old('purchase_price', '0.00') }}">
                        @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Sale Price (PKR) <span>*</span></label>
                        <input type="number" step="0.01" name="sale_price"
                               class="form-control {{ $errors->has('sale_price') ? 'is-invalid' : '' }}"
                               value="{{ old('sale_price') }}">
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
                               value="{{ old('stock_quantity', 0) }}" min="0">
                        @error('stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">Unit <span>*</span></label>
                        <select name="unit" class="form-select">
                            @foreach(['Piece','Pack','Box','Carton','Kg','Gram','Litre','Ml','Dozen','Set','Pair','Bottle','Bag','Sachet'] as $unit)
                                <option value="{{ $unit }}" {{ old('unit','Piece') == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active','1') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div style="display:flex; gap:0.8rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Product</button>
                <a href="{{ route('superadmin.products.index') }}" class="btn-outline btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Dynamically load categories when outlet changes
document.getElementById('outlet_id').addEventListener('change', function () {
    const outletId  = this.value;
    const catSelect = document.getElementById('category_id');

    catSelect.innerHTML = '<option value="">— Loading... —</option>';

    if (!outletId) {
        catSelect.innerHTML = '<option value="">— Select Outlet First —</option>';
        return;
    }

    fetch(`/superadmin/outlets/${outletId}/categories`)
        .then(r => r.json())
        .then(data => {
            catSelect.innerHTML = '<option value="">— Select Category —</option>';
            data.forEach(cat => {
                catSelect.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
            });
        })
        .catch(() => {
            catSelect.innerHTML = '<option value="">— Failed to load —</option>';
        });
});
</script>
@endsection