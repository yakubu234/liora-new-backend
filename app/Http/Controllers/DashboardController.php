<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'total_bookings' => 0,
            'approved_bookings' => 0,
            'pending_bookings' => 0,
            'declined_bookings' => 0,
            'part_payment_bookings' => 0,
            'completed_bookings' => 0,
            'staff_users' => 0,
            'payments_total' => 0.0,
        ];

        $recentBookings = collect();
        $calendarBookings = collect();

        try {
            if (Schema::hasTable('bookings')) {
                $stats['total_bookings'] = Booking::count();
                $stats['approved_bookings'] = Booking::where('status', 'approved')->count();
                $stats['pending_bookings'] = Booking::where(function ($query): void {
                    $query->whereNull('status')
                        ->orWhere('status', '')
                        ->orWhere('status', 'pending');
                })->count();
                $stats['declined_bookings'] = Booking::where('status', 'declined')->count();
                $stats['part_payment_bookings'] = Booking::where('payment_status', 'part payment')->count();
                $stats['completed_bookings'] = Booking::where('payment_status', 'completed')->count();

                $recentBookings = Booking::query()
                    ->latest('created_at')
                    ->limit(8)
                    ->get([
                        'bookign_id',
                        'customer_fullname',
                        'event_type',
                        'date_start',
                        'status',
                        'payment_status',
                        'total_amount',
                        'created_at',
                    ]);

                $calendarBookings = Booking::query()
                    ->latest('created_at')
                    ->get([
                        'bookign_id',
                        'customer_fullname',
                        'event_type',
                        'date_start',
                        'time_start',
                        'time_end',
                        'status',
                        'payment_status',
                        'total_amount',
                    ])
                    ->map(function (Booking $booking): ?array {
                        $date = $this->normalizeDate($booking->date_start);

                        if (! $date) {
                            return null;
                        }

                        return [
                            'id' => $booking->bookign_id,
                            'title' => $booking->event_type ?: 'Booking',
                            'start' => $date,
                            'date_label' => Carbon::parse($date)->format('l, F j, Y'),
                            'customer' => $booking->customer_fullname ?: 'Not set',
                            'event_type' => $booking->event_type ?: 'Not set',
                            'time' => trim(($booking->time_start ?: 'N/A') . ' to ' . ($booking->time_end ?: 'N/A')),
                            'status' => $booking->status ?: 'active',
                            'payment_status' => $booking->payment_status ?: 'pending',
                            'total_amount' => $booking->total_amount ?: '0.00',
                            'url' => route('bookings.show', $booking->bookign_id),
                        ];
                    })
                    ->filter()
                    ->values();
            }

            if (Schema::hasTable('users')) {
                $stats['staff_users'] = User::count();
            }

            if (Schema::hasTable('payments')) {
                $stats['payments_total'] = (float) (
                    Payment::query()
                        ->selectRaw("COALESCE(SUM(CAST(REPLACE(REPLACE(amount, ',', ''), '₦', '') AS DECIMAL(15,2))), 0) as total_paid")
                        ->value('total_paid') ?? 0
                );
            }
        } catch (QueryException) {
            $recentBookings = collect();
            $calendarBookings = collect();
        }

        return view('dashboard', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'calendarBookings' => $calendarBookings,
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
}
