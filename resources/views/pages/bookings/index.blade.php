@extends('layouts.admin')

@section('title', $title)
@section('page_title', $pageTitle)

@php
    $currency = fn (float|int $amount) => 'NGN ' . number_format((float) $amount, 2);
    $userType = (int) (auth()->user()->type ?? 0);
@endphp

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $title }}</h3>
            <div class="card-tools">
                <a href="{{ route('bookings.create') }}" class="btn btn-success btn-sm">Book Now</a>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted">{{ $description }}</p>

            <div class="px-3 pb-3">
                <table class="table table-bordered table-hover js-data-table w-100">
                    <thead>
                    <tr>
                        <th class="dt-priority-1">Booking ID</th>
                        <th class="dt-priority-3">Event Type</th>
                        <th class="dt-priority-2">Date</th>
                        <th>Guests</th>
                        <th class="dt-priority-2">Status</th>
                        <th>Total Amount</th>
                        @if ($isBalancePage)
                            <th>Balance Due</th>
                        @endif
                        <th class="dt-actions" style="width: 210px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>#{{ $booking['bookign_id'] }}</td>
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
                            @if ($isBalancePage)
                                <td><span class="text-danger">{{ $currency($booking['balance_due']) }}</span></td>
                            @endif
                            <td>
                                <div class="dt-action-group">
                                <a href="{{ route('bookings.show', $booking['bookign_id']) }}" class="btn btn-primary btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if ($userType > 0)
                                    <a href="{{ route('bookings.edit', $booking['bookign_id']) }}" class="btn btn-info btn-sm" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                @endif

                                <a href="{{ route('bookings.show', ['bookingId' => $booking['bookign_id'], 'open' => 'approval']) }}" class="btn btn-success btn-sm" title="Approve or Decline">
                                    <i class="fas fa-check-circle"></i>
                                </a>

                                @if ($userType >= 5)
                                    <form action="{{ route('bookings.destroy', $booking['bookign_id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this booking permanently?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isBalancePage ? 7 : 6 }}" class="text-center text-muted py-4">
                                No booking records found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
