<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicMessageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:200'],
            'email' => ['required', 'email', 'max:200'],
            'phone' => ['nullable', 'string', 'max:100'],
            'event_type' => ['nullable', 'string', 'max:200'],
            'event_date' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return $this->corsJson([
                'message' => 'Please correct the highlighted form details.',
                'errors' => $validator->errors()->toArray(),
            ], $request, 422);
        }

        $validated = $validator->validated();

        $subjectParts = array_filter([
            'Website event inquiry',
            $validated['event_type'] ?? null,
        ]);

        Message::query()->create([
            'recepient' => 'website-contact',
            'name' => $validated['full_name'],
            'email' => $validated['email'],
            'subject' => implode(' - ', $subjectParts),
            'message' => $this->buildMessageBody($validated),
            'status' => 'active',
            'is_read' => false,
        ]);

        return $this->corsJson([
            'message' => 'Your message has been sent successfully.',
        ], $request);
    }

    private function buildMessageBody(array $validated): string
    {
        $lines = [
            'Phone: ' . ($validated['phone'] ?: 'Not provided'),
            'Event Type: ' . ($validated['event_type'] ?: 'Not provided'),
            'Preferred Date: ' . ($validated['event_date'] ?: 'Not provided'),
            '',
            'Message:',
            $validated['message'],
        ];

        return implode("\n", $lines);
    }

    private function corsJson(array $payload, Request $request, int $status = 200): JsonResponse
    {
        $response = response()->json($payload, $status);
        $origin = $request->headers->get('Origin');
        $allowedOrigins = config('app.frontend_urls', ['*']);

        if ($allowedOrigins === ['*']) {
            return $response
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'POST, OPTIONS');
        }

        if ($origin && in_array($origin, $allowedOrigins, true)) {
            return $response
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                ->header('Vary', 'Origin');
        }

        return $response;
    }
}
