@extends('layouts.admin')

@section('title', 'Reports')
@section('page_title', 'Reports')

@php
    $currency = fn (float|int $amount) => 'NGN ' . number_format((float) $amount, 2);
    $metricHelp = [
        'total_bookings' => 'Count of all bookings created within the selected report date range.',
        'approved' => 'Bookings in the selected range where the booking status is marked as approved.',
        'under_review' => 'Bookings in the selected range still sitting in the active state, meaning they are under review and not yet approved or declined.',
        'declined' => 'Bookings in the selected range where the booking status has been marked as declined.',
        'outstanding' => 'Number of bookings in the selected range where the computed balance due is greater than zero.',
        'total_paid' => 'Sum of all payment records linked to the bookings in this report range.',
        'balance_due' => 'Combined outstanding amount remaining after subtracting recorded payments from each booking total.',
        'total_amount' => 'Combined booking value from the stored total amount on every booking returned in this report.',
    ];
@endphp

@push('styles')
    <style>
        .report-metric-box .inner {
            position: relative;
            z-index: 2;
            padding-right: 2.5rem;
        }

        .report-metric-box .inner h3 {
            font-size: clamp(1.2rem, 2vw, 1.9rem);
            line-height: 1.2;
            margin-bottom: 0.45rem;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        .report-metric-box .inner p {
            margin-bottom: 0;
            font-size: clamp(0.82rem, 1.5vw, 1rem);
            line-height: 1.35;
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
            max-width: 100%;
        }

        .report-metric-box .icon {
            z-index: 1;
            font-size: 44px;
            top: 16px;
            right: 14px;
        }

        .metric-help {
            position: absolute;
            top: 0.15rem;
            right: 0;
            z-index: 3;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.7rem;
            height: 1.7rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
            cursor: pointer;
            font-size: 0.9rem;
        }

        @media (max-width: 767.98px) {
            .report-metric-box .small-box,
            .report-metric-box.small-box {
                min-height: 150px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Booking Reports</h3>
        </div>
        <form method="GET" action="{{ route('reports.index') }}">
            <div class="card-body">
                <p class="text-muted">
                    Filter bookings by creation date range to review totals, payments received, and outstanding balances.
                </p>
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-block">Run Report</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-lg col-md-4 col-sm-6">
            <div class="small-box bg-info report-metric-box">
                <div class="inner">
                    <span class="metric-help" data-toggle="tooltip" data-placement="top" title="{{ $metricHelp['total_bookings'] }}">
                        <i class="fas fa-circle-info"></i>
                    </span>
                    <h3>{{ $summary['total_bookings'] }}</h3>
                    <p>Total Bookings</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-check"></i></div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-sm-6">
            <div class="small-box bg-primary report-metric-box">
                <div class="inner">
                    <span class="metric-help" data-toggle="tooltip" data-placement="top" title="{{ $metricHelp['approved'] }}">
                        <i class="fas fa-circle-info"></i>
                    </span>
                    <h3>{{ $summary['approved'] }}</h3>
                    <p>Approved</p>
                </div>
                <div class="icon"><i class="fas fa-circle-check"></i></div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-sm-6">
            <div class="small-box bg-secondary report-metric-box">
                <div class="inner">
                    <span class="metric-help" data-toggle="tooltip" data-placement="top" title="{{ $metricHelp['under_review'] }}">
                        <i class="fas fa-circle-info"></i>
                    </span>
                    <h3>{{ $summary['under_review'] }}</h3>
                    <p>Under Review</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-sm-6">
            <div class="small-box bg-dark report-metric-box">
                <div class="inner">
                    <span class="metric-help" data-toggle="tooltip" data-placement="top" title="{{ $metricHelp['declined'] }}">
                        <i class="fas fa-circle-info"></i>
                    </span>
                    <h3>{{ $summary['declined'] }}</h3>
                    <p>Declined</p>
                </div>
                <div class="icon"><i class="fas fa-ban"></i></div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-sm-6">
            <div class="small-box bg-warning report-metric-box">
                <div class="inner">
                    <span class="metric-help" data-toggle="tooltip" data-placement="top" title="{{ $metricHelp['outstanding'] }}">
                        <i class="fas fa-circle-info"></i>
                    </span>
                    <h3>{{ $summary['outstanding'] }}</h3>
                    <p>Bookings With Balance</p>
                </div>
                <div class="icon"><i class="fas fa-scale-balanced"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="small-box bg-success report-metric-box">
                <div class="inner">
                    <span class="metric-help" data-toggle="tooltip" data-placement="top" title="{{ $metricHelp['total_paid'] }}">
                        <i class="fas fa-circle-info"></i>
                    </span>
                    <h3>{{ $currency($summary['total_paid']) }}</h3>
                    <p>Total Recorded Payments</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <div class="small-box bg-danger report-metric-box">
                <div class="inner">
                    <span class="metric-help" data-toggle="tooltip" data-placement="top" title="{{ $metricHelp['balance_due'] }}">
                        <i class="fas fa-circle-info"></i>
                    </span>
                    <h3>{{ $currency($summary['balance_due']) }}</h3>
                    <p>Total Balance Due</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 col-sm-12">
            <div class="small-box bg-teal report-metric-box">
                <div class="inner">
                    <span class="metric-help" data-toggle="tooltip" data-placement="top" title="{{ $metricHelp['total_amount'] }}">
                        <i class="fas fa-circle-info"></i>
                    </span>
                    <h3>{{ $currency($summary['total_amount']) }}</h3>
                    <p>Total Booking Value</p>
                </div>
                <div class="icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">Report Results</h3>
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
                    <th class="dt-priority-3">Status</th>
                    <th>Total Amount</th>
                    <th>Amount Paid</th>
                    <th>Balance</th>
                    <th class="dt-actions" style="width: 120px;">Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($bookings as $booking)
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
                        <td>
                            <span class="badge badge-{{ $booking['status'] === 'approved' ? 'success' : ($booking['status'] === 'declined' ? 'danger' : 'primary') }}">
                                {{ $booking['status'] === 'active' ? 'Under Review' : $booking['status'] }}
                            </span>
                        </td>
                        <td>{{ $currency($booking['total_amount']) }}</td>
                        <td>{{ $currency($booking['amount_paid']) }}</td>
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
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No bookings were found for this report range.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip({
                container: 'body',
                trigger: 'hover focus'
            });
        });
    </script>
@endpush
