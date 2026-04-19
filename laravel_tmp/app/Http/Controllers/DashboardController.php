<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
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
        }

        return view('dashboard', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
        ]);
    }
}
