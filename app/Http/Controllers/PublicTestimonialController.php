<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicTestimonialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = [];

        if (Schema::hasTable('testimonials')) {
            $items = DB::table('testimonials')
                ->where('status', 'active')
                ->orderBy('id')
                ->get()
                ->map(fn (object $testimonial): array => [
                    'id' => $testimonial->id,
                    'quote' => $testimonial->quote,
                    'name' => $testimonial->name,
                    'role' => $testimonial->role,
                    'rating' => (int) $testimonial->rating,
                ])
                ->all();
        }

        return $this->corsJson([
            'data' => $items,
            'meta' => [
                'preview_count' => 3,
                'actual_count' => count($items),
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
}
