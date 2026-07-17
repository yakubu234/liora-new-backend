<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Invoice - {{ $booking['bookign_id'] }}</title>
    <link href="{{ asset('legacy-receipt/assets/images/favicon/icon.png') }}" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('legacy-receipt/assets/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy-receipt/assets/css/media-query.css') }}">
    <link rel="stylesheet" href="{{ asset('legacy-receipt/assets/css/watermark.css') }}">
</head>
<body>
@php
    $money = static fn ($value): string => number_format((float) $value, 2);
@endphp
<div class="invoice_wrap train-invoice">
    <div class="invoice-container">
        <div class="invoice-content-wrap" id="download_section">
            <div class="receipt-watermarks" aria-hidden="true">
                @for ($watermark = 0; $watermark < 24; $watermark++)
                    <div class="receipt-watermark">
                        <img src="{{ asset('legacy-receipt/assets/images/logo/logo.png') }}" alt="">
                        <span>EVENT DATE<br>{{ $booking['date_start'] }}</span>
                    </div>
                @endfor
            </div>
            <header class="train-header" id="invo_header">
                <div class="invoice-logo-content">
                    <div class="invoice-logo">
                        <div>
                            <a href="{{ route('bookings.show', $booking['bookign_id']) }}" class="logo"><img src="{{ asset('legacy-receipt/assets/images/logo/logo.png') }}" width="200" alt="Liora City logo"></a>
                        </div>
                        <div>
                            <div class="invo-cont-wrap">
                                <div class="invo-social-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#address-icon)"><path d="M19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5Z" stroke="#12151C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 7L12 13L21 7" stroke="#12151C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></g><defs><clipPath id="address-icon"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>
                                </div>
                                <div class="invo-social-name">
                                    <p class="font-md-grey color-grey">NO 1 OKO-OGBA ROAD, IRHIRHI JUNCTION. BENIN EDO STATE. NIGERIA</p>
                                </div>
                            </div>
                        </div>
                        <div class="invoice-header-contact pt-15">
                            <div class="invo-cont-wrap">
                                <div class="invo-social-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5Z" stroke="#12151C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 7L12 13L21 7" stroke="#12151C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <div class="invo-social-name">
                                    <a href="mailto:info@lioracityeventcenter.com" class="font-18 color-grey">info@lioracityeventcenter.com</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="invo-head-content">
                        <div class="train-header-sec">
                            <h1 class="train-txt">INVOICE</h1>
                            <div class="invo-head-wrap">
                                <div class="w-40">Invoice No: </div>
                                <div class="invo-num inter-400">#{{ $booking['bookign_id'] }}</div>
                            </div>
                            <div class="invo-head-wrap invoi-date-wrap">
                                <div class="w-40">Invoice Date: </div>
                                <div class="invo-num inter-400">{{ $generatedAt->format('d-m-Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <section class="bus-booking-content pt-40" id="train_booking">
                <div class="container">
                    <div class="invoice-owner-conte-wrap">
                        <div class="invo-to-wrap width-50">
                            <div class="invoice-to-content">
                                <p class="font-md color-light-black">Customer Info: </p>
                                <h2 class="font-lg color-train pt-10">{{ $booking['customer_fullname'] }}</h2>
                                <p class="font-md-grey color-grey pt-10">Phone: {{ $booking['customer_phone'] }} <br> Email: {{ $booking['customer_email'] }}</p>
                            </div>
                        </div>
                        <div class="invo-pay-to-wrap width-50">
                            <div class="movie-detail-col train-detail-col border-bottom table-bg">
                                <div class="font-md color-light-black w-90 text-left">Contact Person Details:</div>
                                <div class="font-md-grey color-grey"></div>
                            </div>
                            <div class="movie-detail-col border-bottom train-detail-col">
                                <div class="font-md color-light-black w-40 text-left">Name:</div>
                                <div class="font-md-grey color-grey">{{ $booking['customer_contact_person_fullname'] }}</div>
                            </div>
                            <div class="movie-detail-col border-bottom table-bg train-detail-col">
                                <div class="font-md color-light-black w-40 text-left">Phone:</div>
                                <div class="font-md-grey color-grey">{{ $booking['customer_contact_person_phone'] }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="money-send-title-wrap mt pt-40">
                        <h3 class="font-lg color-train transfer-title">Booking Info</h3>
                        <div class="mon-sent-content-wrap">
                            <div class="mon-send-left-data">
                                <div class="mon-send-col-one"><span class="font-sm-500 color-light-black">Booking Status:</span><span class="font-sm color-grey">{{ $booking['status'] }}</span></div>
                                <div class="mon-send-col-one"><span class="font-sm-500 color-light-black">Booking From:</span><span class="font-sm color-grey">{{ $booking['date_start'] }}</span></div>
                                <div class="mon-send-col-one"><span class="font-sm-500 color-light-black">Usage Time:</span><span class="font-sm color-grey">{{ $booking['time_start'] }} to {{ $booking['time_end'] }}</span></div>
                            </div>
                            <div class="mon-send-right-data">
                                <div class="mon-send-col-two"><span class="font-sm-500 color-light-black">Number of Guest:</span><span class="font-sm color-grey">{{ $booking['number_of_guest'] }}</span></div>
                                <div class="mon-send-col-two"><span class="font-sm-500 color-light-black">Apply Date:</span><span class="font-sm color-grey">{{ $booking['date_of_application'] }}</span></div>
                                <div class="mon-send-col-two"><span class="font-sm-500 color-light-black">Event Type:</span><span class="font-sm color-grey">{{ $booking['event_type'] }}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="table-wrapper pt-40">
                        <table class="invoice-table train-table">
                            <thead>
                            <tr class="invo-tb-header">
                                <th class="font-md color-light-black wid-20">Service Name</th>
                                <th class="font-md color-light-black w-40">Description</th>
                                <th class="font-md color-light-black width-20">Qty X Price</th>
                                <th class="font-md color-light-black wid-20">Total</th>
                            </tr>
                            </thead>
                            <tbody class="invo-tb-body">
                            @forelse ($booking['services'] as $service)
                                <tr class="invo-tb-row">
                                    <td class="font-sm">{{ $service['name'] }}</td>
                                    <td class="font-sm">{{ $service['description'] }}</td>
                                    <td class="font-sm">&nbsp; {{ $service['quantity'] }} x &#8358;{{ $money($service['unit_price']) }}</td>
                                    <td class="font-sm">&nbsp; &#8358;{{ $money($service['line_total']) }}</td>
                                </tr>
                            @empty
                                <tr class="invo-tb-row"><td class="font-sm" colspan="4">No services recorded.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="invo-addition-wrap pt-20">
                        <div class="invo-add-info-content">
                            @if (filled($booking['message']))
                                <h3 class="font-md color-light-black">Additional Information:</h3>
                                <p class="font-sm color-grey pt-10">{{ $booking['message'] }}</p>
                            @endif
                        </div>
                        <div class="invo-bill-total width-30">
                            <table class="invo-total-table">
                                <tbody>
                                <tr><td class="font-md color-light-black">Sub Total:</td><td class="font-md-grey color-grey text-right">&#8358;{{ $money($booking['sub_total']) }}</td></tr>
                                @foreach ($booking['pre_tax_deductions'] as $deduction)
                                    <tr><td class="font-md color-light-black">Less for tax: {{ $deduction['name'] }}</td><td class="font-md-grey color-grey text-right">-&#8358;{{ $money($deduction['amount']) }}</td></tr>
                                @endforeach
                                @if ($booking['tax_rate'] > 0)
                                    <tr><td class="font-md color-light-black">Taxable Amount:</td><td class="font-md-grey color-grey text-right">&#8358;{{ $money($booking['taxable_amount']) }}</td></tr>
                                @endif
                                @if ($booking['tax'] > 0)
                                    <tr class="tax-row bottom-border"><td class="font-md color-light-black">Tax ({{ rtrim(rtrim(number_format($booking['tax_rate'], 4, '.', ''), '0'), '.') }}%)</td><td class="font-md-grey color-grey text-right">&#8358;{{ $money($booking['tax']) }}</td></tr>
                                @endif
                                @if ($booking['discount'] > 0)
                                    <tr class="tax-row bottom-border"><td class="font-md color-light-black">Discount</td><td class="font-md-grey color-grey text-right">-&#8358;{{ $money($booking['discount']) }}</td></tr>
                                @endif
                                <tr class="invo-grand-total"><td class="font-18-700 color-train font-18-500 pt-20">Grand Total:</td><td class="font-18-500 color-light-black pt-20 text-right">&#8358;{{ $money($booking['final_total']) }}</td></tr>
                                <tr class="invo-grand-total"><td class="font-18-700 color-train font-18-500 pt-20">Amount Paid:</td><td class="font-18-500 color-light-green pt-20 text-right" style="color:green;">&#8358;{{ $money($booking['amount_paid']) }}</td></tr>
                                <tr class="invo-grand-total"><td class="font-18-700 color-train font-18-500 pt-20">To Balance:</td><td class="font-18-500 color-light-red pt-20 text-right" style="color:red;">&#8358;{{ $money($booking['balance_due']) }}</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="invo-addition-wrap receipt-signature-wrap">
                        <div class="width-100">
                            <div class="font-sm color-grey receipt-configured-content receipt-signature">{!! $receiptSignatureHtml !!}</div>
                        </div>
                    </div>

                    <div class="receipt-agreement-heading">AGREEMENT</div>

                    <div class="invo-addition-wrap receipt-agreement-wrap">
                        <div class="width-100">
                            <div class="font-sm color-grey receipt-configured-content receipt-agreement">{!! $agreementHtml !!}</div>
                        </div>
                    </div>
                </div>
                <div class="train-thanks-bg bg-train">
                    <p>Thank you for choosing our service. See you soon 🙂 . contact us <a href="tel:+2347041167461" class="font-18 color-white">+234 704 116 7461</a></p>
                </div>
            </section>
        </div>

        <section class="agency-bottom-content d-print-none" id="agency_bottom">
            <div class="invo-buttons-wrap">
                <div class="invo-print-btn invo-btns">
                    <a href="javascript:window.print()" class="print-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M17 17H19C20.1046 17 21 16.1046 21 15V11C21 9.89543 20.1046 9 19 9H5C3.89543 9 3 9.89543 3 11V15C3 16.1046 3.89543 17 5 17H7" stroke="white" stroke-width="2"/><path d="M17 9V5C17 3.89543 16.1046 3 15 3H9C7.89543 3 7 3.89543 7 5V9" stroke="white" stroke-width="2"/><path d="M7 15C7 13.8954 7.89543 13 9 13H15C16.1046 13 17 13.8954 17 15V19C17 20.1046 16.1046 21 15 21H9C7.89543 21 7 20.1046 7 19V15Z" stroke="white" stroke-width="2"/></svg>
                        <span class="inter-700 medium-font">Print</span>
                    </a>
                </div>
                <div class="invo-down-btn invo-btns">
                    <a class="download-btn" id="generatePDF">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 17V19C4 20.1046 4.89543 21 6 21H18C19.1046 21 20 20.1046 20 19V17" stroke="white" stroke-width="2"/><path d="M7 11L12 16L17 11M12 4V16" stroke="white" stroke-width="2"/></svg>
                        <span class="inter-700 medium-font">Download</span>
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="{{ asset('legacy-receipt/assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('legacy-receipt/assets/js/jspdf.min.js') }}"></script>
<script src="{{ asset('legacy-receipt/assets/js/html2canvas.min.js') }}"></script>
<script>
    (function ($) {
        'use strict';
        var customerName = @json($booking['customer_fullname']);
        var pdfName = customerName + '_Invoice_' + new Date().getTime() + '.pdf';

        function fillReceiptWatermarks() {
            var section = document.getElementById('download_section');
            var layer = section ? section.querySelector('.receipt-watermarks') : null;
            var source = layer ? layer.querySelector('.receipt-watermark') : null;

            if (!section || !layer || !source) {
                return;
            }

            var styles = window.getComputedStyle(layer);
            var columns = Math.max(styles.gridTemplateColumns.split(' ').filter(Boolean).length, 1);
            var rowHeight = parseFloat(styles.gridAutoRows) || 190;
            var requiredCount = Math.max(Math.ceil(section.scrollHeight / rowHeight) * columns, 24);

            if (layer.children.length === requiredCount) {
                return;
            }

            var fragment = document.createDocumentFragment();
            for (var index = 0; index < requiredCount; index++) {
                fragment.appendChild(source.cloneNode(true));
            }
            layer.replaceChildren(fragment);
        }

        window.addEventListener('load', function () {
            fillReceiptWatermarks();
            window.requestAnimationFrame(fillReceiptWatermarks);
        });
        window.addEventListener('beforeprint', fillReceiptWatermarks);

        if ('ResizeObserver' in window) {
            new ResizeObserver(fillReceiptWatermarks).observe(document.getElementById('download_section'));
        }

        $('#generatePDF').on('click', function () {
            fillReceiptWatermarks();
            var downloadSection = $('#download_section');
            var sectionElement = downloadSection[0];
            var sectionTop = sectionElement.getBoundingClientRect().top;
            var safeBreaks = Array.from(sectionElement.querySelectorAll(
                'tr, p, li, h1, h2, h3, h4, .receipt-agreement-heading, .invo-addition-wrap'
            )).map(function (element) {
                return element.getBoundingClientRect().bottom - sectionTop;
            }).filter(function (offset) {
                return offset > 0 && offset < sectionElement.scrollHeight;
            }).sort(function (a, b) {
                return a - b;
            });

            html2canvas(sectionElement, { allowTaint: true, scale: 2 }).then(function (canvas) {
                var pdfWidth = 595.28;
                var pdfHeight = 841.89;
                var horizontalMargin = 24;
                var verticalMargin = 28;
                var usableWidth = pdfWidth - (horizontalMargin * 2);
                var usableHeight = pdfHeight - (verticalMargin * 2);
                var maxSliceHeight = Math.floor(usableHeight * canvas.width / usableWidth);
                var canvasScale = canvas.height / sectionElement.scrollHeight;
                var canvasBreaks = safeBreaks.map(function (offset) {
                    return Math.floor(offset * canvasScale);
                });
                var pdf = new jsPDF('p', 'pt', [pdfWidth, pdfHeight]);
                var sliceStart = 0;
                var pageNumber = 0;

                while (sliceStart < canvas.height) {
                    var targetEnd = Math.min(sliceStart + maxSliceHeight, canvas.height);
                    var minimumUsefulBreak = sliceStart + (maxSliceHeight * 0.55);
                    var safeEnd = canvasBreaks.filter(function (point) {
                        return point >= minimumUsefulBreak && point <= targetEnd - 8;
                    }).pop();
                    var sliceEnd = targetEnd === canvas.height ? canvas.height : (safeEnd || targetEnd);
                    var sliceHeight = Math.max(sliceEnd - sliceStart, 1);
                    var pageCanvas = document.createElement('canvas');
                    pageCanvas.width = canvas.width;
                    pageCanvas.height = sliceHeight;
                    pageCanvas.getContext('2d').drawImage(
                        canvas,
                        0, sliceStart, canvas.width, sliceHeight,
                        0, 0, canvas.width, sliceHeight
                    );

                    if (pageNumber > 0) {
                        pdf.addPage(pdfWidth, pdfHeight);
                    }

                    var renderedHeight = sliceHeight * usableWidth / canvas.width;
                    pdf.addImage(
                        pageCanvas.toDataURL('image/jpeg', 0.96),
                        'JPG',
                        horizontalMargin,
                        verticalMargin,
                        usableWidth,
                        renderedHeight
                    );

                    sliceStart = sliceEnd;
                    pageNumber++;
                }

                pdf.save(pdfName);
            });
        });
    })(jQuery);
</script>
</body>
</html>
