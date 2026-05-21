@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-tags"></i></div>
        <div>
            <div class="stat-value">{{ $stats['categories'] }}</div>
            <div class="stat-label">Categories</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon accent"><i class="fas fa-box"></i></div>
        <div>
            <div class="stat-value">{{ $stats['products'] }}</div>
            <div class="stat-label">Total Products</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-value">{{ $stats['active_products'] }}</div>
            <div class="stat-label">Active Products</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-user-tie"></i></div>
        <div>
            <div class="stat-value">{{ $stats['salesmen'] }}</div>
            <div class="stat-label">Salesmen</div>
        </div>
    </div>
</div>

@if($outlet)
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-store"></i> My Outlet</div>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div><div class="info-item-label">Outlet Name</div><div class="info-item-value">{{ $outlet->name }}</div></div>
            <div><div class="info-item-label">City</div><div class="info-item-value">{{ $outlet->city }}</div></div>
            <div><div class="info-item-label">Province</div><div class="info-item-value">{{ $outlet->province }}</div></div>
            <div><div class="info-item-label">Phone</div><div class="info-item-value">{{ $outlet->phone ?? '—' }}</div></div>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-box"></i> Recent Products</div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>Product</th><th>Category</th><th>Sale Price</th><th>Stock</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($recentProducts as $product)
                <tr>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td>{{ $product->category->name ?? '—' }}</td>
                    <td>{{ $product->formatted_sale_price }}</td>
                    <td>
                        <span class="badge badge-{{ str_replace('_', '-', $product->stock_status) }}">
                            {{ $product->stock_quantity }} {{ $product->unit }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $product->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center; color:var(--text-muted); padding:2rem;">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection