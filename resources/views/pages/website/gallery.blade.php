@extends('layouts.admin')

@section('title', 'Gallery')
@section('page_title', 'Gallery Grid With Description')

@push('styles')
    <style>
        .gallery-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.25rem;
        }

        .gallery-card {
            border: 1px solid #dee2e6;
            border-radius: 1rem;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .gallery-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 32px rgba(15, 23, 42, 0.12);
        }

        .gallery-media {
            position: relative;
            height: 230px;
            background: #edf2f7;
        }

        .gallery-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .gallery-delete-btn {
            position: absolute;
            top: 0.85rem;
            right: 0.85rem;
            border: 0;
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: rgba(220, 53, 69, 0.95);
            color: #fff;
            box-shadow: 0 10px 18px rgba(220, 53, 69, 0.2);
        }

        .gallery-delete-btn:hover {
            background: #c82333;
        }

        .gallery-card-body {
            padding: 1rem 1rem 1.1rem;
        }

        .gallery-card-body h5 {
            margin-bottom: 0.45rem;
        }

        .gallery-card-body p {
            margin-bottom: 0;
            color: #6c757d;
            min-height: 48px;
        }

        .gallery-empty {
            border: 1px dashed #ced4da;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            color: #6c757d;
            background: #fafbfc;
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
            <div class="gallery-toolbar">
                <div>
                    <h3 class="card-title mb-0">Image Gallery With Description</h3>
                    <div class="text-muted small mt-1">Upload images, review the preview, and remove any image from the gallery grid.</div>
                </div>
                <div class="d-flex flex-wrap" style="gap: 0.75rem;">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadSingleImage">
                        Upload Single
                    </button>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#uploadMultipleImage">
                        Upload Multiple
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if ($galleries->isEmpty())
                <div class="gallery-empty">
                    No gallery images have been uploaded yet.
                </div>
            @else
                <div class="gallery-grid">
                    @foreach ($galleries as $gallery)
                        <div class="gallery-card">
                            <div class="gallery-media">
                                <a href="{{ asset('uploads/' . $gallery->img) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset('uploads/' . $gallery->img) }}" alt="{{ $gallery->heading ?: 'Gallery image' }}">
                                </a>

                                <form action="{{ route('website.gallery.destroy', $gallery->id) }}" method="POST" onsubmit="return confirm('Delete this gallery image?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="gallery-delete-btn" title="Delete image">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="gallery-card-body">
                                <h5>{{ $gallery->heading ?: 'Untitled image' }}</h5>
                                <p>{{ $gallery->text ?: 'No extra image details supplied yet.' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="uploadSingleImage" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('website.gallery.single') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Single Image With Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="single_file">Choose Image</label>
                            <input class="form-control" id="single_file" name="file" type="file" accept="image/*" required>
                        </div>
                        <div class="form-group">
                            <label for="single_heading">Image Title</label>
                            <input class="form-control" id="single_heading" type="text" name="heading" placeholder="Enter image title" required>
                        </div>
                        <div class="form-group mb-0">
                            <label for="single_text">Image Details</label>
                            <textarea class="form-control" id="single_text" name="text" rows="4" placeholder="Enter image details"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadMultipleImage" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('website.gallery.multiple') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Multiple Images</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="multiple_files">Choose Images</label>
                            <input class="form-control" id="multiple_files" name="files[]" type="file" accept="image/*" multiple required>
                        </div>
                        <div class="form-group">
                            <label for="multiple_heading">Shared Title for Uploaded Images</label>
                            <input class="form-control" id="multiple_heading" type="text" name="heading" placeholder="Optional shared title">
                        </div>
                        <div class="form-group mb-0">
                            <label for="multiple_text">Shared Details</label>
                            <textarea class="form-control" id="multiple_text" name="text" rows="4" placeholder="Optional shared details for all uploaded images"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-success" type="submit">Upload Images</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
