@extends('layouts.admin')

@section('title', 'Type of Event')
@section('page_title', 'Type of Event')

@php
    $userType = (int) (auth()->user()->type ?? 0);
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

    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['enabled'] }}</h3>
                    <p>Enabled Event Types</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-plus"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Event Types</p>
                </div>
                <div class="icon"><i class="fas fa-list"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['disabled'] }}</h3>
                    <p>Disabled Event Types</p>
                </div>
                <div class="icon"><i class="fas fa-ban"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">All Available Event Types</h3>
            <div class="card-tools">
                @if ($userType > 0)
                    <a href="{{ route('settings.event-types.create') }}" class="btn btn-success btn-sm">Add New</a>
                @endif
            </div>
        </div>
        <div class="card-body px-3 pb-3">
            <table class="table table-hover mb-0 js-data-table w-100">
                <thead>
                <tr>
                    <th class="dt-priority-1" style="width: 70px;">#</th>
                    <th class="dt-priority-2">Name</th>
                    <th class="dt-priority-3" style="width: 140px;">Status</th>
                    <th style="width: 220px;">Created On</th>
                    <th class="dt-actions" style="width: 170px;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($eventTypes as $eventType)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $eventType->name }}</strong></td>
                        <td>
                            <span class="badge badge-success">{{ $eventType->status }}</span>
                        </td>
                        <td>{{ \Illuminate\Support\Carbon::parse($eventType->created_at)->format('M d, Y h:i A') }}</td>
                        <td>
                            <div class="dt-action-group">
                            @if ($userType > 0)
                                <a href="{{ route('settings.event-types.edit', $eventType->id) }}" class="btn btn-info btn-sm" title="Edit Event Type">
                                    <i class="fas fa-pen"></i>
                                </a>

                                <form action="{{ route('settings.event-types.destroy', $eventType->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Disable this event type?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Disable Event Type">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No enabled event types found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
