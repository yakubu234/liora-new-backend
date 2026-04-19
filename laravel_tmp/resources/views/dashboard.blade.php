@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Booking Dashboard')

@php
    $currency = fn (float|int $amount) => '₦' . number_format((float) $amount, 2);
@endphp

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total_bookings']) }}</h3>
                    <p>Total bookings</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['approved_bookings']) }}</h3>
                    <p>Approved bookings</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['part_payment_bookings']) }}</h3>
                    <p>Part-payment bookings</p>
                </div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $currency($stats['payments_total']) }}</h3>
                    <p>Total recorded payments</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Operations snapshot</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Pending / unset bookings</span>
                            <strong>{{ number_format($stats['pending_bookings']) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Declined bookings</span>
                            <strong>{{ number_format($stats['declined_bookings']) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Completed payment status</span>
                            <strong>{{ number_format($stats['completed_bookings']) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Staff users</span>
                            <strong>{{ number_format($stats['staff_users']) }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Recent bookings</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-valign-middle mb-0">
                        <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentBookings as $booking)
                            <tr>
                                <td>{{ $booking->bookign_id }}</td>
                                <td>{{ $booking->customer_fullname ?: 'Not set' }}</td>
                                <td>{{ $booking->event_type ?: 'Not set' }}</td>
                                <td>{{ $booking->date_start ?: 'Not set' }}</td>
                                <td>
                                    <span class="badge badge-{{ $booking->status === 'approved' ? 'success' : ($booking->status === 'declined' ? 'danger' : 'secondary') }}">
                                        {{ $booking->status ?: 'pending' }}
                                    </span>
                                </td>
                                <td>{{ $booking->payment_status ?: 'pending' }}</td>
                                <td>{{ $booking->total_amount ? '₦' . $booking->total_amount : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Import the event center database to populate the booking dashboard.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
