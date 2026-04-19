<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt - {{ $booking['bookign_id'] }}</title>
    <style>
        :root {
            --brand: #8c1d18;
            --brand-soft: #f6e8e6;
            --ink: #1f2937;
            --muted: #6b7280;
            --line: #e5e7eb;
            --success: #0f766e;
            --danger: #b91c1c;
            --paper: #ffffff;
            --surface: #f8fafc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #edf1f5;
            color: var(--ink);
            font: 14px/1.6 "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .receipt-shell {
            max-width: 1120px;
            margin: 32px auto;
            padding: 0 16px 40px;
        }

        .receipt-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .receipt-toolbar__text {
            color: var(--muted);
            font-size: 0.95rem;
        }

        .receipt-toolbar__actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .receipt-btn {
            border: 0;
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .receipt-btn--primary {
            background: var(--brand);
            color: #fff;
        }

        .receipt-btn--ghost {
            background: #fff;
            color: var(--ink);
            border: 1px solid #d1d5db;
        }

        .receipt-card {
            position: relative;
            background: var(--paper);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 50px rgba(15, 23, 42, 0.12);
        }

        .receipt-watermark {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            opacity: 0.05;
            z-index: 0;
        }

        .receipt-watermark img {
            width: min(56vw, 520px);
            height: auto;
        }

        .receipt-content {
            position: relative;
            z-index: 1;
            padding: 34px;
        }

        .receipt-header {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(260px, 0.75fr);
            gap: 28px;
            padding-bottom: 26px;
            border-bottom: 1px solid var(--line);
        }

        .brand-block {
            display: flex;
            gap: 18px;
            align-items: flex-start;
        }

        .brand-logo {
            width: 92px;
            height: 92px;
            border-radius: 20px;
            background: linear-gradient(145deg, #fff, #f8e5e2);
            border: 1px solid rgba(140, 29, 24, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .brand-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .brand-copy h1 {
            margin: 0;
            font-size: 1.75rem;
            line-height: 1.1;
            letter-spacing: 0.02em;
        }

        .brand-copy p {
            margin: 8px 0 0;
            color: var(--muted);
        }

        .brand-contact {
            margin-top: 12px;
            display: grid;
            gap: 4px;
            color: #374151;
        }

        .meta-card {
            background: linear-gradient(135deg, #fff7f6, #fff);
            border: 1px solid rgba(140, 29, 24, 0.12);
            border-radius: 20px;
            padding: 20px;
        }

        .meta-card__eyebrow {
            margin: 0 0 10px;
            color: var(--brand);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .meta-card h2 {
            margin: 0 0 14px;
            font-size: 1.85rem;
            line-height: 1;
        }

        .meta-list {
            display: grid;
            gap: 10px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px dashed rgba(140, 29, 24, 0.15);
            padding-bottom: 8px;
        }

        .meta-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .meta-row span:first-child {
            color: var(--muted);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 5px 11px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: capitalize;
            background: #e5e7eb;
            color: #374151;
        }

        .status-pill--approved {
            background: #dcfce7;
            color: #166534;
        }

        .status-pill--declined {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-pill--active {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .section {
            margin-top: 28px;
        }

        .section-title {
            margin: 0 0 14px;
            font-size: 1.05rem;
            font-weight: 700;
            color: #111827;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px;
        }

        .panel h3 {
            margin: 0 0 10px;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--brand);
        }

        .panel p {
            margin: 0;
        }

        .stack {
            display: grid;
            gap: 8px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .detail-tile {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
        }

        .detail-tile span {
            display: block;
            color: var(--muted);
            font-size: 0.82rem;
            margin-bottom: 6px;
        }

        .detail-tile strong {
            font-size: 1rem;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .services-table {
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 18px;
        }

        .services-table thead th {
            background: #fcf2f0;
            color: #4b5563;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            text-align: left;
            padding: 14px 16px;
        }

        .services-table tbody td {
            padding: 14px 16px;
            border-top: 1px solid var(--line);
            vertical-align: top;
        }

        .services-table tbody tr:nth-child(even) td {
            background: #fcfcfd;
        }

        .services-table__money {
            text-align: right;
            white-space: nowrap;
        }

        .totals-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
            gap: 18px;
            align-items: start;
        }

        .note-box {
            background: #fffaf3;
            border: 1px solid #fde7b0;
            border-radius: 18px;
            padding: 18px;
            color: #6b4f0f;
        }

        .totals-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px;
        }

        .totals-card table td {
            padding: 10px 0;
            border-bottom: 1px dashed var(--line);
        }

        .totals-card table tr:last-child td {
            border-bottom: 0;
        }

        .totals-card table td:last-child {
            text-align: right;
            font-weight: 700;
        }

        .amount-paid {
            color: var(--success);
        }

        .amount-balance {
            color: var(--danger);
        }

        .rich-copy {
            color: #374151;
        }

        .rich-copy p:first-child,
        .rich-copy h1:first-child,
        .rich-copy h2:first-child,
        .rich-copy h3:first-child {
            margin-top: 0;
        }

        .rich-copy p:last-child {
            margin-bottom: 0;
        }

        .rich-copy table {
            display: block;
            width: 100%;
            overflow-x: auto;
        }

        .rich-copy img {
            max-width: 100%;
            height: auto;
        }

        .signature-box {
            background: linear-gradient(180deg, #fff, #fff8f7);
            border: 1px solid rgba(140, 29, 24, 0.14);
            border-radius: 20px;
            padding: 22px;
        }

        .signature-label {
            color: var(--brand);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.78rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .receipt-footer {
            margin-top: 26px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .agreement-page {
            margin-top: 32px;
            background: var(--paper);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 50px rgba(15, 23, 42, 0.12);
            position: relative;
        }

        .agreement-page__inner {
            position: relative;
            z-index: 1;
            padding: 34px;
        }

        .agreement-page__header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: center;
            border-bottom: 1px solid var(--line);
            padding-bottom: 20px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .agreement-page__title {
            margin: 0;
            font-size: 1.8rem;
            line-height: 1.1;
        }

        .agreement-page__subtitle {
            margin: 8px 0 0;
            color: var(--muted);
        }

        .agreement-page__meta {
            background: #fcf2f0;
            border: 1px solid rgba(140, 29, 24, 0.12);
            border-radius: 16px;
            padding: 14px 16px;
            min-width: 260px;
        }

        .agreement-page__meta strong {
            display: block;
            color: #111827;
            margin-bottom: 4px;
        }

        .agreement-signature {
            margin-top: 28px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 0.8fr);
            gap: 18px;
            align-items: start;
        }

        @media (max-width: 900px) {
            .receipt-content {
                padding: 22px;
            }

            .receipt-header,
            .info-grid,
            .detail-grid,
            .totals-layout,
            .agreement-signature {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .brand-block {
                flex-direction: column;
            }

            .receipt-shell {
                margin-top: 18px;
                padding-inline: 10px;
            }

            .receipt-content {
                padding: 18px;
            }

            .services-table {
                border-radius: 14px;
            }

            .services-table thead {
                display: none;
            }

            .services-table,
            .services-table tbody,
            .services-table tr,
            .services-table td {
                display: block;
                width: 100%;
            }

            .services-table tbody tr {
                border-top: 1px solid var(--line);
                padding: 10px 0;
            }

            .services-table tbody td {
                border-top: 0;
                padding: 6px 12px;
                text-align: left;
            }

            .services-table tbody td::before {
                content: attr(data-label);
                display: block;
                color: var(--muted);
                font-size: 0.76rem;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                margin-bottom: 4px;
            }

            .services-table__money {
                text-align: left;
            }
        }

        @media print {
            body {
                background: #fff;
            }

            .receipt-shell {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .receipt-toolbar {
                display: none !important;
            }

            .receipt-card {
                box-shadow: none;
                border-radius: 0;
            }

            .agreement-page {
                margin-top: 0;
                box-shadow: none;
                border-radius: 0;
                break-before: page;
                page-break-before: always;
            }

            .receipt-content {
                padding: 18px;
            }

            .agreement-page__inner {
                padding: 18px;
            }

            .section,
            .panel,
            .detail-tile,
            .totals-card,
            .note-box,
            .signature-box,
            .agreement-page__meta {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
@php
    $currency = fn (float|int $amount) => 'NGN ' . number_format((float) $amount, 2);
    $statusClass = match (strtolower((string) $booking['status'])) {
        'approved' => 'status-pill status-pill--approved',
        'declined' => 'status-pill status-pill--declined',
        default => 'status-pill status-pill--active',
    };
@endphp

<div class="receipt-shell">
    <div class="receipt-toolbar">
        <div class="receipt-toolbar__text">
            Receipt for booking <strong>#{{ $booking['bookign_id'] }}</strong>. Use print to save as PDF with the agreement and signature included in one document.
        </div>
        <div class="receipt-toolbar__actions">
            <a href="{{ route('bookings.show', $booking['bookign_id']) }}" class="receipt-btn receipt-btn--ghost">Back to Booking</a>
            <button type="button" class="receipt-btn receipt-btn--primary" onclick="window.print()">Print / Save PDF</button>
        </div>
    </div>

    <main class="receipt-card">
        <div class="receipt-watermark">
            <img src="{{ asset('logo.png') }}" alt="">
        </div>

        <div class="receipt-content">
            <header class="receipt-header">
                <div class="brand-block">
                    <div class="brand-logo">
                        <img src="{{ asset('logo.png') }}" alt="Liora City Event Center logo">
                    </div>
                    <div class="brand-copy">
                        <h1>Liora City Event Center</h1>
                        <p>Professional booking receipt and event payment summary.</p>
                        <div class="brand-contact">
                            <div>{{ $companyContact['address'] }}</div>
                            <div>{{ $companyContact['email'] }}</div>
                            <div>{{ $companyContact['phone'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="meta-card">
                    <p class="meta-card__eyebrow">Official Receipt</p>
                    <h2>Receipt</h2>
                    <div class="meta-list">
                        <div class="meta-row">
                            <span>Receipt No.</span>
                            <strong>#{{ $booking['bookign_id'] }}</strong>
                        </div>
                        <div class="meta-row">
                            <span>Generated On</span>
                            <strong>{{ $generatedAt->format('F j, Y g:i A') }}</strong>
                        </div>
                        <div class="meta-row">
                            <span>Status</span>
                            <span class="{{ $statusClass }}">{{ $booking['status'] }}</span>
                        </div>
                        <div class="meta-row">
                            <span>Payment Status</span>
                            <strong>{{ $booking['payment_status'] }}</strong>
                        </div>
                    </div>
                </div>
            </header>

            <section class="section">
                <h2 class="section-title">Customer and Booking Details</h2>
                <div class="info-grid">
                    <div class="panel">
                        <h3>Customer</h3>
                        <div class="stack">
                            <p><strong>{{ $booking['customer_fullname'] }}</strong></p>
                            <p>{{ $booking['customer_email'] }}</p>
                            <p>{{ $booking['customer_phone'] }}</p>
                            <p>{{ $booking['customer_address'] }}</p>
                        </div>
                    </div>
                    <div class="panel">
                        <h3>Contact Person</h3>
                        <div class="stack">
                            <p><strong>{{ $booking['customer_contact_person_fullname'] }}</strong></p>
                            <p>{{ $booking['customer_contact_person_phone'] }}</p>
                            <p>Approved By: {{ $booking['admin_id'] ?: 'Pending approval' }}</p>
                            <p>Applied On: {{ $booking['date_of_application'] }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="detail-grid">
                    <div class="detail-tile">
                        <span>Event Type</span>
                        <strong>{{ $booking['event_type'] }}</strong>
                    </div>
                    <div class="detail-tile">
                        <span>Booking Date</span>
                        <strong>{{ $booking['date_start'] }}</strong>
                    </div>
                    <div class="detail-tile">
                        <span>Time Slot</span>
                        <strong>{{ $booking['time_start'] }} to {{ $booking['time_end'] }}</strong>
                    </div>
                    <div class="detail-tile">
                        <span>Guests</span>
                        <strong>{{ $booking['number_of_guest'] }}</strong>
                    </div>
                    <div class="detail-tile">
                        <span>Amount Paid</span>
                        <strong class="amount-paid">{{ $currency($booking['amount_paid']) }}</strong>
                    </div>
                    <div class="detail-tile">
                        <span>Outstanding Balance</span>
                        <strong class="amount-balance">{{ $currency($booking['balance_due']) }}</strong>
                    </div>
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Booked Services</h2>
                <div class="services-table">
                    <table>
                        <thead>
                        <tr>
                            <th>Service</th>
                            <th>Description</th>
                            <th>Qty x Price</th>
                            <th class="services-table__money">Line Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($booking['services'] as $service)
                            <tr>
                                <td data-label="Service">
                                    <strong>{{ $service['name'] }}</strong>
                                </td>
                                <td data-label="Description">{{ $service['description'] ?: 'No description provided.' }}</td>
                                <td data-label="Qty x Price">{{ $service['quantity'] }} x {{ $currency($service['unit_price']) }}</td>
                                <td data-label="Line Total" class="services-table__money">{{ $currency($service['line_total']) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="section">
                <div class="totals-layout">
                    <div>
                        @if (filled($booking['message']))
                            <div class="note-box">
                                <strong>Additional Note</strong>
                                <div class="mt-2">{{ $booking['message'] }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="totals-card">
                        <table>
                            <tbody>
                            <tr>
                                <td>Sub Total</td>
                                <td>{{ $currency($booking['sub_total']) }}</td>
                            </tr>
                            <tr>
                                <td>Tax</td>
                                <td>{{ $currency($booking['tax']) }}</td>
                            </tr>
                            <tr>
                                <td>Discount</td>
                                <td>- {{ $currency($booking['discount']) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Grand Total</strong></td>
                                <td><strong>{{ $currency($booking['final_total']) }}</strong></td>
                            </tr>
                            <tr>
                                <td>Amount Paid</td>
                                <td class="amount-paid">{{ $currency($booking['amount_paid']) }}</td>
                            </tr>
                            <tr>
                                <td>To Balance</td>
                                <td class="amount-balance">{{ $currency($booking['balance_due']) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Agreement and Conditions</h2>
                <div class="panel rich-copy">
                    {!! $agreementHtml !!}
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Receipt Signature</h2>
                <div class="signature-box rich-copy">
                    <div class="signature-label">Authorized Signature Block</div>
                    {!! $receiptSignatureHtml !!}
                </div>
            </section>

            <footer class="receipt-footer">
                <div>This document was generated from the booking details page and includes the latest agreement and receipt signature records.</div>
                <div>{{ $companyContact['email'] }} | {{ $companyContact['phone'] }}</div>
            </footer>
        </div>
    </main>

    <section class="agreement-page">
        <div class="receipt-watermark">
            <img src="{{ asset('logo.png') }}" alt="">
        </div>

        <div class="agreement-page__inner">
            <div class="agreement-page__header">
                <div>
                    <h2 class="agreement-page__title">Agreement and Conditions</h2>
                    <p class="agreement-page__subtitle">
                        This page is included as part of the receipt package for booking #{{ $booking['bookign_id'] }}.
                    </p>
                </div>

                <div class="agreement-page__meta">
                    <strong>Liora City Event Center</strong>
                    <div>Booking ID: #{{ $booking['bookign_id'] }}</div>
                    <div>Client: {{ $booking['customer_fullname'] }}</div>
                    <div>Date: {{ $booking['date_start'] }}</div>
                </div>
            </div>

            <div class="panel rich-copy">
                {!! $agreementHtml !!}
            </div>

            <div class="agreement-signature">
                <div class="panel">
                    <h3>Customer Reference</h3>
                    <div class="stack">
                        <p><strong>{{ $booking['customer_fullname'] }}</strong></p>
                        <p>{{ $booking['customer_email'] }}</p>
                        <p>{{ $booking['customer_phone'] }}</p>
                        <p>{{ $booking['customer_address'] }}</p>
                    </div>
                </div>

                <div class="signature-box rich-copy">
                    <div class="signature-label">Receipt Signature</div>
                    {!! $receiptSignatureHtml !!}
                </div>
            </div>
        </div>
    </section>
</div>
</body>
</html>
