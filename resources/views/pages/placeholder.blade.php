@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <h3 class="mb-3">{{ $title }}</h3>
            <p class="text-muted mb-4">{{ $description }}</p>

            <div class="alert alert-info mb-0">
                This menu item is now wired into the admin panel. We can build this feature next.
            </div>
        </div>
    </div>
@endsection
