@extends('layouts.app')
@section('title', 'Products')
@section('page-title', 'Products')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Products</div>
        <div class="page-header-sub">Manage products for your outlet</div>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Product
    </a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Sale Price</th>
                    <th>Stock</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>{{ $products->firstItem() + $loop->index }}</td>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td><code style="font-size:0.8rem; color:var(--text-muted);">{{ $product->sku }}</code></td>
                    <td>{{ $product->category->name ?? '—' }}</td>
                    <td><strong>{{ $product->formatted_sale_price }}</strong></td>
                    <td>
                        <span class="badge badge-{{ str_replace('_', '-', $product->stock_status) }}">
                            {{ $product->stock_quantity }}
                        </span>
                    </td>
                    <td>{{ $product->unit }}</td>
                    <td>
                        <span class="badge {{ $product->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.products.show', $product) }}" class="btn-outline btn btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-accent btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.products.toggle', $product) }}" method="POST" style="display:inline">
                            @csrf
                            <button class="btn btn-sm {{ $product->is_active ? 'btn-danger' : 'btn-success' }}">
                                <i class="fas fa-{{ $product->is_active ? 'ban' : 'check' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display:inline"
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
                            <h4>No products yet</h4>
                            <p>Add your first product to get started.</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Product
                            </a>
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