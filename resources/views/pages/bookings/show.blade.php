@extends('layouts.admin')

@section('title', 'Booking Detail')
@section('page_title', 'Booking Detail Breakdown')

@php
    $currency = fn (float|int $amount) => 'NGN ' . number_format((float) $amount, 2);
    $message = filled($booking['message'] ?? null) ? $booking['message'] : 'N/A';
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
            <h3 class="card-title">Booking {{ $booking['bookign_id'] }}</h3>
            <div class="card-tools">
                <a href="{{ route('bookings.create') }}" class="btn btn-success btn-sm">Make Booking</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th colspan="7" class="text-center">Preview of booking information</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th>Client Name</th>
                        <td colspan="3">{{ $booking['customer_fullname'] }}</td>
                        <th>Client Number</th>
                        <td colspan="2">{{ $booking['customer_phone'] }}</td>
                    </tr>
                    <tr>
                        <th>Client Email</th>
                        <td colspan="3">{{ $booking['customer_email'] }}</td>
                        <th>Customer Address</th>
                        <td colspan="2">{{ $booking['customer_address'] }}</td>
                    </tr>
                    <tr>
                        <th>Contact Person Name</th>
                        <td colspan="3">{{ $booking['customer_contact_person_fullname'] }}</td>
                        <th>Contact Person Phone</th>
                        <td colspan="2">{{ $booking['customer_contact_person_phone'] }}</td>
                    </tr>
                    <tr>
                        <th>Booking Status</th>
                        <td colspan="3">
                            <span class="badge badge-{{ $booking['status'] === 'approved' ? 'success' : ($booking['status'] === 'declined' ? 'danger' : 'secondary') }}">
                                {{ $booking['status'] }}
                            </span>
                        </td>
                        <th>Event Type</th>
                        <td colspan="2">{{ $booking['event_type'] }}</td>
                    </tr>
                    <tr>
                        <th>Booking Date</th>
                        <td colspan="3">{{ $booking['date_start'] }}</td>
                        <th>Time</th>
                        <td colspan="2">{{ $booking['time_start'] }} to {{ $booking['time_end'] }}</td>
                    </tr>
                    <tr>
                        <th>Number of Guests</th>
                        <td colspan="3">{{ $booking['number_of_guest'] }}</td>
                        <th>Apply Date</th>
                        <td colspan="2">{{ $booking['date_of_application'] }}</td>
                    </tr>
                    <tr>
                        <th>Payment Status</th>
                        <td colspan="3">{{ $booking['payment_status'] }}</td>
                        <th>Approved By</th>
                        <td colspan="2">{{ $booking['admin_id'] ?: 'Not approved yet' }}</td>
                    </tr>
                    <tr>
                        <th>Message</th>
                        <td colspan="6">{{ $message }}</td>
                    </tr>
                    <tr>
                        <th colspan="7"></th>
                    </tr>
                    @foreach ($booking['services'] as $service)
                        <tr>
                            <th colspan="2">Service Name: <b><i>{{ $service['name'] }}</i></b></th>
                            <th>Description</th>
                            <td>{{ $service['description'] }}</td>
                            <th>Price: {{ $currency($service['unit_price']) }}</th>
                            <th>Qty: {{ $service['quantity'] }}</th>
                            <th>Sub Total: {{ $currency($service['line_total']) }}</th>
                        </tr>
                    @endforeach
                    <tr>
                        <th colspan="4">
                            @if ($booking['can_approve'])
                                <button type="button" class="btn btn-danger mb-2" data-toggle="modal" data-target="#approvalModal">Approve Booking</button>
                            @endif

                            @if ($booking['can_add_balance'])
                                <button type="button" class="btn btn-success mb-2" data-toggle="modal" data-target="#balanceModal">Add Balance</button>
                            @endif

                            <a href="{{ route('bookings.audit', $booking['bookign_id']) }}" class="btn btn-primary mb-2">View History</a>

                            @if ($booking['can_generate_invoice'])
                                <a href="{{ route('bookings.receipt', $booking['bookign_id']) }}" target="_blank" rel="noopener" class="btn btn-outline-danger mb-2">View Receipt</a>
                            @endif
                        </th>
                        <th>Total</th>
                        <td colspan="2">{{ $currency($booking['final_total']) }}</td>
                    </tr>
                    <tr>
                        <th colspan="4"></th>
                        <th>Sub Total</th>
                        <td colspan="2">{{ $currency($booking['sub_total']) }}</td>
                    </tr>
                    <tr>
                        <th colspan="4"></th>
                        <th>Tax</th>
                        <td colspan="2">{{ $currency($booking['tax']) }}</td>
                    </tr>
                    <tr>
                        <th colspan="4"></th>
                        <th>Discount</th>
                        <td colspan="2">{{ $currency($booking['discount']) }}</td>
                    </tr>
                    <tr>
                        <th colspan="4"></th>
                        <th>Amount Paid</th>
                        <td colspan="2">{{ $currency($booking['amount_paid']) }}</td>
                    </tr>
                    <tr>
                        <th colspan="4"></th>
                        <th>To Balance</th>
                        <td colspan="2"><span class="text-danger">{{ $currency($booking['balance_due']) }}</span></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            @if (!empty($booking['payments']))
                <div class="mt-4">
                    <h5>Payment History</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($booking['payments'] as $payment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $payment['amount_display'] }}</td>
                                    <td>{{ $payment['created_at'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="balanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('bookings.balance.add', $booking['bookign_id']) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Balance</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">You can add either a partial amount or the full outstanding balance.</p>
                        <div class="form-group">
                            <label for="balance">Balance Amount</label>
                            <input type="number" class="form-control" id="balance" name="balance" min="0.01" step="0.01" required>
                        </div>
                        <div class="alert alert-light border mb-0">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Current balance</span>
                                <strong id="balance-current">{{ $currency($booking['balance_due']) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Amount entered</span>
                                <strong id="balance-entered">{{ $currency(0) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-0">
                                <span>Total to balance</span>
                                <strong id="balance-remaining" class="text-danger">{{ $currency($booking['balance_due']) }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Balance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="approvalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('bookings.approval.update', $booking['bookign_id']) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Booking</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Booking Type</label>
                            <input type="text" class="form-control" value="{{ $booking['event_type'] }}" readonly>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="text" class="form-control" value="{{ $booking['date_start'] }}" readonly>
                        </div>
                        <div class="form-group mb-0">
                            <label for="status">Select Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="active" @selected($booking['status'] === 'active')>active</option>
                                <option value="approved">approved</option>
                                <option value="declined">declined</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Save Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const currentBalance = {{ json_encode((float) $booking['balance_due']) }};
            const balanceInput = document.getElementById('balance');
            const enteredElement = document.getElementById('balance-entered');
            const remainingElement = document.getElementById('balance-remaining');

            function formatMoney(amount) {
                return `NGN ${Number(amount || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;
            }

            function updateBalancePreview() {
                if (!balanceInput || !enteredElement || !remainingElement) {
                    return;
                }

                const enteredAmount = Math.max(parseFloat(balanceInput.value || '0') || 0, 0);
                const remainingAmount = Math.max(currentBalance - enteredAmount, 0);

                enteredElement.textContent = formatMoney(enteredAmount);
                remainingElement.textContent = formatMoney(remainingAmount);
            }

            if (balanceInput) {
                balanceInput.addEventListener('input', updateBalancePreview);
                balanceInput.addEventListener('change', updateBalancePreview);
                updateBalancePreview();
            }

            @if (($openModal ?? null) === 'approval')
                $('#approvalModal').modal('show');
            @endif
        });

        @if ($errors->has('balance'))
            $(function () {
                $('#balanceModal').modal('show');
            });
        @endif

        @if ($errors->has('status'))
            $(function () {
                $('#approvalModal').modal('show');
            });
        @endif
    </script>
@endpush
