<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class TestimonialController extends Controller
{
    public function index(): View
    {
        $testimonials = collect();

        if (Schema::hasTable('testimonials')) {
            $testimonials = DB::table('testimonials')
                ->orderBy('id')
                ->get();
        }

        return view('pages.website.testimonials', [
            'testimonials' => $testimonials,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        DB::table('testimonials')->insert([
            'quote' => $validated['quote'],
            'name' => $validated['name'],
            'role' => $validated['role'],
            'rating' => $validated['rating'],
            'status' => $validated['status'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('website.testimonials')
            ->with('success', 'Testimonial created successfully.');
    }

    public function update(Request $request, int $testimonialId): RedirectResponse
    {
        $testimonial = DB::table('testimonials')->where('id', $testimonialId)->first();
        abort_unless($testimonial, 404);

        $validated = $this->validatePayload($request);

        DB::table('testimonials')
            ->where('id', $testimonialId)
            ->update([
                'quote' => $validated['quote'],
                'name' => $validated['name'],
                'role' => $validated['role'],
                'rating' => $validated['rating'],
                'status' => $validated['status'],
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('website.testimonials')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(int $testimonialId): RedirectResponse
    {
        $testimonial = DB::table('testimonials')->where('id', $testimonialId)->first();
        abort_unless($testimonial, 404);

        DB::table('testimonials')->where('id', $testimonialId)->delete();

        return redirect()
            ->route('website.testimonials')
            ->with('success', 'Testimonial deleted successfully.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'quote' => ['required', 'string', 'max:5000'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }
}
