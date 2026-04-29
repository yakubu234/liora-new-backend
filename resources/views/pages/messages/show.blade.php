@extends('layouts.admin')

@section('title', 'Message Details')
@section('page_title', 'Message Details')

@push('styles')
    <style>
        .message-detail-card .card-title {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .message-body-box {
            white-space: pre-wrap;
            line-height: 1.75;
            color: #495057;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.75rem;
            padding: 1rem;
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

    <div class="card card-outline card-primary message-detail-card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.75rem;">
            <div>
                <h3 class="card-title mb-1">{{ $messageRecord->subject ?: 'Website message' }}</h3>
                <div class="text-muted small">Received {{ optional($messageRecord->created_at)->format('d M Y, h:i A') }}</div>
            </div>
            <div class="d-flex" style="gap: 0.5rem;">
                <a href="{{ route('messages.index') }}" class="btn btn-outline-primary">Back to Messages</a>
                @if ($canDelete)
                    <form action="{{ route('messages.destroy', $messageRecord->id) }}" method="POST" onsubmit="return confirm('Delete this message?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4">
                    <div class="mb-3">
                        <strong>Sender</strong>
                        <div>{{ $messageRecord->name ?: 'Guest' }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Email</strong>
                        <div>{{ $messageRecord->email ?: 'No email provided' }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Recipient Channel</strong>
                        <div>{{ $messageRecord->recepient ?: 'website-contact' }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Status</strong>
                        <div>
                            <span class="badge {{ $messageRecord->is_read ? 'badge-secondary' : 'badge-success' }}">
                                {{ $messageRecord->is_read ? 'Read' : 'New' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <strong>Message Body</strong>
                    <div class="message-body-box mt-2">{{ $messageRecord->message }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
