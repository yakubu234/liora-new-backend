@extends('layouts.admin')

@section('title', isset($booking) ? 'Edit Booking' : 'Book Now')
@section('page_title', isset($booking) ? 'Edit Booking' : 'Book Now')

@php
    $oldStep = $errors->has('services') || $errors->has('quantities') || $errors->has('discount') || $errors->has('amount_paid')
        ? 2
        : ($errors->has('terms') ? 3 : 1);
    $formValues = $booking['form'] ?? [];
    $value = fn (string $key, $default = '') => old($key, $formValues[$key] ?? $default);
    $selectedServices = old('services', $formValues['services'] ?? []);
    $selectedQuantities = old('quantities', $formValues['quantities'] ?? []);
    $isEditMode = isset($booking);
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fullcalendar/main.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <style>
        .booking-step-nav {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .booking-step-chip {
            min-width: 150px;
            padding: 0.9rem 1rem;
            border-radius: 0.85rem;
            background: #f4f6f9;
            border: 1px solid #d7dde4;
            color: #5b6570;
            transition: all 0.2s ease;
        }

        .booking-step-chip.active {
            background: #1f6feb;
            border-color: #1f6feb;
            color: #fff;
            box-shadow: 0 12px 26px rgba(31, 111, 235, 0.18);
        }

        .booking-step-chip span {
            display: inline-flex;
            width: 2rem;
            height: 2rem;
            border-radius: 999px;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.18);
            margin-right: 0.65rem;
            font-weight: 700;
        }

        .booking-step-chip:not(.active) span {
            background: #fff;
            color: #1f6feb;
            border: 1px solid #d7dde4;
        }

        .wizard-step {
            display: none;
        }

        .wizard-step.active {
            display: block;
        }

        .fc .fc-toolbar.fc-header-toolbar {
            margin-bottom: 0.75rem;
        }

        .fc .fc-button-primary {
            background: #1f6feb;
            border-color: #1f6feb;
        }

        .fc .fc-daygrid-day.booked-date {
            background: #eef1f4;
        }

        .fc .fc-daygrid-day.booked-date .fc-daygrid-day-number {
            color: #c0392b;
            text-decoration: line-through;
            font-weight: 700;
        }

        .fc .fc-daygrid-day.selected-date {
            background: #e8f1ff;
            box-shadow: inset 0 0 0 2px #1f6feb;
        }

        .booking-summary-card {
            background: linear-gradient(135deg, #0d3b66, #1f6feb);
            color: #fff;
            border-radius: 1rem;
        }

        .booking-summary-card .summary-line {
            display: flex;
            justify-content: space-between;
            padding: 0.45rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
        }

        .booking-summary-card .summary-line:last-child {
            border-bottom: 0;
            margin-top: 0.35rem;
            padding-top: 0.75rem;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .selected-date-badge {
            min-height: 48px;
            border: 1px dashed #1f6feb;
            border-radius: 0.8rem;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            background: #f8fbff;
            font-weight: 600;
            color: #1f2d3d;
        }

        .service-empty {
            border: 1px dashed #ced4da;
            border-radius: 0.85rem;
            padding: 1rem;
            text-align: center;
            color: #6c757d;
            background: #fafbfc;
        }

        .preview-table th {
            width: 28%;
            background: #f8f9fa;
        }

        .timepicker-wrapper .input-group-text {
            background: #1f6feb;
            color: #fff;
            border-color: #1f6feb;
        }

        .timepicker-wrapper .form-control[readonly] {
            background: #fff;
            cursor: pointer;
        }

        .timepicker-wrapper .bootstrap-datetimepicker-widget.dropdown-menu {
            left: 0 !important;
            right: auto !important;
        }

        .timepicker-ok-wrap {
            padding: 0.5rem 0.75rem 0.75rem;
            border-top: 1px solid #e9ecef;
            text-align: right;
        }

        .timepicker-ok-wrap .btn {
            min-width: 84px;
        }

        .fc .fc-daygrid-day.past-date:not(.booked-date) {
            background: #f6f6f6;
            opacity: 0.65;
        }

        .fc .fc-daygrid-day.past-date:not(.booked-date) .fc-daygrid-day-number {
            color: #9aa1a9;
        }

        @media (max-width: 767.98px) {
            .booking-step-chip {
                width: 100%;
            }
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
            <h5 class="mb-2">Please correct the highlighted booking details.</h5>
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ $isEditMode ? 'Edit Booking Wizard' : 'Booking Wizard' }}</h3>
            <div class="card-tools text-muted">
                {{ $isEditMode ? 'Update the booking details, services, and payment figures.' : 'Capture customer details, attach services, preview, and submit.' }}
            </div>
        </div>

        <form action="{{ $isEditMode ? route('bookings.update', $booking['bookign_id']) : route('bookings.store') }}" method="POST" id="bookingWizardForm" novalidate>
            @csrf
            @if ($isEditMode)
                @method('PUT')
            @endif
            <input type="hidden" name="tax" value="{{ $value('tax', 0) }}">

            <div class="card-body">
                <div class="booking-step-nav">
                    <div class="booking-step-chip" data-step-chip="1">
                        <span>1</span> Bio Data & Schedule
                    </div>
                    <div class="booking-step-chip" data-step-chip="2">
                        <span>2</span> Services & Payment
                    </div>
                    <div class="booking-step-chip" data-step-chip="3">
                        <span>3</span> Preview & Submit
                    </div>
                </div>

                <section class="wizard-step" data-step="1">
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">Customer Details</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="customer_email">Customer Email</label>
                                            <input type="email" class="form-control" id="customer_email" name="customer_email" value="{{ $value('customer_email') }}" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="customer_fullname">Customer Full Name</label>
                                            <input type="text" class="form-control" id="customer_fullname" name="customer_fullname" value="{{ $value('customer_fullname') }}" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="customer_phone">Customer Phone</label>
                                            <input type="text" class="form-control" id="customer_phone" name="customer_phone" value="{{ $value('customer_phone') }}" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="customer_address">Customer Address</label>
                                            <input type="text" class="form-control" id="customer_address" name="customer_address" value="{{ $value('customer_address') }}" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="customer_contact_person_fullname">Contact Person Full Name</label>
                                            <input type="text" class="form-control" id="customer_contact_person_fullname" name="customer_contact_person_fullname" value="{{ $value('customer_contact_person_fullname') }}" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="customer_contact_person_phone">Contact Person Phone</label>
                                            <input type="text" class="form-control" id="customer_contact_person_phone" name="customer_contact_person_phone" value="{{ $value('customer_contact_person_phone') }}" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="event_type">Event Type</label>
                                            <select class="form-control" id="event_type" name="event_type" required>
                                                <option value="">Select event type</option>
                                                @foreach ($eventTypes as $eventType)
                                                    <option value="{{ $eventType->name }}" @selected($value('event_type') === $eventType->name)>{{ $eventType->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="number_of_guest">Number of Guests</label>
                                            <input type="number" min="1" class="form-control" id="number_of_guest" name="number_of_guest" value="{{ $value('number_of_guest') }}" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="start_time">Start Time</label>
                                            <div class="input-group date timepicker-wrapper" id="start_time_picker" data-target-input="nearest">
                                                <input
                                                    type="text"
                                                    class="form-control datetimepicker-input"
                                                    id="start_time"
                                                    name="start_time"
                                                    data-target="#start_time_picker"
                                                    value="{{ $value('start_time') }}"
                                                    placeholder="Select start time"
                                                    autocomplete="off"
                                                    readonly
                                                    required
                                                >
                                                <div class="input-group-append" data-target="#start_time_picker" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="end_time">End Time</label>
                                            <div class="input-group date timepicker-wrapper" id="end_time_picker" data-target-input="nearest">
                                                <input
                                                    type="text"
                                                    class="form-control datetimepicker-input"
                                                    id="end_time"
                                                    name="end_time"
                                                    data-target="#end_time_picker"
                                                    value="{{ $value('end_time') }}"
                                                    placeholder="Select end time"
                                                    autocomplete="off"
                                                    readonly
                                                    required
                                                >
                                                <div class="input-group-append" data-target="#end_time_picker" data-toggle="datetimepicker">
                                                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">Booking Date</h3>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">
                                        Click any future available day to select it. Already booked dates are greyed out and crossed in red, and clicking them opens the booking details.
                                    </p>

                                    <input type="hidden" id="booking_date" name="booking_date" value="{{ $value('booking_date') }}">

                                    <div class="row">
                                        <div class="col-lg-8">
                                            <div class="selected-date-badge mb-3" id="selected-date-display">
                                                {{ $value('booking_date') ? \Carbon\Carbon::parse($value('booking_date'))->format('l, F j, Y') : 'No date selected yet.' }}
                                            </div>

                                            <div class="alert alert-danger d-none" id="date-error-banner">
                                                That day is already booked. Please pick another available date.
                                            </div>

                                            <div id="booking-calendar"></div>
                                        </div>

                                        <div class="col-lg-4 mt-4 mt-lg-0">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge badge-secondary mr-2">&nbsp;</span>
                                                <small>Booked date</small>
                                            </div>
                                            <div class="d-flex align-items-center mb-3">
                                                <span class="badge badge-primary mr-2">&nbsp;</span>
                                                <small>Selected date</small>
                                            </div>

                                            @if (count($bookedDates) > 0)
                                                <h6 class="mb-2">Unavailable dates</h6>
                                                <div class="border rounded p-2" style="max-height: 320px; overflow:auto;">
                                                    @foreach ($bookedDates as $bookedDate)
                                                        <div class="small mb-2">
                                                            <strong>{{ $bookedDate['label'] }}</strong><br>
                                                            <span class="text-muted">{{ $bookedDate['event_type'] }} for {{ $bookedDate['customer'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="alert alert-light border mb-0">
                                                    No blocked booking dates were found yet.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="wizard-step" data-step="2">
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">Select Services</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-end">
                                        <div class="col-md-9 form-group">
                                            <label for="service-select">Available Services</label>
                                            <select id="service-select" class="form-control">
                                                <option value="">Choose a service</option>
                                                @foreach ($services as $service)
                                                    <option value="{{ $service['id'] }}">{{ $service['name'] }} - {{ $service['price_display'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <button type="button" class="btn btn-primary btn-block" id="add-service-btn">
                                                Add Service
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th style="width: 70px;">S/N</th>
                                                    <th>Name</th>
                                                    <th style="width: 150px;">Unit Price</th>
                                                    <th style="width: 130px;">Quantity</th>
                                                    <th style="width: 150px;">Total Price</th>
                                                    <th style="width: 90px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="service-table-body"></tbody>
                                        </table>
                                    </div>

                                    <div class="service-empty mt-3" id="service-empty-state">
                                        No services selected yet.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 order-2 order-lg-1">
                            <div class="booking-summary-card p-4">
                                <h4 class="mb-4">Totals</h4>
                                <div class="summary-line">
                                    <span>Sub total</span>
                                    <strong id="summary-subtotal">NGN 0.00</strong>
                                </div>
                                <div class="summary-line">
                                    <span>Tax</span>
                                    <strong id="summary-tax">NGN 0.00</strong>
                                </div>
                                <div class="summary-line">
                                    <span>Discount</span>
                                    <strong id="summary-discount">NGN 0.00</strong>
                                </div>
                                <div class="summary-line">
                                    <span>Total overall</span>
                                    <strong id="summary-total">NGN 0.00</strong>
                                </div>
                                <div class="summary-line">
                                    <span>Amount paid</span>
                                    <strong id="summary-paid">NGN 0.00</strong>
                                </div>
                                <div class="summary-line">
                                    <span>To balance</span>
                                    <strong id="summary-balance">NGN 0.00</strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8 order-1 order-lg-2">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">Additional Notes & Payment Entry</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="discount">Discount (if any)</label>
                                            <input type="number" min="0" step="0.01" class="form-control" id="discount" name="discount" value="{{ $value('discount') }}">
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="amount_paid">Amount User Is Paying Now</label>
                                            <input type="number" min="0" step="0.01" class="form-control" id="amount_paid" name="amount_paid" value="{{ $value('amount_paid') }}">
                                        </div>
                                        <div class="col-12 form-group mb-0">
                                            <label for="message">Message (optional)</label>
                                            <textarea class="form-control" rows="4" id="message" name="message">{{ $value('message') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="wizard-step" data-step="3">
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">Booking Preview</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered preview-table mb-4">
                                        <tbody id="preview-customer-body"></tbody>
                                    </table>

                                    <h5 class="mb-3">Selected Services</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>S/N</th>
                                                    <th>Service</th>
                                                    <th>Unit Price</th>
                                                    <th>Quantity</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody id="preview-service-body"></tbody>
                                        </table>
                                    </div>

                                    <table class="table table-bordered mt-4 mb-0 preview-table">
                                        <tbody id="preview-totals-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">Terms & Confirmation</h3>
                                </div>
                                <div class="card-body">
                                    <div class="border rounded p-3 bg-light mb-3" style="max-height: 320px; overflow:auto;">
                                        {!! $agreementText !!}
                                    </div>

                                    <div class="custom-control custom-checkbox">
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            id="terms"
                                            name="terms"
                                            value="1"
                                            required
                                            @checked(old('terms'))
                                        >
                                        <label class="custom-control-label" for="terms">
                                            I agree to the terms and conditions, and I confirm the booking preview is correct.
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="card-footer d-flex justify-content-between flex-wrap">
                <button type="button" class="btn btn-outline-secondary mb-2" id="prev-step-btn">Previous</button>
                <div class="ml-auto">
                    <button type="button" class="btn btn-primary mb-2" id="next-step-btn">Proceed</button>
                    <button type="submit" class="btn btn-success mb-2 d-none" id="submit-booking-btn">{{ $isEditMode ? 'Update Booking' : 'Submit Booking' }}</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/adminlte/plugins/moment/moment-with-locales.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/plugins/fullcalendar/main.min.js') }}"></script>
    <script>
        const services = @json($services->values());
        const bookedDates = @json($bookedDates);
        const initialSelectedServices = @json($selectedServices);
        const initialQuantities = @json($selectedQuantities);
        const initialStep = {{ $oldStep }};
        const serverErrorKeys = @json($errors->keys());
        const todayKey = '{{ now()->toDateString() }}';

        const serviceMap = new Map(services.map((service) => [String(service.id), service]));
        const bookedDateMap = new Map(bookedDates.map((item) => [item.date, item]));
        const bookedDateSet = new Set(bookedDates.map((item) => item.date));

        const form = document.getElementById('bookingWizardForm');
        const steps = Array.from(document.querySelectorAll('.wizard-step'));
        const stepChips = Array.from(document.querySelectorAll('[data-step-chip]'));
        const prevStepBtn = document.getElementById('prev-step-btn');
        const nextStepBtn = document.getElementById('next-step-btn');
        const submitBookingBtn = document.getElementById('submit-booking-btn');
        const serviceSelect = document.getElementById('service-select');
        const addServiceBtn = document.getElementById('add-service-btn');
        const serviceTableBody = document.getElementById('service-table-body');
        const serviceEmptyState = document.getElementById('service-empty-state');
        const selectedDateDisplay = document.getElementById('selected-date-display');
        const bookingDateInput = document.getElementById('booking_date');
        const dateErrorBanner = document.getElementById('date-error-banner');
        const termsCheckbox = document.getElementById('terms');
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');
        let calendarInitialized = false;

        let currentStep = initialStep;

        function parseMoney(value) {
            return Number.parseFloat(String(value ?? 0).replace(/[^\d.-]/g, '')) || 0;
        }

        function formatMoney(value) {
            return `NGN ${parseMoney(value).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`;
        }

        function toDisplayDate(dateString) {
            if (!dateString) {
                return 'No date selected yet.';
            }

            const date = new Date(`${dateString}T00:00:00`);
            return date.toLocaleDateString(undefined, {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        function toDateKey(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function parseTimeValue(value) {
            const parsed = moment(value, 'hh:mm A', true);
            return parsed.isValid() ? parsed : null;
        }

        function scrollToElement(element) {
            if (!element) {
                return;
            }

            window.setTimeout(() => {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                if (typeof element.focus === 'function') {
                    element.focus({
                        preventScroll: true
                    });
                }
            }, 50);
        }

        function scrollToStepTop(stepNumber) {
            const activeStep = document.querySelector(`.wizard-step[data-step="${stepNumber}"]`);

            if (!activeStep) {
                return;
            }

            window.setTimeout(() => {
                activeStep.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 30);
        }

        function getSelectedRows() {
            return Array.from(serviceTableBody.querySelectorAll('tr')).map((row) => {
                const serviceId = row.dataset.serviceId;
                const quantityInput = row.querySelector('.service-qty-input');
                const service = serviceMap.get(serviceId);
                const quantity = Math.max(parseInt(quantityInput.value || '1', 10), 1);
                const unitPrice = parseMoney(service.price);

                return {
                    id: serviceId,
                    name: service.name,
                    description: service.description,
                    quantity,
                    unitPrice,
                    total: unitPrice * quantity
                };
            });
        }

        function syncSerialNumbers() {
            Array.from(serviceTableBody.querySelectorAll('tr')).forEach((row, index) => {
                row.querySelector('.service-sn').textContent = index + 1;
            });
        }

        function updateEmptyState() {
            serviceEmptyState.classList.toggle('d-none', serviceTableBody.children.length > 0);
        }

        function updateRowTotal(row) {
            const service = serviceMap.get(row.dataset.serviceId);
            const qtyInput = row.querySelector('.service-qty-input');
            const hiddenQtyInput = row.querySelector('.service-hidden-qty');
            const quantity = Math.max(parseInt(qtyInput.value || '1', 10), 1);
            qtyInput.value = quantity;
            hiddenQtyInput.value = quantity;
            row.querySelector('.service-total-cell').textContent = formatMoney(parseMoney(service.price) * quantity);
        }

        function recalculateTotals() {
            const rows = getSelectedRows();
            const subTotal = rows.reduce((sum, row) => sum + row.total, 0);
            const tax = parseMoney(document.querySelector('input[name="tax"]').value);
            const discount = parseMoney(document.getElementById('discount').value);
            const amountPaid = parseMoney(document.getElementById('amount_paid').value);
            const total = Math.max((subTotal + tax) - discount, 0);
            const balance = Math.max(total - amountPaid, 0);

            document.getElementById('summary-subtotal').textContent = formatMoney(subTotal);
            document.getElementById('summary-tax').textContent = formatMoney(tax);
            document.getElementById('summary-discount').textContent = formatMoney(discount);
            document.getElementById('summary-total').textContent = formatMoney(total);
            document.getElementById('summary-paid').textContent = formatMoney(amountPaid);
            document.getElementById('summary-balance').textContent = formatMoney(balance);

            return { rows, subTotal, tax, discount, amountPaid, total, balance };
        }

        function addServiceRow(serviceId, quantity = 1) {
            const normalizedId = String(serviceId);
            const service = serviceMap.get(normalizedId);

            if (!service) {
                return;
            }

            if (serviceTableBody.querySelector(`tr[data-service-id="${normalizedId}"]`)) {
                window.alert('That service has already been added.');
                return;
            }

            const row = document.createElement('tr');
            row.dataset.serviceId = normalizedId;
            row.innerHTML = `
                <td class="service-sn"></td>
                <td>
                    <strong>${service.name}</strong>
                    <div class="text-muted small">${service.description ?? ''}</div>
                    <input type="hidden" name="services[]" value="${service.id}">
                </td>
                <td>${formatMoney(service.price)}</td>
                <td>
                    <input type="hidden" name="quantities[]" class="service-hidden-qty" value="${quantity}">
                    <input type="number" min="1" value="${quantity}" class="form-control service-qty-input">
                </td>
                <td class="service-total-cell"></td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm service-remove-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

            serviceTableBody.appendChild(row);
            updateRowTotal(row);
            syncSerialNumbers();
            updateEmptyState();
            recalculateTotals();
        }

        function renderPreview() {
            const totals = recalculateTotals();
            const previewCustomerBody = document.getElementById('preview-customer-body');
            const previewServiceBody = document.getElementById('preview-service-body');
            const previewTotalsBody = document.getElementById('preview-totals-body');

            previewCustomerBody.innerHTML = `
                <tr><th>Customer Full Name</th><td>${document.getElementById('customer_fullname').value || 'N/A'}</td></tr>
                <tr><th>Customer Email</th><td>${document.getElementById('customer_email').value || 'N/A'}</td></tr>
                <tr><th>Customer Phone</th><td>${document.getElementById('customer_phone').value || 'N/A'}</td></tr>
                <tr><th>Customer Address</th><td>${document.getElementById('customer_address').value || 'N/A'}</td></tr>
                <tr><th>Contact Person</th><td>${document.getElementById('customer_contact_person_fullname').value || 'N/A'}</td></tr>
                <tr><th>Contact Person Phone</th><td>${document.getElementById('customer_contact_person_phone').value || 'N/A'}</td></tr>
                <tr><th>Event Type</th><td>${document.getElementById('event_type').value || 'N/A'}</td></tr>
                <tr><th>Number of Guests</th><td>${document.getElementById('number_of_guest').value || 'N/A'}</td></tr>
                <tr><th>Booking Date</th><td>${toDisplayDate(bookingDateInput.value)}</td></tr>
                <tr><th>Time</th><td>${document.getElementById('start_time').value || 'N/A'} to ${document.getElementById('end_time').value || 'N/A'}</td></tr>
                <tr><th>Message</th><td>${document.getElementById('message').value || 'N/A'}</td></tr>
            `;

            previewServiceBody.innerHTML = totals.rows.map((row, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td>${row.name}</td>
                    <td>${formatMoney(row.unitPrice)}</td>
                    <td>${row.quantity}</td>
                    <td>${formatMoney(row.total)}</td>
                </tr>
            `).join('');

            previewTotalsBody.innerHTML = `
                <tr><th>Sub total</th><td>${formatMoney(totals.subTotal)}</td></tr>
                <tr><th>Tax</th><td>${formatMoney(totals.tax)}</td></tr>
                <tr><th>Discount</th><td>${formatMoney(totals.discount)}</td></tr>
                <tr><th>Total overall</th><td>${formatMoney(totals.total)}</td></tr>
                <tr><th>Amount paid</th><td>${formatMoney(totals.amountPaid)}</td></tr>
                <tr><th>To balance</th><td>${formatMoney(totals.balance)}</td></tr>
            `;
        }

        function updateSelectedDateUI() {
            selectedDateDisplay.textContent = toDisplayDate(bookingDateInput.value);

            document.querySelectorAll('#booking-calendar [data-date]').forEach((cell) => {
                cell.classList.remove('selected-date');

                if (cell.dataset.date === bookingDateInput.value) {
                    cell.classList.add('selected-date');
                }
            });
        }

        function validateStep(stepNumber) {
            if (stepNumber === 1) {
                const stepOneInputs = [
                    'customer_email',
                    'customer_fullname',
                    'customer_phone',
                    'customer_address',
                    'customer_contact_person_fullname',
                    'customer_contact_person_phone',
                    'event_type',
                    'number_of_guest',
                    'start_time',
                    'end_time'
                ].map((id) => document.getElementById(id));

                for (const input of stepOneInputs) {
                    if (!input.reportValidity()) {
                        scrollToElement(input);
                        return false;
                    }
                }

                if (!bookingDateInput.value) {
                    dateErrorBanner.textContent = 'Please choose a booking date from the calendar.';
                    dateErrorBanner.classList.remove('d-none');
                    scrollToElement(dateErrorBanner);
                    return false;
                }

                const startTime = parseTimeValue(document.getElementById('start_time').value);
                const endTime = parseTimeValue(document.getElementById('end_time').value);

                if (startTime && endTime && !endTime.isAfter(startTime)) {
                    dateErrorBanner.textContent = 'End time must be later than start time.';
                    dateErrorBanner.classList.remove('d-none');
                    scrollToElement(dateErrorBanner);
                    return false;
                }

                dateErrorBanner.classList.add('d-none');
                return true;
            }

            if (stepNumber === 2) {
                if (serviceTableBody.children.length === 0) {
                    window.alert('Please add at least one service before continuing.');
                    scrollToElement(document.getElementById('service-select'));
                    return false;
                }

                return true;
            }

            if (stepNumber === 3) {
                if (!termsCheckbox.checked) {
                    termsCheckbox.reportValidity();
                    scrollToElement(termsCheckbox);
                    return false;
                }
            }

            return true;
        }

        function showStep(stepNumber) {
            currentStep = stepNumber;
            steps.forEach((step) => {
                step.classList.toggle('active', Number(step.dataset.step) === stepNumber);
            });

            stepChips.forEach((chip) => {
                chip.classList.toggle('active', Number(chip.dataset.stepChip) === stepNumber);
            });

            prevStepBtn.classList.toggle('d-none', stepNumber === 1);
            nextStepBtn.classList.toggle('d-none', stepNumber === 3);
            submitBookingBtn.classList.toggle('d-none', stepNumber !== 3);
            submitBookingBtn.disabled = !termsCheckbox.checked;

            if (stepNumber === 3) {
                renderPreview();
            }

            if (stepNumber === 1) {
                if (!calendarInitialized) {
                    calendar.render();
                    calendarInitialized = true;
                }

                window.setTimeout(() => calendar.updateSize(), 50);
            }

            scrollToStepTop(stepNumber);
        }

        function scrollToFirstServerError() {
            if (!serverErrorKeys.length) {
                return;
            }

            const fieldMap = {
                customer_email: document.getElementById('customer_email'),
                customer_fullname: document.getElementById('customer_fullname'),
                customer_phone: document.getElementById('customer_phone'),
                customer_address: document.getElementById('customer_address'),
                customer_contact_person_fullname: document.getElementById('customer_contact_person_fullname'),
                customer_contact_person_phone: document.getElementById('customer_contact_person_phone'),
                event_type: document.getElementById('event_type'),
                number_of_guest: document.getElementById('number_of_guest'),
                booking_date: dateErrorBanner,
                start_time: document.getElementById('start_time'),
                end_time: document.getElementById('end_time'),
                services: document.getElementById('service-select'),
                quantities: document.getElementById('service-select'),
                discount: document.getElementById('discount'),
                amount_paid: document.getElementById('amount_paid'),
                terms: termsCheckbox
            };

            for (const key of serverErrorKeys) {
                const normalizedKey = key.replace(/\.\d+$/, '');
                const element = fieldMap[normalizedKey];

                if (element) {
                    scrollToElement(element);
                    break;
                }
            }
        }

        function initializeTimePicker(wrapperSelector, inputElement) {
            const wrapper = $(wrapperSelector);

            wrapper.datetimepicker({
                format: 'hh:mm A',
                stepping: 15,
                useCurrent: false,
                widgetPositioning: {
                    horizontal: 'left',
                    vertical: 'bottom'
                },
                icons: {
                    time: 'far fa-clock',
                    date: 'far fa-calendar',
                    up: 'fas fa-chevron-up',
                    down: 'fas fa-chevron-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'far fa-calendar-check',
                    clear: 'fas fa-trash',
                    close: 'fas fa-times'
                }
            });

            wrapper.on('show.datetimepicker', function () {
                window.setTimeout(() => {
                    const widget = wrapper.find('.bootstrap-datetimepicker-widget');

                    if (!widget.length || widget.find('.timepicker-ok-wrap').length) {
                        return;
                    }

                    const okButton = $(`
                        <div class="timepicker-ok-wrap">
                            <button type="button" class="btn btn-primary btn-sm">OK</button>
                        </div>
                    `);

                    okButton.find('button').on('click', function () {
                        wrapper.datetimepicker('hide');
                    });

                    widget.append(okButton);
                }, 0);
            });

            wrapper.on('change.datetimepicker', function (event) {
                if (!event.date) {
                    inputElement.value = '';
                    return;
                }

                inputElement.value = event.date.format('hh:mm A');
            });

            wrapper.on('shown.datetimepicker', function () {
                inputElement.focus({
                    preventScroll: true
                });
            });

            inputElement.addEventListener('focus', () => {
                wrapper.datetimepicker('show');
            });

            inputElement.addEventListener('click', () => {
                wrapper.datetimepicker('show');
            });

            inputElement.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === 'ArrowDown' || event.key === ' ') {
                    event.preventDefault();
                    wrapper.datetimepicker('show');
                }
            });
        }

        addServiceBtn.addEventListener('click', () => {
            if (!serviceSelect.value) {
                serviceSelect.reportValidity();
                return;
            }

            addServiceRow(serviceSelect.value, 1);
            serviceSelect.value = '';
        });

        serviceTableBody.addEventListener('input', (event) => {
            if (!event.target.classList.contains('service-qty-input')) {
                return;
            }

            const row = event.target.closest('tr');
            updateRowTotal(row);
            recalculateTotals();
        });

        serviceTableBody.addEventListener('click', (event) => {
            const button = event.target.closest('.service-remove-btn');

            if (!button) {
                return;
            }

            button.closest('tr').remove();
            syncSerialNumbers();
            updateEmptyState();
            recalculateTotals();
        });

        document.getElementById('discount').addEventListener('input', recalculateTotals);
        document.getElementById('amount_paid').addEventListener('input', recalculateTotals);

        prevStepBtn.addEventListener('click', () => {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        });

        nextStepBtn.addEventListener('click', () => {
            if (!validateStep(currentStep)) {
                return;
            }

            if (currentStep < 3) {
                showStep(currentStep + 1);
            }
        });

        termsCheckbox.addEventListener('change', () => {
            submitBookingBtn.disabled = !termsCheckbox.checked;
        });

        form.addEventListener('submit', (event) => {
            if (!validateStep(1) || !validateStep(2) || !validateStep(3)) {
                event.preventDefault();

                if (!validateStep(1)) {
                    showStep(1);
                    return;
                }

                if (!validateStep(2)) {
                    showStep(2);
                    return;
                }

                showStep(3);
            }
        });

        const calendar = new FullCalendar.Calendar(document.getElementById('booking-calendar'), {
            initialView: 'dayGridMonth',
            initialDate: bookingDateInput.value || undefined,
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            dayCellDidMount(info) {
                const dateKey = toDateKey(info.date);

                if (bookedDateSet.has(dateKey)) {
                    info.el.classList.add('booked-date');
                    info.el.title = 'Already booked. Click to open details.';
                }

                if (dateKey < todayKey) {
                    info.el.classList.add('past-date');

                    if (!bookedDateSet.has(dateKey)) {
                        info.el.title = 'Past dates cannot be selected for a new booking.';
                    }
                }

                if (dateKey === bookingDateInput.value) {
                    info.el.classList.add('selected-date');
                }
            },
            dateClick(info) {
                const bookedDate = bookedDateMap.get(info.dateStr);

                if (bookedDate) {
                    window.open(bookedDate.url, '_blank', 'noopener');
                    return;
                }

                if (info.dateStr < todayKey) {
                    dateErrorBanner.textContent = 'Past dates cannot be selected for a new booking.';
                    dateErrorBanner.classList.remove('d-none');
                    return;
                }

                bookingDateInput.value = info.dateStr;
                dateErrorBanner.classList.add('d-none');
                updateSelectedDateUI();
            }
        });

        updateSelectedDateUI();
        initializeTimePicker('#start_time_picker', startTimeInput);
        initializeTimePicker('#end_time_picker', endTimeInput);

        initialSelectedServices.forEach((serviceId, index) => {
            addServiceRow(serviceId, initialQuantities[index] || 1);
        });

        recalculateTotals();
        showStep(currentStep);
        scrollToFirstServerError();
    </script>
@endpush
