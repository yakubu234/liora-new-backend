<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicAvailabilityController extends Controller
{
    public function calendar(Request $request): JsonResponse
    {
        $rangeStart = $this->normalizeDate($request->query('start')) ?? now()->startOfMonth()->toDateString();
        $rangeEnd = $this->normalizeDate($request->query('end')) ?? now()->addMonths(10)->endOfMonth()->toDateString();

        if ($rangeStart > $rangeEnd) {
            [$rangeStart, $rangeEnd] = [$rangeEnd, $rangeStart];
        }

        if (! Schema::hasTable('bookings')) {
            return $this->corsJson([
                'data' => [],
                'meta' => [
                    'range_start' => $rangeStart,
                    'range_end' => $rangeEnd,
                    'generated_at' => now()->toIso8601String(),
                ],
            ], $request);
        }

        $dates = DB::table('bookings')
            ->select('bookign_id', 'date_start', 'event_type', 'customer_fullname', 'status', 'payment_status')
            ->whereNotNull('date_start')
            ->where(function ($query): void {
                $query->whereNull('status')
                    ->orWhereRaw("LOWER(TRIM(status)) != 'declined'");
            })
            ->get()
            ->map(function (object $booking): ?array {
                $normalizedDate = $this->normalizeDate($booking->date_start);

                if (! $normalizedDate) {
                    return null;
                }

                return [
                    'date' => $normalizedDate,
                    'label' => Carbon::parse($normalizedDate)->format('l, F j, Y'),
                    'status' => $this->mapFrontendStatus($booking->status),
                    'approval_status' => $booking->status ?: 'active',
                    'payment_status' => $booking->payment_status ?: 'pending',
                    'event_type' => $booking->event_type ?: 'Booked',
                    'customer' => $booking->customer_fullname ?: 'Existing booking',
                    'booking_id' => $booking->bookign_id,
                ];
            })
            ->filter(fn (?array $booking): bool => $booking !== null)
            ->filter(fn (array $booking): bool => $booking['date'] >= $rangeStart && $booking['date'] <= $rangeEnd)
            ->sortBy('date')
            ->unique('date')
            ->values()
            ->all();

        return $this->corsJson([
            'data' => $dates,
            'meta' => [
                'range_start' => $rangeStart,
                'range_end' => $rangeEnd,
                'generated_at' => now()->toIso8601String(),
            ],
        ], $request);
    }

    private function corsJson(array $payload, Request $request): JsonResponse
    {
        $response = response()->json($payload);
        $origin = $request->headers->get('Origin');
        $allowedOrigins = config('app.frontend_urls', ['*']);

        if ($allowedOrigins === ['*']) {
            return $response
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
        }

        if ($origin && in_array($origin, $allowedOrigins, true)) {
            return $response
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->header('Vary', 'Origin');
        }

        return $response;
    }

    private function mapFrontendStatus(?string $status): string
    {
        $normalizedStatus = strtolower(trim((string) $status));

        if ($normalizedStatus === 'approved') {
            return 'booked';
        }

        return 'hold';
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
