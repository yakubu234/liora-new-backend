@extends('layouts.admin')

@section('title', 'User Details')
@section('page_title', 'User Details')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-4">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Profile Summary</h3>
                    <div class="card-tools">
                        <a href="{{ route('settings.users.edit', $userRecord->id) }}" class="btn btn-info btn-sm">Edit User</a>
                    </div>
                </div>
                <div class="card-body">
                    <p><strong>Full Name:</strong><br>{{ $userRecord->fullname }}</p>
                    <p><strong>Username:</strong><br>{{ $userRecord->username }}</p>
                    <p><strong>Email:</strong><br>{{ $userRecord->email }}</p>
                    <p><strong>Phone:</strong><br>{{ $userRecord->phone ?: 'N/A' }}</p>
                    <p><strong>Status:</strong><br>
                        <span class="badge badge-{{ $userRecord->status === 'active' ? 'success' : 'secondary' }}">{{ $userRecord->status ?: 'inactive' }}</span>
                        @if ($userRecord->trashed())
                            <span class="badge badge-dark">Soft Deleted</span>
                        @endif
                    </p>
                    <p class="mb-0"><strong>Legacy Type:</strong><br>
                        {{ (int) $userRecord->type >= 5 ? 'Super Admin' : ((int) $userRecord->type > 0 ? 'Admin' : 'User') }}
                    </p>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Access Summary</h3>
                </div>
                <div class="card-body">
                    <p><strong>Roles</strong></p>
                    <p>{{ $userRecord->roles->pluck('name')->join(', ') ?: 'No roles assigned.' }}</p>

                    <p><strong>Direct Permissions</strong></p>
                    <p>{{ $userRecord->permissions->pluck('name')->join(', ') ?: 'No direct permissions assigned.' }}</p>

                    <p class="mb-0"><strong>Effective Permissions</strong></p>
                    <p class="mb-0">{{ $effectivePermissions->pluck('name')->join(', ') ?: 'No effective permissions available.' }}</p>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Activity History</h3>
                </div>
                <div class="card-body px-3 pb-3">
                    <table class="table table-hover mb-0 js-data-table w-100">
                        <thead>
                        <tr>
                            <th class="dt-priority-2" style="width: 180px;">Date</th>
                            <th class="dt-priority-1">Action</th>
                            <th style="width: 160px;">Booking Ref</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($audits as $audit)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($audit->created_at)->format('M d, Y h:i A') }}</td>
                                <td>{{ $audit->action }}</td>
                                <td>
                                    @if ($audit->booking_id)
                                        <a href="{{ route('bookings.show', $audit->booking_id) }}">#{{ $audit->booking_id }}</a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No audit history recorded for this user yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
