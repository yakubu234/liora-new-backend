<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicGalleryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = [];

        if (Schema::hasTable('gallery')) {
            $items = DB::table('gallery')
                ->where('status', 'active')
                ->orderBy('id')
                ->get()
                ->map(fn (object $gallery): array => [
                    'id' => $gallery->id,
                    'image_url' => asset('uploads/' . $gallery->img),
                    'heading' => $gallery->heading,
                    'text' => $gallery->text,
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
