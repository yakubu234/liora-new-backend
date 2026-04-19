@extends('layouts.admin')

@section('title', 'SMTP Details Update')
@section('page_title', 'SMTP Details Update')

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

    <div class="row">
        <div class="col-xl-5 col-lg-7">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Add New or Update SMTP Details</h3>
                </div>
                <form method="POST" action="{{ route('website.smtp.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <p class="text-muted">
                            These credentials are used for outgoing email notifications. The recipient email is the admin notification address that receives updates from the website.
                        </p>

                        <div class="form-group">
                            <label for="username">SMTP Email</label>
                            <input
                                type="email"
                                id="username"
                                name="username"
                                class="form-control"
                                value="{{ old('username', $config?->username) }}"
                                required
                                placeholder="Enter SMTP email"
                            >
                        </div>

                        <div class="form-group">
                            <label for="password">SMTP Password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                value="{{ old('password', $config?->password) }}"
                                required
                                placeholder="Enter SMTP password"
                            >
                        </div>

                        <div class="form-group mb-0">
                            <label for="receiver_id">Recipient Email</label>
                            <input
                                type="email"
                                id="receiver_id"
                                name="receiver_id"
                                class="form-control"
                                value="{{ old('receiver_id', $config?->receiver_id ?: $defaultRecipient) }}"
                                required
                                placeholder="Enter recipient email"
                            >
                            <small class="form-text text-muted">
                                Default admin notification email: {{ $defaultRecipient }}
                            </small>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button class="btn btn-primary" type="submit">Save SMTP Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
