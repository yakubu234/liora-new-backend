@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Booking Dashboard')

@php
    $currency = fn (float|int|string $amount) => 'NGN ' . number_format((float) preg_replace('/[^\d.\-]/', '', (string) $amount), 2);
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fullcalendar/main.min.css') }}">
    <style>
        .dashboard-calendar-shell {
            min-height: 100%;
        }

        .dashboard-calendar-shell .fc .fc-button-primary {
            background: #1f6feb;
            border-color: #1f6feb;
        }

        .dashboard-calendar-shell .fc .fc-toolbar.fc-header-toolbar {
            margin-bottom: 1rem;
        }

        .dashboard-calendar-shell .fc-daygrid-event {
            cursor: pointer;
        }

        .booking-agenda {
            border-left: 1px solid #e9ecef;
            min-height: 100%;
        }

        .booking-agenda-scroll {
            max-height: 640px;
            overflow-y: auto;
            padding-right: 0.35rem;
        }

        .booking-agenda-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .booking-agenda-scroll::-webkit-scrollbar-thumb {
            background: #c7d0d9;
            border-radius: 999px;
        }

        .agenda-date {
            font-size: 0.95rem;
            color: #6c757d;
        }

        .agenda-item {
            border: 1px solid #dee2e6;
            border-radius: 0.85rem;
            padding: 0.9rem 1rem;
            margin-bottom: 0.85rem;
            background: #fff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        }

        .agenda-item:last-child {
            margin-bottom: 0;
        }

        .agenda-empty {
            border: 1px dashed #ced4da;
            border-radius: 0.85rem;
            padding: 1.25rem;
            text-align: center;
            color: #6c757d;
            background: #fafbfc;
        }

        @media (max-width: 991.98px) {
            .booking-agenda {
                border-left: 0;
                border-top: 1px solid #e9ecef;
                margin-top: 1.5rem;
                padding-top: 1.5rem;
            }

            .booking-agenda-scroll {
                max-height: none;
                overflow: visible;
                padding-right: 0;
            }
        }
    </style>
@endpush

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

        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ number_format($stats['pending_bookings']) }}</h3>
                    <p>Under review bookings</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['declined_bookings']) }}</h3>
                    <p>Declined bookings</p>
                </div>
                <div class="icon"><i class="fas fa-ban"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3>{{ number_format($stats['completed_bookings']) }}</h3>
                    <p>Completed payments</p>
                </div>
                <div class="icon"><i class="fas fa-check-double"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-indigo">
                <div class="inner">
                    <h3>{{ number_format($stats['staff_users']) }}</h3>
                    <p>Staff users</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary dashboard-calendar-shell">
                <div class="card-header">
                    <h3 class="card-title">Bookings Calendar</h3>
                    <div class="card-tools">
                        <a href="{{ route('bookings.history') }}" class="btn btn-primary btn-sm">View All Bookings</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-8 col-lg-7 col-12">
                            <div id="dashboard-booking-calendar"></div>
                        </div>
                        <div class="col-xl-4 col-lg-5 col-12 booking-agenda">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">Bookings</h5>
                                    <div class="agenda-date" id="dashboard-agenda-date">Current month</div>
                                </div>
                            </div>
                            <div class="booking-agenda-scroll">
                                <div id="dashboard-agenda-list"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Recent bookings</h3>
                </div>
                <div class="card-body px-3 pb-3">
                    <table class="table table-striped table-valign-middle mb-0 js-data-table w-100">
                        <thead>
                        <tr>
                            <th class="dt-priority-1">Booking ID</th>
                            <th class="dt-priority-2">Customer</th>
                            <th>Event</th>
                            <th class="dt-priority-3">Date</th>
                            <th class="dt-priority-3">Status</th>
                            <th>Payment</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentBookings as $booking)
                            <tr>
                                <td>
                                    <a href="{{ route('bookings.show', $booking->bookign_id) }}">{{ $booking->bookign_id }}</a>
                                </td>
                                <td>{{ $booking->customer_fullname ?: 'Not set' }}</td>
                                <td>{{ $booking->event_type ?: 'Not set' }}</td>
                                <td>{{ $booking->date_start ?: 'Not set' }}</td>
                                <td>
                                    <span class="badge badge-{{ $booking->status === 'approved' ? 'success' : ($booking->status === 'declined' ? 'danger' : 'secondary') }}">
                                        {{ $booking->status ?: 'pending' }}
                                    </span>
                                </td>
                                <td>{{ $booking->payment_status ?: 'pending' }}</td>
                                <td>{{ $booking->total_amount ? $currency($booking->total_amount) : 'N/A' }}</td>
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

@push('scripts')
    <script src="{{ asset('vendor/adminlte/plugins/fullcalendar/main.min.js') }}"></script>
    <script>
        const calendarBookings = @json($calendarBookings);
        const agendaList = document.getElementById('dashboard-agenda-list');
        const agendaDate = document.getElementById('dashboard-agenda-date');

        function normalizeStatus(status) {
            if (!status || status === 'active') {
                return 'Under Review';
            }

            return status;
        }

        function formatMoney(value) {
            const amount = Number.parseFloat(String(value ?? 0).replace(/[^\d.-]/g, '')) || 0;
            return `NGN ${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        function renderAgenda(bookings, label) {
            agendaDate.textContent = label;

            if (!bookings.length) {
                agendaList.innerHTML = '<div class="agenda-empty">No bookings found for this selection.</div>';
                return;
            }

            agendaList.innerHTML = bookings.map((booking) => `
                <div class="agenda-item">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong>${booking.event_type}</strong>
                        <span class="badge badge-${booking.status === 'approved' ? 'success' : (booking.status === 'declined' ? 'danger' : 'secondary')}">${normalizeStatus(booking.status)}</span>
                    </div>
                    <div class="small text-muted mb-1">${booking.customer}</div>
                    <div class="small mb-1">${booking.date_label}</div>
                    <div class="small mb-2">${booking.time}</div>
                    <div class="small mb-3">Payment: ${booking.payment_status}</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>${formatMoney(booking.total_amount)}</strong>
                        <a href="${booking.url}" class="btn btn-outline-primary btn-sm">Open</a>
                    </div>
                </div>
            `).join('');
        }

        function bookingsForDate(dateStr) {
            return calendarBookings.filter((booking) => booking.start === dateStr);
        }

        function bookingsForMonth(date) {
            const year = date.getFullYear();
            const month = date.getMonth();

            return calendarBookings.filter((booking) => {
                const bookingDate = new Date(`${booking.start}T00:00:00`);
                return bookingDate.getFullYear() === year && bookingDate.getMonth() === month;
            });
        }

        function monthLabel(date) {
            return date.toLocaleDateString(undefined, {
                month: 'long',
                year: 'numeric'
            });
        }

        function dateLabel(dateStr) {
            return new Date(`${dateStr}T00:00:00`).toLocaleDateString(undefined, {
                weekday: 'long',
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
        }

        const dashboardCalendar = new FullCalendar.Calendar(document.getElementById('dashboard-booking-calendar'), {
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            events: calendarBookings.map((booking) => ({
                title: booking.title,
                start: booking.start,
                url: booking.url,
                extendedProps: booking
            })),
            datesSet(info) {
                renderAgenda(bookingsForMonth(info.view.currentStart), monthLabel(info.view.currentStart));
            },
            dateClick(info) {
                renderAgenda(bookingsForDate(info.dateStr), dateLabel(info.dateStr));
            },
            eventClick(info) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        });

        dashboardCalendar.render();
    </script>
@endpush
