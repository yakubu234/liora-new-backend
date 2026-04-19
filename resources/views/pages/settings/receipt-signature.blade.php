@extends('layouts.admin')

@section('title', 'Receipt Signature')
@section('page_title', 'Receipt Signature')

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
            <h3 class="card-title">Receipt Signature Details</h3>
        </div>
        <form method="POST" action="{{ route('settings.receipt-signature.update') }}">
            @csrf
            <div class="card-body">
                <p class="text-muted">
                    Update the receipt signature content used wherever the system needs to display or print signature details on receipts.
                </p>

                <div class="form-group mb-0">
                    <label for="receipt_signature_editor">Enter Receipt Signature Description</label>
                    <textarea class="form-control" id="receipt_signature_editor" name="description" required>{{ old('description', $decodedDescription) }}</textarea>
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary">Save Receipt Signature</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/adminlte/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script>
        $(function () {
            $('#receipt_signature_editor').summernote({
                height: 450,
                placeholder: 'Type the receipt signature details here...'
            });
        });
    </script>
@endpush
