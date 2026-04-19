@extends('layouts.admin')

@section('title', 'Services')
@section('page_title', 'Services')

@php
    $currency = fn (float|int|string $amount) => 'NGN ' . number_format((float) preg_replace('/[^\d.\-]/', '', (string) $amount), 2);
    $userType = (int) (auth()->user()->type ?? 0);
@endphp

@push('styles')
    <style>
        .services-stat-value-small .inner h3 {
            font-size: 1.55rem;
            line-height: 1.2;
            word-break: break-word;
            overflow-wrap: anywhere;
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

    <div class="row">
        <div class="col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Services</p>
                </div>
                <div class="icon"><i class="fas fa-concierge-bell"></i></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['enabled'] }}</h3>
                    <p>Enabled Services</p>
                </div>
                <div class="icon"><i class="fas fa-circle-check"></i></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['disabled'] }}</h3>
                    <p>Disabled Services</p>
                </div>
                <div class="icon"><i class="fas fa-ban"></i></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-primary services-stat-value-small">
                <div class="inner">
                    <h3>{{ $currency($stats['value']) }}</h3>
                    <p>Total Listed Value</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">All Available Services</h3>
            <div class="card-tools d-flex" style="gap: 0.5rem;">
                <a href="{{ route('bookings.create') }}" class="btn btn-success btn-sm">Book Now</a>
                @if ($userType > 0)
                    <a href="{{ route('services.create') }}" class="btn btn-primary btn-sm">Add New Service</a>
                @endif
            </div>
        </div>
        <div class="card-body px-3 pb-3">
            <table class="table table-hover mb-0 js-data-table w-100">
                <thead>
                <tr>
                    <th class="dt-priority-1" style="width: 70px;">#</th>
                    <th class="dt-priority-2">Name</th>
                    <th>Description</th>
                    <th style="width: 150px;">Amount</th>
                    <th class="dt-priority-3" style="width: 130px;">Status</th>
                    <th class="dt-actions" style="width: 220px;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($services as $service)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $service->name }}</strong></td>
                        <td>{{ $service->description }}</td>
                        <td>{{ $currency($service->price) }}</td>
                        <td>
                            <span class="badge badge-{{ $service->status === 'enabled' ? 'success' : 'secondary' }}">
                                {{ $service->status ?: 'disabled' }}
                            </span>
                        </td>
                        <td>
                            <div class="dt-action-group">
                            <a href="{{ route('bookings.create') }}" class="btn btn-primary btn-sm" title="Book Now">
                                <i class="fas fa-bookmark"></i>
                            </a>

                            @if ($userType > 0)
                                <a href="{{ route('services.edit', $service->id) }}" class="btn btn-info btn-sm" title="Edit Service">
                                    <i class="fas fa-pen"></i>
                                </a>

                                @if ($service->status !== 'disabled')
                                    <form action="{{ route('services.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Disable this service?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Disable Service">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                @endif
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No services found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
