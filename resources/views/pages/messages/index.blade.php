@extends('layouts.admin')

@section('title', 'Messages')
@section('page_title', 'Messages')

@php
    $isNewMode = $mode === 'new';
@endphp

@push('styles')
    <style>
        .message-stat .inner h3 {
            font-size: 2rem;
        }

        .message-subject {
            font-weight: 600;
            color: #2c3e50;
        }

        .message-preview {
            color: #6c757d;
            max-width: 440px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .message-unread-dot {
            width: 10px;
            height: 10px;
            display: inline-block;
            border-radius: 50%;
            background: #28a745;
            margin-right: 0.5rem;
        }
    </style>
@endpush

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
        <div class="col-md-3">
            <div class="small-box bg-primary message-stat">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>All Messages</p>
                </div>
                <div class="icon"><i class="fas fa-inbox"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success message-stat">
                <div class="inner">
                    <h3>{{ $stats['new'] }}</h3>
                    <p>New Messages</p>
                </div>
                <div class="icon"><i class="fas fa-envelope"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info message-stat">
                <div class="inner">
                    <h3>{{ $stats['read'] }}</h3>
                    <p>Read Messages</p>
                </div>
                <div class="icon"><i class="fas fa-envelope-open-text"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning message-stat">
                <div class="inner">
                    <h3>{{ $stats['today'] }}</h3>
                    <p>Received Today</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.75rem;">
            <div>
                <h3 class="card-title mb-0">{{ $isNewMode ? 'New Website Messages' : 'All Website Messages' }}</h3>
                <div class="text-muted small mt-1">
                    {{ $isNewMode ? 'Unread contact messages waiting for attention.' : 'Messages submitted from the website contact form.' }}
                </div>
            </div>
            <div class="d-flex" style="gap: 0.5rem;">
                <a href="{{ route('messages.index') }}" class="btn {{ $isNewMode ? 'btn-outline-primary' : 'btn-primary' }}">View All</a>
                <a href="{{ route('messages.new') }}" class="btn {{ $isNewMode ? 'btn-success' : 'btn-outline-success' }}">New</a>
            </div>
        </div>
        <div class="card-body px-3 pb-3">
            @if ($messages->isEmpty())
                <div class="text-muted py-3">{{ $isNewMode ? 'No unread messages right now.' : 'No messages have been received yet.' }}</div>
            @else
                <table class="table table-hover js-data-table w-100">
                    <thead>
                    <tr>
                        <th style="width: 70px;">#</th>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th class="dt-actions" style="width: 220px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($messages as $message)
                        <tr>
                            <td>{{ $message->id }}</td>
                            <td>
                                <strong>{{ $message->name ?: 'Guest' }}</strong><br>
                                <span class="text-muted">{{ $message->email ?: 'No email provided' }}</span>
                            </td>
                            <td>
                                @if (! $message->is_read)
                                    <span class="message-unread-dot"></span>
                                @endif
                                <span class="message-subject">{{ $message->subject ?: 'Website message' }}</span>
                                <div class="message-preview">{{ \Illuminate\Support\Str::limit($message->message, 95) }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $message->is_read ? 'badge-secondary' : 'badge-success' }}">
                                    {{ $message->is_read ? 'Read' : 'New' }}
                                </span>
                            </td>
                            <td>{{ optional($message->created_at)->format('d M Y, h:i A') }}</td>
                            <td>
                                <div class="dt-action-group">
                                    <a href="{{ route('messages.show', $message->id) }}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                    @if ($canDelete)
                                        <form action="{{ route('messages.destroy', $message->id) }}" method="POST" onsubmit="return confirm('Delete this message?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
