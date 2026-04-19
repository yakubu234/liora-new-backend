<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SearchReportController extends Controller
{
    public function bookingSearch(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $bookings = collect();

        if ($query !== '') {
            $bookings = $this->searchBookings($query);
        }

        return view('pages.bookings.search', [
            'query' => $query,
            'bookings' => $bookings,
        ]);
    }

    public function reports(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange(
            $request->query('start_date'),
            $request->query('end_date')
        );

        $bookings = $this->reportBookings($startDate, $endDate);

        return view('pages.reports.index', [
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'bookings' => $bookings,
            'summary' => [
                'total_bookings' => $bookings->count(),
                'approved' => $bookings->where('status', 'approved')->count(),
                'under_review' => $bookings->where('status', 'active')->count(),
                'declined' => $bookings->where('status', 'declined')->count(),
                'outstanding' => $bookings->filter(fn (array $booking): bool => $booking['balance_due'] > 0)->count(),
                'total_amount' => $bookings->sum('total_amount'),
                'total_paid' => $bookings->sum('amount_paid'),
                'balance_due' => $bookings->sum('balance_due'),
            ],
        ]);
    }

    private function searchBookings(string $term): Collection
    {
        if (! Schema::hasTable('bookings')) {
            return collect();
        }

        $normalizedTerm = ltrim($term, '#');
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $normalizedTerm) . '%';

        $bookings = DB::table('bookings')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->select(
                'bookings.*',
                'users.email as staff_email',
                'users.phone as staff_phone',
                'users.fullname as staff_name'
            )
            ->where(function ($query) use ($like): void {
                $query->where('bookings.bookign_id', 'like', $like)
                    ->orWhere('bookings.customer_email', 'like', $like)
                    ->orWhere('bookings.customer_phone', 'like', $like)
                    ->orWhere('bookings.customer_fullname', 'like', $like)
                    ->orWhere('bookings.event_type', 'like', $like)
                    ->orWhere('bookings.date_start', 'like', $like)
                    ->orWhere('users.email', 'like', $like)
                    ->orWhere('users.phone', 'like', $like)
                    ->orWhere('users.fullname', 'like', $like);
            })
            ->orderByDesc('bookings.id')
            ->limit(100)
            ->get();

        return $this->mapBookings($bookings);
    }

    private function reportBookings(Carbon $startDate, Carbon $endDate): Collection
    {
        if (! Schema::hasTable('bookings')) {
            return collect();
        }

        $bookings = DB::table('bookings')
            ->whereBetween('created_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->orderByDesc('id')
            ->get();

        return $this->mapBookings($bookings);
    }

    private function mapBookings(iterable $bookings): Collection
    {
        return collect($bookings)->map(function (object $booking): array {
            $amountPaid = Schema::hasTable('payments')
                ? (float) DB::table('payments')->where('booking_id', $booking->bookign_id)->sum('amount')
                : 0.0;

            $totalAmount = $this->parseMoney($booking->total_amount);
            $balanceDue = max($totalAmount - $amountPaid, 0);

            return [
                'bookign_id' => $booking->bookign_id,
                'event_type' => $booking->event_type ?: 'N/A',
                'date_start' => $booking->date_start,
                'time_start' => $booking->time_start,
                'time_end' => $booking->time_end,
                'number_of_guest' => $booking->number_of_guest ?: 0,
                'status' => $booking->status ?: 'active',
                'payment_status' => $booking->payment_status ?: 'pending',
                'customer_fullname' => $booking->customer_fullname,
                'customer_email' => $booking->customer_email,
                'customer_phone' => $booking->customer_phone,
                'total_amount' => $totalAmount,
                'amount_paid' => $amountPaid,
                'balance_due' => $balanceDue,
                'created_at' => $booking->created_at,
            ];
        });
    }

    private function resolveDateRange(?string $startDate, ?string $endDate): array
    {
        $fallbackEnd = now();
        $fallbackStart = $fallbackEnd->copy()->startOfMonth();

        try {
            $start = $startDate ? Carbon::parse($startDate) : $fallbackStart;
        } catch (\Throwable) {
            $start = $fallbackStart;
        }

        try {
            $end = $endDate ? Carbon::parse($endDate) : $fallbackEnd;
        } catch (\Throwable) {
            $end = $fallbackEnd;
        }

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end];
    }

    private function parseMoney(string|int|float|null $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) preg_replace('/[^\d.\-]/', '', (string) $value);
    }
}
