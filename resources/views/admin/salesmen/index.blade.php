@extends('layouts.app')
@section('title', 'Salesmen')
@section('page-title', 'Salesmen')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">Salesmen</div>
        <div class="page-header-sub">Manage salesman accounts for your outlet</div>
    </div>
    <a href="{{ route('admin.salesmen.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Salesman
    </a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesmen as $salesman)
                <tr>
                    <td>{{ $salesmen->firstItem() + $loop->index }}</td>
                    <td><strong>{{ $salesman->name }}</strong></td>
                    <td>{{ $salesman->email }}</td>
                    <td>{{ $salesman->phone ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $salesman->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $salesman->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>{{ $salesman->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.salesmen.edit', $salesman) }}" class="btn btn-accent btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.salesmen.toggle', $salesman) }}" method="POST" style="display:inline">
                            @csrf
                            <button class="btn btn-sm {{ $salesman->is_active ? 'btn-danger' : 'btn-success' }}">
                                <i class="fas fa-{{ $salesman->is_active ? 'ban' : 'check' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.salesmen.destroy', $salesman) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Delete this salesman account?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-user-tie"></i>
                            <h4>No salesmen yet</h4>
                            <p>Add your first salesman account.</p>
                            <a href="{{ route('admin.salesmen.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Salesman
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($salesmen->hasPages())
    <div class="card-footer">{{ $salesmen->links() }}</div>
    @endif
</div>
@endsection