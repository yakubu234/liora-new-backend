@extends('layouts.admin')

@section('title', 'Booking History')
@section('page_title', 'Booking Activity History')

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Audit Trail for {{ $booking['bookign_id'] }}</h3>
            <div class="card-tools">
                <a href="{{ route('bookings.show', $booking['bookign_id']) }}" class="btn btn-outline-primary btn-sm">Back to Booking</a>
            </div>
        </div>
        <div class="card-body px-3 pb-3">
                <table class="table table-striped table-bordered mb-0 js-data-table w-100">
                    <thead>
                    <tr>
                        <th class="dt-priority-1">#</th>
                        <th class="dt-priority-2">Name</th>
                        <th>Email</th>
                        <th class="dt-priority-1">Action</th>
                        <th>Booking ID</th>
                        <th class="dt-priority-2">Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($audits as $audit)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $audit->user_name }}</td>
                            <td>{{ $audit->user_email }}</td>
                            <td>{{ $audit->action }}</td>
                            <td>{{ $audit->booking_id }}</td>
                            <td>{{ $audit->created_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No audit records for this booking yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
        </div>
    </div>
@endsection
