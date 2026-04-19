@extends('layouts.admin')

@section('title', $userRecord->exists ? 'Edit User' : 'Create User')
@section('page_title', $userRecord->exists ? 'Edit User' : 'Create User')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $userRecord->exists ? 'Update user access and profile' : 'Create a new staff user' }}</h3>
        </div>
        <form method="POST" action="{{ $userRecord->exists ? route('settings.users.update', $userRecord->id) : route('settings.users.store') }}">
            @csrf
            @if ($userRecord->exists)
                @method('PUT')
            @endif
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
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $userRecord->phone) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="active" @selected(old('status', $userRecord->status) === 'active')>Active</option>
                                <option value="inactive" @selected(old('status', $userRecord->status) === 'inactive')>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type">Legacy User Type</label>
                            <select id="type" name="type" class="form-control" required>
                                @foreach ($legacyTypes as $typeValue => $typeLabel)
                                    <option value="{{ $typeValue }}" @selected((string) old('type', (string) $userRecord->type) === (string) $typeValue)>{{ $typeLabel }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">This keeps compatibility with the existing admin logic while roles handle the new access system.</small>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">{{ $userRecord->exists ? 'New Password' : 'Password' }}</label>
                            <input type="password" id="password" name="password" class="form-control" {{ $userRecord->exists ? '' : 'required' }}>
                            <small class="form-text text-muted">
                                {{ $userRecord->exists ? 'Leave blank to keep the current password.' : 'Minimum 8 characters.' }}
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" {{ $userRecord->exists ? '' : 'required' }}>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group mb-0">
                            <label>Assign Roles</label>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                @forelse ($roles as $role)
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input
                                            class="custom-control-input"
                                            type="checkbox"
                                            id="role_{{ $role->id }}"
                                            name="roles[]"
                                            value="{{ $role->id }}"
                                            @checked(in_array($role->id, old('roles', $selectedRoleIds), true))
                                        >
                                        <label class="custom-control-label" for="role_{{ $role->id }}">
                                            {{ $role->name }}
                                            @if ($role->description)
                                                <span class="d-block text-muted">{{ $role->description }}</span>
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No roles available yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group mb-0">
                            <label>Assign Direct Permissions</label>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                @forelse ($permissions as $group => $items)
                                    <div class="mb-3">
                                        <strong class="d-block mb-2">{{ $group }}</strong>
                                        @foreach ($items as $permission)
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input
                                                    class="custom-control-input"
                                                    type="checkbox"
                                                    id="permission_{{ $permission->id }}"
                                                    name="permissions[]"
                                                    value="{{ $permission->id }}"
                                                    @checked(in_array($permission->id, old('permissions', $selectedPermissionIds), true))
                                                >
                                                <label class="custom-control-label" for="permission_{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                    @if ($permission->description)
                                                        <span class="d-block text-muted">{{ $permission->description }}</span>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No permissions available yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('settings.users') }}" class="btn btn-default">Back</a>
                <button type="submit" class="btn btn-primary">{{ $userRecord->exists ? 'Save Changes' : 'Create User' }}</button>
            </div>
        </form>
    </div>
@endsection
