@extends('layouts.admin')

@section('title', 'Website Slider')
@section('page_title', 'Homepage Slider Images')

@push('styles')
    <style>
        .slider-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .slider-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.25rem;
        }

        .slider-card {
            border: 1px solid #dee2e6;
            border-radius: 1rem;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
        }

        .slider-media {
            position: relative;
            height: 230px;
            background: #edf2f7;
        }

        .slider-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .slider-media video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            background: #111827;
        }

        .slider-delete-btn {
            position: absolute;
            top: 0.85rem;
            right: 0.85rem;
            border: 0;
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: rgba(220, 53, 69, 0.95);
            color: #fff;
        }

        .slider-card-body {
            padding: 1rem 1rem 1.1rem;
        }

        .slider-card-body p {
            margin-bottom: 0;
            color: #6c757d;
            min-height: 48px;
        }

        .slider-empty {
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

    @if ($sliders->count() === $expectedCount)
        <div class="alert alert-success">
            The homepage slider is complete. It currently has exactly {{ $expectedCount }} active image{{ $expectedCount === 1 ? '' : 's' }}.
        </div>
    @else
        <div class="alert alert-warning">
            The homepage slider expects exactly {{ $expectedCount }} images. You currently have {{ $sliders->count() }} active image{{ $sliders->count() === 1 ? '' : 's' }}.
            @if ($remainingSlots > 0)
                Upload {{ $remainingSlots }} more to complete the slider.
            @else
                Remove extra images until the slider returns to the expected count.
            @endif
        </div>
    @endif

    @if ($videoSlides->count() === $expectedVideoCount)
        <div class="alert alert-success">
            The homepage video slider is complete. It currently has exactly {{ $expectedVideoCount }} active video slide.
        </div>
    @else
        <div class="alert alert-warning">
            The homepage expects exactly {{ $expectedVideoCount }} additional hero video slide. You currently have {{ $videoSlides->count() }} active video slide{{ $videoSlides->count() === 1 ? '' : 's' }}.
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="slider-toolbar">
                <div>
                    <h3 class="card-title mb-0">Homepage Slider Manager</h3>
                    <div class="text-muted small mt-1">Upload optimized hero images for the standalone homepage. Images are converted for faster delivery.</div>
                </div>
                <div class="d-flex flex-wrap" style="gap: 0.75rem;">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadSingleSlider" {{ $remainingSlots === 0 ? 'disabled' : '' }}>
                        Upload Single
                    </button>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#uploadMultipleSlider" {{ $remainingSlots === 0 ? 'disabled' : '' }}>
                        Upload Multiple
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if ($sliders->isEmpty())
                <div class="slider-empty">
                    No slider images have been uploaded yet.
                </div>
            @else
                <div class="slider-grid">
                    @foreach ($sliders as $index => $slider)
                        <div class="slider-card">
                            <div class="slider-media">
                                <a href="{{ asset('uploads/sliders/' . $slider->img) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset('uploads/sliders/' . $slider->img) }}" alt="{{ $slider->heading ?: 'Slider image ' . ($index + 1) }}">
                                </a>

                                <form action="{{ route('website.slider.destroy', $slider->id) }}" method="POST" onsubmit="return confirm('Delete this slider image?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="slider-delete-btn" title="Delete image">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="slider-card-body">
                                <h5>{{ $slider->heading ?: 'Slider Image ' . ($index + 1) }}</h5>
                                <p>{{ $slider->text ?: 'This image will be used in the homepage hero slider.' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card card-outline card-info mt-4">
        <div class="card-header">
            <div class="slider-toolbar">
                <div>
                    <h3 class="card-title mb-0">Homepage Slider Video</h3>
                    <div class="text-muted small mt-1">Upload one additional hero video slide. Use compressed MP4 or WebM for the fastest load.</div>
                </div>
                <div class="d-flex flex-wrap" style="gap: 0.75rem;">
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#uploadSliderVideo" {{ $videoSlides->count() >= $expectedVideoCount ? 'disabled' : '' }}>
                        Upload Video
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if ($videoSlides->isEmpty())
                <div class="slider-empty">
                    No hero slider video has been uploaded yet.
                </div>
            @else
                <div class="slider-grid">
                    @foreach ($videoSlides as $index => $videoSlide)
                        <div class="slider-card">
                            <div class="slider-media">
                                <video controls preload="metadata">
                                    <source src="{{ asset('uploads/sliders/videos/' . $videoSlide->video) }}">
                                </video>

                                <form action="{{ route('website.slider.video.destroy', $videoSlide->id) }}" method="POST" onsubmit="return confirm('Delete this slider video?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="slider-delete-btn" title="Delete video">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="slider-card-body">
                                <h5>{{ $videoSlide->heading ?: 'Hero Video Slide ' . ($index + 1) }}</h5>
                                <p>{{ $videoSlide->text ?: 'This video will be added as the extra homepage hero slide.' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="uploadSingleSlider" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('website.slider.single') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Single Slider Image</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            Remaining slider slots: {{ $remainingSlots }} of {{ $expectedCount }}.
                        </div>
                        <div class="form-group">
                            <label for="single_slider_file">Choose Image</label>
                            <input class="form-control" id="single_slider_file" name="file" type="file" accept="image/*" required>
                        </div>
                        <div class="form-group">
                            <label for="single_slider_heading">Image Title</label>
                            <input class="form-control" id="single_slider_heading" type="text" name="heading" placeholder="Optional slider title">
                        </div>
                        <div class="form-group mb-0">
                            <label for="single_slider_text">Image Details</label>
                            <textarea class="form-control" id="single_slider_text" name="text" rows="4" placeholder="Optional slider details"></textarea>
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

    <div class="modal fade" id="uploadMultipleSlider" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('website.slider.multiple') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Multiple Slider Images</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            The homepage slider expects exactly {{ $expectedCount }} images. You can only upload up to {{ $remainingSlots }} more right now.
                        </div>
                        <div class="form-group">
                            <label for="multiple_slider_files">Choose Images</label>
                            <input class="form-control" id="multiple_slider_files" name="files[]" type="file" accept="image/*" multiple required>
                        </div>
                        <div class="form-group">
                            <label for="multiple_slider_heading">Shared Title</label>
                            <input class="form-control" id="multiple_slider_heading" type="text" name="heading" placeholder="Optional shared title">
                        </div>
                        <div class="form-group mb-0">
                            <label for="multiple_slider_text">Shared Details</label>
                            <textarea class="form-control" id="multiple_slider_text" name="text" rows="4" placeholder="Optional shared details"></textarea>
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

    <div class="modal fade" id="uploadSliderVideo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('website.slider.video') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Hero Slider Video</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            The homepage allows exactly {{ $expectedVideoCount }} active hero video slide. Use MP4 or WebM and keep the file compressed for faster loading.
                        </div>
                        <div class="form-group">
                            <label for="slider_video_file">Choose Video</label>
                            <input class="form-control" id="slider_video_file" name="video" type="file" accept="video/mp4,video/webm" required>
                        </div>
                        <div class="form-group">
                            <label for="slider_video_heading">Video Title</label>
                            <input class="form-control" id="slider_video_heading" type="text" name="heading" placeholder="Optional slide title">
                        </div>
                        <div class="form-group mb-0">
                            <label for="slider_video_text">Video Details</label>
                            <textarea class="form-control" id="slider_video_text" name="text" rows="4" placeholder="Optional slide details"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-info" type="submit">Upload Video</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
