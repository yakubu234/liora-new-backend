@extends('layouts.admin')

@section('title', 'Booking Search')
@section('page_title', 'Booking Search')

@php
    $currency = fn (float|int $amount) => 'NGN ' . number_format((float) $amount, 2);
@endphp

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Search Bookings</h3>
        </div>
        <form method="GET" action="{{ route('bookings.search') }}">
            <div class="card-body">
                <p class="text-muted">
                    Search by booking code, customer email, customer phone, customer name, event type, or the displayed booking date text.
                </p>
                <div class="input-group">
                    <input
                        type="search"
                        name="q"
                        class="form-control"
                        value="{{ $query }}"
                        placeholder="Enter booking code, email, phone, customer name, event type, or date"
                    >
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">Search Results</h3>
            <div class="card-tools">
                <a href="{{ route('bookings.create') }}" class="btn btn-success btn-sm">Book Now</a>
            </div>
        </div>
        <div class="card-body px-3 pb-3">
            <table class="table table-hover mb-0 js-data-table w-100">
                <thead>
                <tr>
                    <th class="dt-priority-1">Booking ID</th>
                    <th class="dt-priority-2">Customer</th>
                    <th>Event Type</th>
                    <th class="dt-priority-2">Date</th>
                    <th>Guests</th>
                    <th class="dt-priority-3">Status</th>
                    <th>Total</th>
                    <th>Balance</th>
                    <th class="dt-actions" style="width: 120px;">Action</th>
                </tr>
                </thead>
                <tbody>
                @if ($query === '')
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Enter a search term to find bookings.</td>
                    </tr>
                @elseif ($bookings->isEmpty())
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No bookings matched your search.</td>
                    </tr>
                @else
                    @foreach ($bookings as $booking)
                        <tr>
                            <td>#{{ $booking['bookign_id'] }}</td>
                            <td>
                                <strong>{{ $booking['customer_fullname'] ?: 'N/A' }}</strong><br>
                                <span class="text-muted">{{ $booking['customer_email'] ?: ($booking['customer_phone'] ?: 'No contact detail') }}</span>
                            </td>
                            <td>{{ $booking['event_type'] }}</td>
                            <td>
                                <strong>{{ $booking['date_start'] }}</strong><br>
                                <span class="text-muted">{{ $booking['time_start'] }} to {{ $booking['time_end'] }}</span>
                            </td>
                            <td>{{ $booking['number_of_guest'] }}</td>
                            <td>
                                <span class="badge badge-{{ $booking['status'] === 'approved' ? 'success' : ($booking['status'] === 'declined' ? 'danger' : 'primary') }}">
                                    {{ $booking['status'] === 'active' ? 'Under Review' : $booking['status'] }}
                                </span>
                            </td>
                            <td>{{ $currency($booking['total_amount']) }}</td>
                            <td>
                                <span class="{{ $booking['balance_due'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $currency($booking['balance_due']) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('bookings.show', $booking['bookign_id']) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
