@extends('layouts.admin')

@section('title', 'View Users')
@section('page_title', 'User Management')

@php
    $badgeClass = fn (string $status) => $status === 'active' ? 'success' : 'secondary';
    $legacyLabel = function ($type) {
        $type = (int) $type;
        if ($type >= 5) {
            return 'Super Admin';
        }
        if ($type > 0) {
            return 'Admin';
        }
        return 'User';
    };
@endphp

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Users</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active'] }}</h3>
                    <p>Active Accounts</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['admins'] }}</h3>
                    <p>Admin Accounts</p>
                </div>
                <div class="icon"><i class="fas fa-user-shield"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['deleted'] }}</h3>
                    <p>Soft Deleted</p>
                </div>
                <div class="icon"><i class="fas fa-user-slash"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Users and Access Overview</h3>
            <div class="card-tools">
                <a href="{{ route('settings.users.create') }}" class="btn btn-success btn-sm">Create User</a>
            </div>
        </div>
        <div class="card-body px-3 pb-3">
            <table class="table table-hover mb-0 js-data-table w-100">
                <thead>
                <tr>
                    <th class="dt-priority-1">Full Name</th>
                    <th class="dt-priority-2">Email / Username</th>
                    <th>Phone</th>
                    <th class="dt-priority-3">Status</th>
                    <th>Legacy Type</th>
                    <th>Roles</th>
                    <th>Access</th>
                    <th class="dt-actions" style="width: 220px;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    @php
                        $roleNames = $user->roles->pluck('name')->filter()->values();
                        $permissionCount = $user->permissions->count() + $user->roles->flatMap->permissions->pluck('id')->unique()->count();
                    @endphp
                    <tr class="{{ $user->trashed() ? 'table-secondary' : '' }}">
                        <td>
                            <strong>{{ $user->fullname }}</strong>
                            @if ($user->trashed())
                                <div><span class="badge badge-dark">Soft Deleted</span></div>
                            @endif
                        </td>
                        <td>
                            {{ $user->email }}<br>
                            <span class="text-muted">{{ $user->username }}</span>
                        </td>
                        <td>{{ $user->phone ?: 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ $badgeClass($user->status ?? 'inactive') }}">
                                {{ $user->status ?: 'inactive' }}
                            </span>
                        </td>
                        <td>{{ $legacyLabel($user->type) }}</td>
                        <td>
                            @if ($roleNames->isNotEmpty())
                                {{ $roleNames->join(', ') }}
                            @else
                                <span class="text-muted">No roles assigned</span>
                            @endif
                        </td>
                        <td>{{ $permissionCount }} permission{{ $permissionCount === 1 ? '' : 's' }}</td>
                        <td>
                            <div class="dt-action-group">
                            <a href="{{ route('settings.users.show', $user->id) }}" class="btn btn-primary btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('settings.users.edit', $user->id) }}" class="btn btn-info btn-sm" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>

                            @if ($user->trashed())
                                <form action="{{ route('settings.users.restore', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Restore">
                                        <i class="fas fa-rotate-left"></i>
                                    </button>
                                </form>
                            @elseif ((int) auth()->id() !== (int) $user->id)
                                <form action="{{ route('settings.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Soft delete this user account?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Create New Role</h3>
                </div>
                <form method="POST" action="{{ route('settings.users.roles.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="role_name">Role Name</label>
                            <input type="text" id="role_name" name="name" class="form-control" required placeholder="e.g. Booking Supervisor">
                        </div>
                        <div class="form-group">
                            <label for="role_description">Description</label>
                            <textarea id="role_description" name="description" class="form-control" rows="3" placeholder="Short description for this role"></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label>Permissions for This Role</label>
                            <div class="row">
                                @foreach ($permissions->groupBy(fn ($permission) => $permission->group_name ?: 'General') as $group => $items)
                                    <div class="col-md-6">
                                        <div class="border rounded p-2 mb-2">
                                            <strong class="d-block mb-2">{{ $group }}</strong>
                                            @foreach ($items as $permission)
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" type="checkbox" id="role_permission_{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}">
                                                    <label class="custom-control-label" for="role_permission_{{ $permission->id }}">{{ $permission->name }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">Save Role</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Create New Permission</h3>
                </div>
                <form method="POST" action="{{ route('settings.users.permissions.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="permission_name">Permission Name</label>
                            <input type="text" id="permission_name" name="name" class="form-control" required placeholder="e.g. Export Reports">
                        </div>
                        <div class="form-group">
                            <label for="permission_group_name">Access Group</label>
                            <input type="text" id="permission_group_name" name="group_name" class="form-control" placeholder="e.g. Reports">
                        </div>
                        <div class="form-group mb-0">
                            <label for="permission_description">Description</label>
                            <textarea id="permission_description" name="description" class="form-control" rows="4" placeholder="Explain what this access allows"></textarea>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">Save Permission</button>
                    </div>
                </form>
            </div>

            <div class="card card-outline card-light">
                <div class="card-header">
                    <h3 class="card-title">Existing Roles and Access</h3>
                </div>
                <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                    @forelse ($roles as $role)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1">{{ $role->name }}</h5>
                                    <p class="text-muted mb-2">{{ $role->description ?: 'No description added yet.' }}</p>
                                </div>
                                <span class="badge badge-info">{{ $role->users_count }} user{{ $role->users_count === 1 ? '' : 's' }}</span>
                            </div>
                            <div class="text-sm">
                                <strong>Permissions:</strong>
                                {{ $role->permissions->pluck('name')->join(', ') ?: 'No permissions assigned yet.' }}
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No roles available yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
