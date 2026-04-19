@extends('layouts.admin')

@section('title', 'User Profile')
@section('page_title', 'User Profile')

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

    <div class="row">
        <div class="col-xl-4">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Account Summary</h3>
                </div>
                <div class="card-body">
                    <p><strong>Full Name</strong><br>{{ $userRecord->fullname }}</p>
                    <p><strong>Username</strong><br>{{ $userRecord->username }}</p>
                    <p><strong>Email</strong><br>{{ $userRecord->email }}</p>
                    <p><strong>Phone</strong><br>{{ $userRecord->phone ?: 'N/A' }}</p>
                    <p><strong>Status</strong><br>
                        <span class="badge badge-{{ $userRecord->status === 'active' ? 'success' : 'secondary' }}">
                            {{ $userRecord->status ?: 'inactive' }}
                        </span>
                    </p>
                    <p><strong>Access Level</strong><br>
                        {{ (int) $userRecord->type >= 5 ? 'Super Admin' : ((int) $userRecord->type > 0 ? 'Admin' : 'User') }}
                    </p>
                    <p class="mb-0"><strong>Roles</strong><br>
                        {{ $userRecord->roles->pluck('name')->join(', ') ?: 'No roles assigned' }}
                    </p>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Effective Access</h3>
                </div>
                <div class="card-body">
                    @if ($effectivePermissions->isEmpty())
                        <p class="text-muted mb-0">No direct or role-based permissions are currently assigned.</p>
                    @else
                        <div style="max-height: 280px; overflow-y: auto;">
                            @foreach ($effectivePermissions->groupBy(fn ($permission) => $permission->group_name ?: 'General') as $group => $items)
                                <div class="mb-3">
                                    <strong class="d-block mb-2">{{ $group }}</strong>
                                    <div class="text-muted">
                                        {{ $items->pluck('name')->join(', ') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Update Profile Details</h3>
                </div>
                <form method="POST" action="{{ route('profile.user.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fullname">Full Name</label>
                                    <input type="text" id="fullname" name="fullname" class="form-control" value="{{ old('fullname', $userRecord->fullname) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" class="form-control" value="{{ old('username', $userRecord->username) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $userRecord->email) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="phone">Phone</label>
                                    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $userRecord->phone) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">Save Profile Details</button>
                    </div>
                </form>
            </div>

            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title">Update Password</h3>
                </div>
                <form method="POST" action="{{ route('profile.user.password') }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">New Password</label>
                                    <input type="password" id="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="password_confirmation">Confirm New Password</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-danger">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
