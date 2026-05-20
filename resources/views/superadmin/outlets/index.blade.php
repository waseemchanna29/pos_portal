@extends('layouts.app')
@section('title', 'Outlets')
@section('page-title', 'Outlets')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">All Outlets</div>
        <div class="page-header-sub">Manage all POS outlets</div>
    </div>
    <a href="{{ route('superadmin.outlets.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Outlet
    </a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>Province</th>
                    <th>Phone</th>
                    <th>Users</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($outlets as $outlet)
                <tr>
                    <td>{{ $outlets->firstItem() + $loop->index }}</td>
                    <td><strong>{{ $outlet->name }}</strong></td>
                    <td>{{ $outlet->address }}</td>
                    <td>{{ $outlet->city }}</td>
                    <td>{{ $outlet->province }}</td>
                    <td>{{ $outlet->phone ?? '—' }}</td>
                    <td>{{ $outlet->users_count }}</td>
                    <td>
                        <span class="badge {{ $outlet->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $outlet->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('superadmin.outlets.show', $outlet) }}" class="btn-outline btn btn-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('superadmin.outlets.edit', $outlet) }}" class="btn btn-accent btn-sm">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('superadmin.outlets.toggle', $outlet) }}" method="POST" style="display:inline">
                            @csrf
                            <button class="btn btn-sm {{ $outlet->is_active ? 'btn-danger' : 'btn-success' }}">
                                <i class="fas fa-{{ $outlet->is_active ? 'ban' : 'check' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('superadmin.outlets.destroy', $outlet) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Delete this outlet?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="fas fa-store"></i>
                            <h4>No outlets yet</h4>
                            <p>Create your first outlet to get started.</p>
                            <a href="{{ route('superadmin.outlets.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Outlet
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($outlets->hasPages())
    <div class="card-footer">{{ $outlets->links() }}</div>
    @endif
</div>
@endsection