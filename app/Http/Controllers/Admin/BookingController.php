<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(): View
    {
        return view('pages.bookings.index', [
            'title' => 'Booking History',
            'pageTitle' => 'Your booking history',
            'description' => 'Review all bookings and jump into details, edits, approvals, or delete actions.',
            'bookings' => $this->getBookingTableRows(),
            'isBalancePage' => false,
        ]);
    }

    public function yetToBalance(): View
    {
        return view('pages.bookings.index', [
            'title' => 'Yet to Balance',
            'pageTitle' => 'Bookings yet to balance',
            'description' => 'Track bookings that still have outstanding balances.',
            'bookings' => $this->getBookingTableRows(true),
            'isBalancePage' => true,
        ]);
    }

    public function create(): View
    {
        return view('pages.bookings.create', $this->buildFormViewData());
    }

    public function edit(string $bookingId): View
    {
        return view('pages.bookings.create', $this->buildFormViewData($this->getBookingDetails($bookingId)));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->persist($request);
    }

    public function update(Request $request, string $bookingId): RedirectResponse
    {
        return $this->persist($request, $this->getBookingDetails($bookingId));
    }

    public function show(Request $request, string $bookingId): View
    {
        $booking = $this->getBookingDetails($bookingId);

        $this->writeAudit("Viewed booking {$bookingId}.", $bookingId);

        return view('pages.bookings.show', [
            'booking' => $booking,
            'openModal' => $request->query('open'),
        ]);
    }

    public function receipt(string $bookingId): View
    {
        $booking = $this->getBookingDetails($bookingId);

        $this->writeAudit("Opened receipt for booking {$bookingId}.", $bookingId);

        return view('pages.bookings.receipt', [
            'booking' => $booking,
            'agreementHtml' => new HtmlString($this->getAgreementHtml()),
            'receiptSignatureHtml' => new HtmlString($this->getReceiptSignatureHtml()),
            'companyContact' => $this->getCompanyContactDetails(),
            'generatedAt' => now(),
        ]);
    }

    public function addBalance(Request $request, string $bookingId): RedirectResponse
    {
        $booking = $this->getBookingDetails($bookingId);

        $validated = $request->validate([
            'balance' => ['required', 'numeric', 'min:0.01'],
        ]);

        $balance = $this->parseMoney($validated['balance']);
        $newPaidAmount = $booking['amount_paid'] + $balance;
        $newBalance = max($booking['final_total'] - $newPaidAmount, 0);
        $paymentStatus = $newBalance <= 0 ? 'completed' : 'part payment';

        DB::transaction(function () use ($bookingId, $balance, $paymentStatus): void {
            DB::table('payments')->insert([
                'booking_id' => $bookingId,
                'amount' => number_format($balance, 2, '.', ''),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('bookings')
                ->where('bookign_id', $bookingId)
                ->update([
                    'payment_status' => $paymentStatus,
                    'updated_at' => now(),
                ]);

            $this->writeAudit(
                sprintf(
                    'Added balance payment of %s to booking %s as %s.',
                    $this->formatMoney($balance),
                    $bookingId,
                    $paymentStatus
                ),
                $bookingId
            );
        });

        return redirect()
            ->route('bookings.show', $bookingId)
            ->with('success', 'Balance payment was added successfully.');
    }

    public function updateApproval(Request $request, string $bookingId): RedirectResponse
    {
        $booking = $this->getBookingDetails($bookingId);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'approved', 'declined'])],
        ]);

        DB::table('bookings')
            ->where('bookign_id', $bookingId)
            ->update([
                'status' => $validated['status'],
                'admin_id' => Auth::user()?->fullname ?? Auth::user()?->name,
                'updated_at' => now(),
            ]);

        $this->writeAudit(
            sprintf(
                'Updated booking %s approval status from %s to %s.',
                $bookingId,
                $booking['status'] ?: 'unset',
                $validated['status']
            ),
            $bookingId
        );

        return redirect()
            ->route('bookings.show', $bookingId)
            ->with('success', 'Booking approval status was updated successfully.');
    }

    public function destroy(string $bookingId): RedirectResponse
    {
        abort_unless($this->isSuperAdmin(), 403);

        $booking = $this->getBookingDetails($bookingId);

        DB::transaction(function () use ($bookingId, $booking): void {
            DB::table('bookings_services')->where('bookings_id', (string) $booking['id'])->delete();
            DB::table('payments')->where('booking_id', $bookingId)->delete();
            DB::table('bookings')->where('bookign_id', $bookingId)->delete();

            $this->writeAudit(
                sprintf('Deleted booking %s for %s.', $bookingId, $booking['customer_fullname']),
                $bookingId
            );
        });

        return redirect()
            ->route('bookings.history')
            ->with('success', "Booking {$bookingId} was deleted successfully.");
    }

    public function history(string $bookingId): View
    {
        $booking = $this->getBookingDetails($bookingId);
        $audits = collect();

        if (Schema::hasTable('audits')) {
            $audits = DB::table('audits')
                ->where('booking_id', $bookingId)
                ->orderByDesc('created_at')
                ->get();
        }

        return view('pages.bookings.history', [
            'booking' => $booking,
            'audits' => $audits,
        ]);
    }

    private function persist(Request $request, ?array $existingBooking = null): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_fullname' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'customer_address' => ['required', 'string', 'max:500'],
            'customer_contact_person_fullname' => ['required', 'string', 'max:255'],
            'customer_contact_person_phone' => ['required', 'string', 'max:255'],
            'event_type' => [
                'required',
                'string',
                'max:200',
                Rule::exists('event_type', 'name')->where(fn ($query) => $query->whereRaw("TRIM(COALESCE(status, '')) = 'enabled'")),
            ],
            'number_of_guest' => ['required', 'integer', 'min:1'],
            'booking_date' => array_values(array_filter([
                'required',
                'date',
                $existingBooking ? null : 'after_or_equal:today',
            ])),
            'start_time' => ['required', 'date_format:h:i A'],
            'end_time' => ['required', 'date_format:h:i A'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where(fn ($query) => $query->whereRaw("TRIM(COALESCE(status, '')) = 'enabled'")),
            ],
            'quantities' => ['required', 'array', 'min:1'],
            'quantities.*' => ['required', 'integer', 'min:1'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'message' => ['nullable', 'string', 'max:5000'],
            'terms' => ['accepted'],
        ], [
            'booking_date.required' => 'Please choose a booking date from the calendar.',
            'booking_date.after_or_equal' => 'Past dates cannot be used for a new booking.',
            'services.required' => 'Please add at least one service before continuing.',
            'terms.accepted' => 'You must agree to the terms and conditions before submitting.',
        ]);

        $validator->after(function ($validator) use ($request, $existingBooking): void {
            $services = $request->input('services', []);
            $quantities = $request->input('quantities', []);

            if (count($services) !== count($quantities)) {
                $validator->errors()->add('services', 'Each selected service must have a matching quantity.');
            }

            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');

            if ($startTime && $endTime) {
                $start = Carbon::createFromFormat('h:i A', $startTime);
                $end = Carbon::createFromFormat('h:i A', $endTime);

                if ($start->greaterThanOrEqualTo($end)) {
                    $validator->errors()->add('end_time', 'End time must be later than start time.');
                }
            }

            $requestedDate = $this->normalizeDate($request->input('booking_date'));

            if (! $requestedDate || ! Schema::hasTable('bookings')) {
                return;
            }

            $booked = $this->findBookingByDate($requestedDate, $existingBooking['bookign_id'] ?? null);

            if ($booked) {
                $validator->errors()->add('booking_date', 'That booking date is already taken. Please choose another available date.');
            }
        });

        $validated = $validator->validate();

        $serviceRows = $this->resolveServices(
            collect($validated['services'])->map(fn ($id) => (int) $id),
            collect($validated['quantities'])->map(fn ($qty) => (int) $qty)
        );

        if ($serviceRows->isEmpty()) {
            return back()
                ->withErrors(['services' => 'The selected services could not be loaded.'])
                ->withInput();
        }

        $subTotal = $serviceRows->sum(fn (array $service): float => $service['unit_price'] * $service['quantity']);
        $tax = $this->parseMoney($validated['tax'] ?? 0);
        $discount = $this->parseMoney($validated['discount'] ?? 0);
        $inputAmountPaid = $this->parseMoney($validated['amount_paid'] ?? 0);
        $finalTotal = max(($subTotal + $tax) - $discount, 0);
        $bookingDate = Carbon::parse($validated['booking_date']);
        $staff = Auth::user();

        if ($existingBooking) {
            $existingPaymentsTotal = $existingBooking['amount_paid'];
            $paymentDelta = max($inputAmountPaid - $existingPaymentsTotal, 0);
            $amountPaid = $existingPaymentsTotal + $paymentDelta;
            $balance = max($finalTotal - $amountPaid, 0);
            $paymentStatus = $amountPaid <= 0 ? 'pending' : ($balance <= 0 ? 'completed' : 'part payment');

            DB::transaction(function () use (
                $existingBooking,
                $validated,
                $serviceRows,
                $tax,
                $discount,
                $paymentDelta,
                $finalTotal,
                $paymentStatus,
                $bookingDate,
                $staff
            ): void {
                DB::table('bookings')
                    ->where('bookign_id', $existingBooking['bookign_id'])
                    ->update([
                        'user_id' => (string) ($staff?->getAuthIdentifier() ?? ''),
                        'date_start' => $bookingDate->format('l, F j, Y'),
                        'time_start' => Carbon::createFromFormat('h:i A', $validated['start_time'])->format('g:i A'),
                        'time_end' => Carbon::createFromFormat('h:i A', $validated['end_time'])->format('g:i A'),
                        'event_type' => $validated['event_type'],
                        'number_of_guest' => (string) $validated['number_of_guest'],
                        'message' => $validated['message'] ?? null,
                        'admin_id' => $staff?->fullname ?? $staff?->name,
                        'tax' => $this->decimalString($tax),
                        'total_amount' => number_format($finalTotal, 2, '.', ','),
                        'discount' => $this->decimalString($discount),
                        'updated_at' => now(),
                        'customer_contact_person_phone' => $validated['customer_contact_person_phone'],
                        'customer_contact_person_fullname' => $validated['customer_contact_person_fullname'],
                        'customer_address' => $validated['customer_address'],
                        'customer_phone' => $validated['customer_phone'],
                        'customer_fullname' => $validated['customer_fullname'],
                        'customer_email' => $validated['customer_email'],
                        'payment_status' => $paymentStatus,
                    ]);

                DB::table('bookings_services')->where('bookings_id', (string) $existingBooking['id'])->delete();

                foreach ($serviceRows as $service) {
                    DB::table('bookings_services')->insert([
                        'service_id' => (string) $service['id'],
                        'name' => $service['name'],
                        'bookings_id' => (string) $existingBooking['id'],
                        'amount' => number_format($service['unit_price'], 2, '.', ''),
                        'status' => 'active',
                        'discount' => null,
                        'description' => $service['description'],
                        'quantity' => (string) $service['quantity'],
                        'customer_email' => $validated['customer_email'],
                        'customer_fullname' => $validated['customer_fullname'],
                        'customer_phone' => $validated['customer_phone'],
                        'customer_address' => $validated['customer_address'],
                        'customer_contact_person_fullname' => $validated['customer_contact_person_fullname'],
                        'customer_contact_person_phone' => $validated['customer_contact_person_phone'],
                    ]);
                }

                if ($paymentDelta > 0) {
                    DB::table('payments')->insert([
                        'booking_id' => $existingBooking['bookign_id'],
                        'amount' => number_format($paymentDelta, 2, '.', ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $this->writeAudit("Updated booking {$existingBooking['bookign_id']} from the booking form.", $existingBooking['bookign_id']);
            });

            return redirect()
                ->route('bookings.show', $existingBooking['bookign_id'])
                ->with('success', "Booking {$existingBooking['bookign_id']} was updated successfully.");
        }

        $amountPaid = $inputAmountPaid;
        $balance = max($finalTotal - $amountPaid, 0);
        $paymentStatus = $amountPaid <= 0 ? 'pending' : ($balance <= 0 ? 'completed' : 'part payment');
        $bookingId = sprintf('%s%s', $staff?->getAuthIdentifier() ?? '1', strtolower(substr(str_replace('-', '', (string) Str::uuid()), 0, 12)));

        DB::transaction(function () use (
            $validated,
            $serviceRows,
            $tax,
            $discount,
            $amountPaid,
            $finalTotal,
            $paymentStatus,
            $bookingDate,
            $staff,
            $bookingId
        ): void {
            $bookingRecordId = DB::table('bookings')->insertGetId([
                'bookign_id' => $bookingId,
                'user_id' => (string) ($staff?->getAuthIdentifier() ?? ''),
                'date_start' => $bookingDate->format('l, F j, Y'),
                'time_start' => Carbon::createFromFormat('h:i A', $validated['start_time'])->format('g:i A'),
                'time_end' => Carbon::createFromFormat('h:i A', $validated['end_time'])->format('g:i A'),
                'event_type' => $validated['event_type'],
                'number_of_guest' => (string) $validated['number_of_guest'],
                'message' => $validated['message'] ?? null,
                'remarks' => null,
                'status' => 'active',
                'admin_id' => $staff?->fullname ?? $staff?->name,
                'tax' => $this->decimalString($tax),
                'total_amount' => number_format($finalTotal, 2, '.', ','),
                'discount' => $this->decimalString($discount),
                'created_at' => now(),
                'updated_at' => now(),
                'date_of_application' => now()->format('l, F j, Y'),
                'customer_contact_person_phone' => $validated['customer_contact_person_phone'],
                'customer_contact_person_fullname' => $validated['customer_contact_person_fullname'],
                'customer_address' => $validated['customer_address'],
                'customer_phone' => $validated['customer_phone'],
                'customer_fullname' => $validated['customer_fullname'],
                'customer_email' => $validated['customer_email'],
                'payment_status' => $paymentStatus,
            ]);

            foreach ($serviceRows as $service) {
                DB::table('bookings_services')->insert([
                    'service_id' => (string) $service['id'],
                    'name' => $service['name'],
                    'bookings_id' => (string) $bookingRecordId,
                    'amount' => number_format($service['unit_price'], 2, '.', ''),
                    'status' => 'active',
                    'discount' => null,
                    'description' => $service['description'],
                    'quantity' => (string) $service['quantity'],
                    'customer_email' => $validated['customer_email'],
                    'customer_fullname' => $validated['customer_fullname'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_address' => $validated['customer_address'],
                    'customer_contact_person_fullname' => $validated['customer_contact_person_fullname'],
                    'customer_contact_person_phone' => $validated['customer_contact_person_phone'],
                ]);
            }

            if ($amountPaid > 0) {
                DB::table('payments')->insert([
                    'booking_id' => $bookingId,
                    'amount' => number_format($amountPaid, 2, '.', ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->writeAudit(
                sprintf(
                    'Created booking %s for %s with total %s and payment status %s.',
                    $bookingId,
                    $validated['customer_fullname'],
                    $this->formatMoney($finalTotal),
                    $paymentStatus
                ),
                $bookingId
            );
        });

        return redirect()
            ->route('bookings.show', $bookingId)
            ->with('success', "Booking {$bookingId} was created successfully.");
    }

    private function buildFormViewData(?array $booking = null): array
    {
        return [
            'services' => $this->getEnabledServices(),
            'eventTypes' => $this->getEnabledEventTypes(),
            'bookedDates' => $this->getBookedDates($booking['bookign_id'] ?? null),
            'agreementText' => $this->getAgreementText(),
            'booking' => $booking,
        ];
    }

    private function getBookingTableRows(bool $onlyOutstanding = false): Collection
    {
        $bookings = DB::table('bookings')
            ->orderByDesc('id')
            ->get()
            ->map(function (object $booking): array {
                $paymentsTotal = Schema::hasTable('payments')
                    ? (float) DB::table('payments')->where('booking_id', $booking->bookign_id)->sum('amount')
                    : 0.0;

                $totalAmount = $this->parseMoney($booking->total_amount);
                $balance = max($totalAmount - $paymentsTotal, 0);

                return [
                    'bookign_id' => $booking->bookign_id,
                    'event_type' => $booking->event_type,
                    'date_start' => $booking->date_start,
                    'time_start' => $booking->time_start,
                    'time_end' => $booking->time_end,
                    'number_of_guest' => $booking->number_of_guest,
                    'status' => $booking->status ?: 'active',
                    'total_amount' => $totalAmount,
                    'payment_status' => $booking->payment_status ?: 'pending',
                    'balance_due' => $balance,
                ];
            });

        if ($onlyOutstanding) {
            $bookings = $bookings->filter(fn (array $booking): bool => $booking['balance_due'] > 0)->values();
        }

        return $bookings;
    }

    private function getEnabledServices(): Collection
    {
        if (! Schema::hasTable('services')) {
            return collect();
        }

        return DB::table('services')
            ->select('id', 'name', 'description', 'price')
            ->whereRaw("TRIM(COALESCE(status, '')) = 'enabled'")
            ->orderBy('name')
            ->get()
            ->map(function (object $service): array {
                return [
                    'id' => (int) $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price' => $this->parseMoney($service->price),
                    'price_display' => $this->formatMoney($service->price),
                ];
            });
    }

    private function getEnabledEventTypes(): Collection
    {
        if (! Schema::hasTable('event_type')) {
            return collect();
        }

        return DB::table('event_type')
            ->select('id', 'name')
            ->whereRaw("TRIM(COALESCE(status, '')) = 'enabled'")
            ->orderBy('name')
            ->get();
    }

    private function getBookedDates(?string $ignoreBookingId = null): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        return DB::table('bookings')
            ->select('bookign_id', 'date_start', 'event_type', 'customer_fullname', 'status')
            ->whereNotNull('date_start')
            ->when($ignoreBookingId, fn ($query) => $query->where('bookign_id', '!=', $ignoreBookingId))
            ->where(function ($query): void {
                $query->whereNull('status')
                    ->orWhereRaw("LOWER(TRIM(status)) != 'declined'");
            })
            ->orderBy('date_start')
            ->get()
            ->map(function (object $booking): ?array {
                $normalizedDate = $this->normalizeDate($booking->date_start);

                if (! $normalizedDate) {
                    return null;
                }

                return [
                    'date' => $normalizedDate,
                    'label' => $this->displayDate($normalizedDate),
                    'event_type' => $booking->event_type ?: 'Booked',
                    'customer' => $booking->customer_fullname ?: 'Existing booking',
                    'booking_id' => $booking->bookign_id,
                    'url' => route('bookings.show', $booking->bookign_id),
                ];
            })
            ->filter()
            ->unique('date')
            ->values()
            ->all();
    }

    private function getAgreementText(): string
    {
        if (! Schema::hasTable('agreement')) {
            return 'I agree to the booking terms and confirm that all supplied booking details are correct.';
        }

        $agreement = DB::table('agreement')->orderByDesc('id')->value('description');

        if (! $agreement) {
            return 'I agree to the booking terms and confirm that all supplied booking details are correct.';
        }

        return $this->decodeAgreement($agreement);
    }

    private function getAgreementHtml(): string
    {
        return $this->getRichTextRecord('agreement')
            ?: '<p>I agree to the booking terms and confirm that all supplied booking details are correct.</p>';
    }

    private function getReceiptSignatureHtml(): string
    {
        return $this->getRichTextRecord('receipt_signature')
            ?: '<p><strong>For Liora City Event Center</strong></p><p>Authorized Signature</p>';
    }

    private function getRichTextRecord(string $table): string
    {
        if (! Schema::hasTable($table)) {
            return '';
        }

        $content = DB::table($table)->orderByDesc('id')->value('description');

        if (! $content) {
            return '';
        }

        return $this->decodeAgreement($content);
    }

    private function getCompanyContactDetails(): array
    {
        $defaults = [
            'email' => 'info@lioracityeventcenter.com',
            'phone' => '+234 704 116 7461',
            'address' => 'No 1 Oko-Ogba Road, Irhirhi Junction, Benin City, Edo State, Nigeria',
        ];

        if (! Schema::hasTable('contact_page')) {
            return $defaults;
        }

        $contact = DB::table('contact_page')->orderByDesc('id')->first();

        if (! $contact) {
            return $defaults;
        }

        return [
            'email' => $contact->email ?: $defaults['email'],
            'phone' => $contact->phone ?: $defaults['phone'],
            'address' => $contact->address ?: $defaults['address'],
        ];
    }

    private function getBookingDetails(string $bookingId): array
    {
        $booking = DB::table('bookings')
            ->where('bookign_id', $bookingId)
            ->first();

        abort_unless($booking, 404);

        $services = collect();
        if (Schema::hasTable('bookings_services')) {
            $services = DB::table('bookings_services')
                ->where('bookings_id', (string) $booking->id)
                ->orderBy('id')
                ->get()
                ->map(function (object $service): array {
                    $unitPrice = $this->parseMoney($service->amount);
                    $quantity = max((int) ($service->quantity ?: 1), 1);

                    return [
                        'service_id' => $service->service_id,
                        'name' => $service->name,
                        'description' => $service->description,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $unitPrice * $quantity,
                    ];
                });
        }

        $payments = collect();
        if (Schema::hasTable('payments')) {
            $payments = DB::table('payments')
                ->where('booking_id', $bookingId)
                ->orderBy('created_at')
                ->get()
                ->map(function (object $payment): array {
                    $amount = $this->parseMoney($payment->amount);

                    return [
                        'id' => $payment->id,
                        'amount' => $amount,
                        'amount_display' => $this->formatMoney($amount),
                        'created_at' => $payment->created_at,
                    ];
                });
        }

        $subTotal = $services->sum('line_total');
        $tax = $this->parseMoney($booking->tax);
        $discount = $this->parseMoney($booking->discount);
        $finalTotal = max(($subTotal + $tax) - $discount, 0);
        $amountPaid = $payments->sum('amount');
        $balanceDue = max($finalTotal - $amountPaid, 0);

        return [
            'id' => $booking->id,
            'bookign_id' => $booking->bookign_id,
            'user_id' => $booking->user_id,
            'event_type' => $booking->event_type,
            'number_of_guest' => $booking->number_of_guest,
            'date_start' => $booking->date_start,
            'time_start' => $booking->time_start,
            'time_end' => $booking->time_end,
            'payment_status' => $booking->payment_status ?: 'pending',
            'date_of_application' => $booking->date_of_application,
            'tax' => $tax,
            'message' => $booking->message,
            'discount' => $discount,
            'customer_email' => $booking->customer_email,
            'status' => $booking->status ?: 'active',
            'admin_id' => $booking->admin_id,
            'customer_fullname' => $booking->customer_fullname,
            'customer_phone' => $booking->customer_phone,
            'customer_address' => $booking->customer_address,
            'customer_contact_person_fullname' => $booking->customer_contact_person_fullname,
            'customer_contact_person_phone' => $booking->customer_contact_person_phone,
            'services' => $services->all(),
            'payments' => $payments->all(),
            'sub_total' => $subTotal,
            'final_total' => $finalTotal,
            'amount_paid' => $amountPaid,
            'balance_due' => $balanceDue,
            'can_add_balance' => $balanceDue > 0,
            'can_approve' => in_array($booking->status, [null, '', 'active'], true),
            'can_generate_invoice' => $booking->status === 'approved',
            'form' => [
                'customer_email' => $booking->customer_email,
                'customer_fullname' => $booking->customer_fullname,
                'customer_phone' => $booking->customer_phone,
                'customer_address' => $booking->customer_address,
                'customer_contact_person_fullname' => $booking->customer_contact_person_fullname,
                'customer_contact_person_phone' => $booking->customer_contact_person_phone,
                'event_type' => $booking->event_type,
                'number_of_guest' => $booking->number_of_guest,
                'booking_date' => $this->normalizeDate($booking->date_start),
                'start_time' => $this->normalizeTimeForForm($booking->time_start),
                'end_time' => $this->normalizeTimeForForm($booking->time_end),
                'discount' => $discount > 0 ? $this->decimalString($discount) : '',
                'amount_paid' => $amountPaid > 0 ? $this->decimalString($amountPaid) : '',
                'message' => $booking->message,
                'services' => $services->pluck('service_id')->all(),
                'quantities' => $services->pluck('quantity')->all(),
            ],
        ];
    }

    private function resolveServices(Collection $serviceIds, Collection $quantities): Collection
    {
        if ($serviceIds->isEmpty() || ! Schema::hasTable('services')) {
            return collect();
        }

        $serviceMap = DB::table('services')
            ->select('id', 'name', 'description', 'price')
            ->whereIn('id', $serviceIds->all())
            ->whereRaw("TRIM(COALESCE(status, '')) = 'enabled'")
            ->get()
            ->keyBy('id');

        return $serviceIds
            ->values()
            ->map(function (int $serviceId, int $index) use ($serviceMap, $quantities): ?array {
                $service = $serviceMap->get($serviceId);

                if (! $service) {
                    return null;
                }

                return [
                    'id' => (int) $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'unit_price' => $this->parseMoney($service->price),
                    'quantity' => max((int) $quantities->get($index, 1), 1),
                ];
            })
            ->filter()
            ->values();
    }

    private function findBookingByDate(string $date, ?string $ignoreBookingId = null): ?object
    {
        return DB::table('bookings')
            ->select('bookign_id', 'date_start', 'status')
            ->when($ignoreBookingId, fn ($query) => $query->where('bookign_id', '!=', $ignoreBookingId))
            ->whereNotNull('date_start')
            ->where(function ($query): void {
                $query->whereNull('status')
                    ->orWhereRaw("LOWER(TRIM(status)) != 'declined'");
            })
            ->get()
            ->first(fn (object $booking): bool => $this->normalizeDate($booking->date_start) === $date);
    }

    private function writeAudit(string $action, ?string $bookingId = null): void
    {
        if (! Schema::hasTable('audits')) {
            return;
        }

        $staff = Auth::user();

        DB::table('audits')->insert([
            'user_id' => (string) ($staff?->getAuthIdentifier() ?? ''),
            'user_email' => $staff?->email,
            'action' => $action,
            'created_at' => now(),
            'updated_at' => now(),
            'user_name' => $staff?->fullname ?? $staff?->name,
            'booking_id' => $bookingId,
        ]);
    }

    private function normalizeDate(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::parse(trim($date))->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeTimeForForm(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        try {
            return Carbon::parse(trim($time))->format('h:i A');
        } catch (\Throwable) {
            return null;
        }
    }

    private function displayDate(string $date): string
    {
        return Carbon::parse($date)->format('l, F j, Y');
    }

    private function parseMoney(string|int|float|null $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) preg_replace('/[^\d.\-]/', '', (string) $value);
    }

    private function formatMoney(string|int|float $amount): string
    {
        return 'NGN ' . number_format($this->parseMoney($amount), 2);
    }

    private function decimalString(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    private function decodeAgreement(string $content): string
    {
        $decoded = html_entity_decode($content, ENT_QUOTES | ENT_HTML5);
        $decodedAgain = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5);

        return $decodedAgain;
    }

    private function isSuperAdmin(): bool
    {
        return (int) (Auth::user()?->type ?? 0) >= 5;
    }
}
