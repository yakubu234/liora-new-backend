@extends('layouts.admin')

@section('title', 'Testimonials')
@section('page_title', 'Website Testimonials')

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

    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.75rem;">
            <div>
                <h3 class="card-title mb-0">Homepage Testimonial Cards</h3>
                <div class="text-muted small mt-1">Create, edit, and reorder-ready testimonial content for the homepage.</div>
            </div>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createTestimonialModal">
                Add Testimonial
            </button>
        </div>
        <div class="card-body">
            @if ($testimonials->isEmpty())
                <div class="text-muted">No testimonials have been created yet.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover js-data-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Quote</th>
                            <th class="dt-actions">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($testimonials as $testimonial)
                            <tr>
                                <td>{{ $testimonial->id }}</td>
                                <td>{{ $testimonial->name }}</td>
                                <td>{{ $testimonial->role }}</td>
                                <td>{{ str_repeat('★', (int) $testimonial->rating) }}</td>
                                <td>
                                    <span class="badge {{ $testimonial->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                        {{ ucfirst($testimonial->status) }}
                                    </span>
                                </td>
                                <td style="max-width: 360px;">{{ $testimonial->quote }}</td>
                                <td class="dt-actions">
                                    <div class="dt-action-group">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-toggle="modal"
                                            data-target="#editTestimonialModal{{ $testimonial->id }}"
                                        >
                                            Edit
                                        </button>
                                        <form action="{{ route('website.testimonials.destroy', $testimonial->id) }}" method="POST" onsubmit="return confirm('Delete this testimonial?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="createTestimonialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('website.testimonials.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Testimonial</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @include('pages.website.partials.testimonial-form', ['testimonial' => null])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Save Testimonial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ($testimonials as $testimonial)
        <div class="modal fade" id="editTestimonialModal{{ $testimonial->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('website.testimonials.update', $testimonial->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Testimonial</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            @include('pages.website.partials.testimonial-form', ['testimonial' => $testimonial])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-primary" type="submit">Update Testimonial</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
