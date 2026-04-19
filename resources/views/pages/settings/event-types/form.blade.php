@extends('layouts.admin')

@section('title', $eventType->exists ? 'Edit Event Type' : 'Add Event Type')
@section('page_title', $eventType->exists ? 'Edit Event Type' : 'Add Event Type')

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
            <h3 class="card-title">{{ $eventType->exists ? 'Update event type details' : 'Create a new event type' }}</h3>
        </div>
        <form method="POST" action="{{ $eventType->exists ? route('settings.event-types.update', $eventType->id) : route('settings.event-types.store') }}">
            @csrf
            @if ($eventType->exists)
                @method('PUT')
            @endif
            <div class="card-body">
                <p class="text-muted">
                    Event types are used in the booking form. Disabled event types will no longer appear as selectable options for new bookings.
                </p>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $eventType->name) }}" required placeholder="Enter event type name">
                </div>

                <div class="form-group mb-0">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="enabled" @selected(old('status', $eventType->status) === 'enabled')>Enabled</option>
                        <option value="disabled" @selected(old('status', $eventType->status) === 'disabled')>Disabled</option>
                    </select>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('settings.event-types') }}" class="btn btn-default">Back</a>
                <button type="submit" class="btn btn-primary">{{ $eventType->exists ? 'Save Changes' : 'Create Event Type' }}</button>
            </div>
        </form>
    </div>
@endsection
