@extends('layouts.app')
@section('title', 'Categories')
@section('page-title', 'All Categories')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Categories</div>
        <div class="page-header-sub">Manage categories across all outlets</div>
    </div>
    <a href="{{ route('superadmin.categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Category
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
        <input type="text" name="search" class="form-control" placeholder="Search categories..."
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
                    <th>Category</th>
                    <th>Outlet</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td>{{ $categories->firstItem() + $loop->index }}</td>
                    <td>
                        <strong>{{ $category->name }}</strong>
                        <div style="font-size:0.8rem; color:var(--text-muted);">{{ $category->slug }}</div>
                    </td>
                    <td>{{ $category->outlet->name ?? '—' }}</td>
                    <td><span class="badge badge-admin">{{ $category->products_count }}</span></td>
                    <td>
                        <span class="badge {{ $category->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('superadmin.categories.edit', $category) }}" class="btn btn-accent btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('superadmin.categories.toggle', $category) }}" method="POST" style="display:inline">
                            @csrf
                            <button class="btn btn-sm {{ $category->is_active ? 'btn-danger' : 'btn-success' }}">
                                <i class="fas fa-{{ $category->is_active ? 'ban' : 'check' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('superadmin.categories.destroy', $category) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Delete this category?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-tags"></i>
                            <h4>No categories found</h4>
                            <p>Select an outlet above or create a new category.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
    <div class="card-footer">{{ $categories->links() }}</div>
    @endif
</div>
@endsection