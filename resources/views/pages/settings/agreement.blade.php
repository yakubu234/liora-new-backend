@extends('layouts.admin')

@section('title', 'Agreement')
@section('page_title', 'Agreement')

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/summernote/summernote-bs4.min.css') }}">
    <style>
        .note-editor.note-frame {
            border: 1px solid #ced4da;
        }

        .note-editor .note-editing-area .note-editable {
            min-height: 420px;
            background: #fff;
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
            <h3 class="card-title">Agreement Details</h3>
        </div>
        <form method="POST" action="{{ route('settings.agreement.update') }}">
            @csrf
            <div class="card-body">
                <p class="text-muted">
                    Update the agreement content used across bookings. This editor supports formatted text and basic rich content.
                </p>

                <div class="form-group mb-0">
                    <label for="agreement_editor">Enter Agreement Description</label>
                    <textarea class="form-control" id="agreement_editor" name="description" required>{{ old('description', $decodedDescription) }}</textarea>
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary">Save Agreement</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/adminlte/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script>
        $(function () {
            $('#agreement_editor').summernote({
                height: 450,
                placeholder: 'Type the agreement details here...'
            });
        });
    </script>
@endpush
