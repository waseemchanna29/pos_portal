@extends('layouts.app')
@section('title', 'Products')
@section('page-title', 'All Products')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Products</div>
        <div class="page-header-sub">Manage products across all outlets</div>
    </div>
    <a href="{{ route('superadmin.products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Product
    </a>
</div>

<div class="filter-bar">
    <form action="" method="GET" style="display:flex; gap:0.8rem; flex-wrap:wrap;">
        <select name="outlet_id" class="form-select" onchange="this.form.submit()">
            <option value="">— All Outlets —</option>
            @foreach($outlets as $outlet)
                <option value="{{ $outlet->id }}" {{ $outletId == $outlet->id ? 'selected' : '' }}>
                    {{ $outlet->name }}
                </option>
            @endforeach
        </select>
        @if($categories->count())
        <select name="category_id" class="form-select">
            <option value="">— All Categories —</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        @endif
        <input type="text" name="search" class="form-control" placeholder="Search products..."
               value="{{ request('search') }}">
        <button type="submit" class="btn-outline btn"><i class="fas fa-search"></i></button>
    </form>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Outlet</th>
                    <th>Category</th>
                    <th>Sale Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>{{ $products->firstItem() + $loop->index }}</td>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td><code style="font-size:0.8rem;">{{ $product->sku }}</code></td>
                    <td>{{ $product->outlet->name ?? '—' }}</td>
                    <td>{{ $product->category->name ?? '—' }}</td>
                    <td>{{ $product->formatted_sale_price }}</td>
                    <td>
                        <span class="badge badge-{{ str_replace('_', '-', $product->stock_status) }}">
                            {{ $product->stock_quantity }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $product->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('superadmin.products.show', $product) }}" class="btn-outline btn btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('superadmin.products.edit', $product) }}" class="btn btn-accent btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('superadmin.products.toggle', $product) }}" method="POST" style="display:inline">
                            @csrf
                            <button class="btn btn-sm {{ $product->is_active ? 'btn-danger' : 'btn-success' }}">
                                <i class="fas fa-{{ $product->is_active ? 'ban' : 'check' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('superadmin.products.destroy', $product) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Delete this product?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="fas fa-box"></i>
                            <h4>No products found</h4>
                            <p>Select an outlet to filter, or add a new product.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="card-footer">{{ $products->links() }}</div>
    @endif
</div>
@endsection