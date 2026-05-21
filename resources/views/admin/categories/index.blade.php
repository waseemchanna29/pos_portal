@extends('layouts.app')
@section('title', 'Categories')
@section('page-title', 'Categories')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Categories</div>
        <div class="page-header-sub">Manage product categories for your outlet</div>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Category
    </a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td>{{ $categories->firstItem() + $loop->index }}</td>
                    <td><strong>{{ $category->name }}</strong></td>
                    <td><code style="font-size:0.8rem; color:var(--text-muted);">{{ $category->slug }}</code></td>
                    <td>{{ $category->description ? \Illuminate\Support\Str::limit($category->description, 60) : '—' }}</td>
                    <td>
                        <span class="badge badge-admin">{{ $category->products_count }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $category->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-accent btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.categories.toggle', $category) }}" method="POST" style="display:inline">
                            @csrf
                            <button class="btn btn-sm {{ $category->is_active ? 'btn-danger' : 'btn-success' }}">
                                <i class="fas fa-{{ $category->is_active ? 'ban' : 'check' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Delete this category?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-tags"></i>
                            <h4>No categories yet</h4>
                            <p>Create your first category to start organizing products.</p>
                            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Category
                            </a>
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