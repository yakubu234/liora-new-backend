@extends('layouts.admin')

@section('title', $service->exists ? 'Edit Service' : 'Add New Service')
@section('page_title', $service->exists ? 'Edit Service' : 'Add New Service')

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
            <h3 class="card-title">{{ $service->exists ? 'Update service details' : 'Create a new service' }}</h3>
        </div>
        <form method="POST" action="{{ $service->exists ? route('services.update', $service->id) : route('services.store') }}">
            @csrf
            @if ($service->exists)
                @method('PUT')
            @endif
            <div class="card-body">
                <p class="text-muted">
                    Add or update the service details below. Disabled services stay in the system but will no longer appear as enabled booking options.
                </p>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $service->name) }}" required placeholder="Enter service name">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required placeholder="Enter service description">{{ old('description', $service->description) }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" value="{{ old('price', $service->price) }}" required placeholder="Enter service price">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="enabled" @selected(old('status', $service->status) === 'enabled')>Enabled</option>
                                <option value="disabled" @selected(old('status', $service->status) === 'disabled')>Disabled</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('services.index') }}" class="btn btn-default">Back</a>
                <button type="submit" class="btn btn-primary">{{ $service->exists ? 'Save Changes' : 'Create Service' }}</button>
            </div>
        </form>
    </div>
@endsection
