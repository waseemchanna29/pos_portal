@extends('layouts.app')
@section('title', 'Product Details')
@section('page-title', 'Product Details')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">{{ $product->name }}</div>
        <div class="page-header-sub">SKU: {{ $product->sku }}</div>
    </div>
    <div style="display:flex; gap:0.6rem;">
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-accent">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('admin.products.index') }}" class="btn-outline btn">
            <i class="fa-arrow-left fas"></i> Back
        </a>
    </div>
</div>

<div class="product-hero">
    <div>
        <div class="product-hero-name">{{ $product->name }}</div>
        <div class="product-hero-sku">SKU: {{ $product->sku }} &nbsp;|&nbsp; {{ $product->category->name ?? '—' }}</div>
    </div>
    <div class="product-hero-stats">
        <div>
            <div class="product-hero-stat-value">{{ $product->formatted_sale_price }}</div>
            <div class="product-hero-stat-label">Sale Price</div>
        </div>
        <div>
            <div class="product-hero-stat-value">{{ $product->formatted_purchase_price }}</div>
            <div class="product-hero-stat-label">Purchase Price</div>
        </div>
        <div>
            <div class="product-hero-stat-value">{{ $product->stock_quantity }}</div>
            <div class="product-hero-stat-label">In Stock ({{ $product->unit }})</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-info-circle"></i> Product Information</div>
        <span class="badge {{ $product->is_active ? 'badge-active' : 'badge-inactive' }}">
            {{ $product->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div><div class="info-item-label">Product Name</div><div class="info-item-value">{{ $product->name }}</div></div>
            <div><div class="info-item-label">SKU</div><div class="info-item-value"><code>{{ $product->sku }}</code></div></div>
            <div><div class="info-item-label">Category</div><div class="info-item-value">{{ $product->category->name ?? '—' }}</div></div>
            <div><div class="info-item-label">Unit</div><div class="info-item-value">{{ $product->unit }}</div></div>
            <div><div class="info-item-label">Sale Price</div><div class="info-item-value">{{ $product->formatted_sale_price }}</div></div>
            <div><div class="info-item-label">Purchase Price</div><div class="info-item-value">{{ $product->formatted_purchase_price }}</div></div>
            <div>
                <div class="info-item-label">Stock Status</div>
                <div class="info-item-value">
                    <span class="badge badge-{{ str_replace('_', '-', $product->stock_status) }}">
                        {{ $product->stock_quantity }} {{ $product->unit }}
                        @if($product->stock_status === 'low_stock') — Low Stock @endif
                        @if($product->stock_status === 'out_of_stock') — Out of Stock @endif
                    </span>
                </div>
            </div>
            <div><div class="info-item-label">Added By</div><div class="info-item-value">{{ $product->createdBy->name ?? '—' }}</div></div>
        </div>
        @if($product->description)
        <div class="form-group" style="margin-top:0.5rem;">
            <div class="info-item-label">Description</div>
            <div class="info-item-value">{{ $product->description }}</div>
        </div>
        @endif
    </div>
</div>
@endsection