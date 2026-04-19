@extends('layouts.admin')

@section('title', 'Contact')
@section('page_title', 'Contact')

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
            <h3 class="card-title">Company Contact Information</h3>
        </div>
        <form method="POST" action="{{ route('website.contact.update') }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <p class="text-muted">
                    Update the company contact details that should appear on the website contact page.
                </p>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="{{ old('email', $contact->email) }}"
                        required
                        placeholder="Enter company email"
                    >
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea
                        id="address"
                        name="address"
                        class="form-control"
                        rows="4"
                        required
                        placeholder="Enter company address"
                    >{{ old('address', $contact->address) }}</textarea>
                </div>

                <div class="form-group mb-0">
                    <label for="phone">Phone Number</label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        class="form-control"
                        value="{{ old('phone', $contact->phone) }}"
                        required
                        placeholder="Enter company phone number"
                    >
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary">Save Contact Information</button>
            </div>
        </form>
    </div>
@endsection
