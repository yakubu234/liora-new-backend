<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    public function index(): View
    {
        $galleries = collect();

        if (Schema::hasTable('gallery')) {
            $galleries = DB::table('gallery')
                ->where('status', 'active')
                ->orderByDesc('id')
                ->get();
        }

        return view('pages.website.gallery', [
            'galleries' => $galleries,
        ]);
    }

    public function storeSingle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'image', 'max:8192'],
            'heading' => ['required', 'string', 'max:255'],
            'text' => ['nullable', 'string', 'max:5000'],
        ], [
            'file.required' => 'Please choose an image to upload.',
            'heading.required' => 'Please enter image details before uploading.',
        ]);

        $fileName = $this->storeUploadedImage($request->file('file'));

        DB::table('gallery')->insert([
            'img' => $fileName,
            'status' => 'active',
            'created_at' => now(),
            'unpdated_at' => now(),
            'heading' => $validated['heading'],
            'text' => $validated['text'] ?? null,
        ]);

        return redirect()
            ->route('website.gallery')
            ->with('success', 'Image uploaded successfully.');
    }

    public function storeMultiple(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'image', 'max:8192'],
            'heading' => ['nullable', 'string', 'max:255'],
            'text' => ['nullable', 'string', 'max:5000'],
        ], [
            'files.required' => 'Please choose one or more images to upload.',
        ]);

        foreach ($request->file('files', []) as $index => $file) {
            $fileName = $this->storeUploadedImage($file);
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            DB::table('gallery')->insert([
                'img' => $fileName,
                'status' => 'active',
                'created_at' => now(),
                'unpdated_at' => now(),
                'heading' => $validated['heading'] ?: Str::headline($originalName),
                'text' => $validated['text'] ?? null,
            ]);
        }

        return redirect()
            ->route('website.gallery')
            ->with('success', 'Images uploaded successfully.');
    }

    public function destroy(int $galleryId): RedirectResponse
    {
        $gallery = DB::table('gallery')->where('id', $galleryId)->first();
        abort_unless($gallery, 404);

        $imagePath = public_path('uploads/' . $gallery->img);
        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }

        DB::table('gallery')->where('id', $galleryId)->delete();

        return redirect()
            ->route('website.gallery')
            ->with('success', 'Image deleted successfully.');
    }

    private function storeUploadedImage(UploadedFile $file): string
    {
        $uploadDirectory = public_path('uploads');

        if (! File::isDirectory($uploadDirectory)) {
            File::makeDirectory($uploadDirectory, 0755, true);
        }

        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($uploadDirectory, $fileName);

        return $fileName;
    }
}
