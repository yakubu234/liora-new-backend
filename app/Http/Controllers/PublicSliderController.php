<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicSliderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sliders = [];
        $videoSlide = null;

        if (Schema::hasTable('website_sliders')) {
            $sliders = DB::table('website_sliders')
                ->where('status', 'active')
                ->orderBy('id')
                ->limit((int) config('app.website_slider_image_count', 3))
                ->get()
                ->map(fn (object $slider): array => [
                    'id' => $slider->id,
                    'image_url' => asset('uploads/sliders/' . $slider->img),
                    'heading' => $slider->heading,
                    'text' => $slider->text,
                ])
                ->all();
        }

        if (Schema::hasTable('website_slider_videos')) {
            $video = DB::table('website_slider_videos')
                ->where('status', 'active')
                ->orderBy('id')
                ->first();

            if ($video) {
                $extension = strtolower(pathinfo($video->video, PATHINFO_EXTENSION));

                $videoSlide = [
                    'id' => $video->id,
                    'video_url' => asset('uploads/sliders/videos/' . $video->video),
                    'mime_type' => $extension === 'webm' ? 'video/webm' : 'video/mp4',
                    'heading' => $video->heading,
                    'text' => $video->text,
                ];
            }
        }

        return $this->corsJson([
            'data' => $sliders,
            'video' => $videoSlide,
            'meta' => [
                'expected_count' => (int) config('app.website_slider_image_count', 3),
                'expected_video_count' => (int) config('app.website_slider_video_count', 1),
                'actual_count' => count($sliders),
                'actual_video_count' => $videoSlide ? 1 : 0,
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
