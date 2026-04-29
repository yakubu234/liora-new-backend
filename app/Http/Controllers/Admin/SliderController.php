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
use Illuminate\Validation\ValidationException;

class SliderController extends Controller
{
    public function index(): View
    {
        $sliders = collect();
        $videoSlides = collect();

        if (Schema::hasTable('website_sliders')) {
            $sliders = DB::table('website_sliders')
                ->where('status', 'active')
                ->orderBy('id')
                ->get();
        }

        if (Schema::hasTable('website_slider_videos')) {
            $videoSlides = DB::table('website_slider_videos')
                ->where('status', 'active')
                ->orderBy('id')
                ->get();
        }

        $expectedCount = $this->expectedCount();

        return view('pages.website.slider', [
            'sliders' => $sliders,
            'videoSlides' => $videoSlides,
            'expectedCount' => $expectedCount,
            'remainingSlots' => max($expectedCount - $sliders->count(), 0),
            'expectedVideoCount' => $this->expectedVideoCount(),
        ]);
    }

    public function storeSingle(Request $request): RedirectResponse
    {
        $this->ensureCapacityFor(1);

        $validated = $request->validate([
            'file' => ['required', 'image', 'max:10240'],
            'heading' => ['nullable', 'string', 'max:255'],
            'text' => ['nullable', 'string', 'max:5000'],
        ], [
            'file.required' => 'Please choose a slider image to upload.',
        ]);

        $fileName = $this->storeOptimizedSliderImage($request->file('file'));

        DB::table('website_sliders')->insert([
            'img' => $fileName,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
            'heading' => $validated['heading'] ?? null,
            'text' => $validated['text'] ?? null,
        ]);

        return redirect()
            ->route('website.slider')
            ->with('success', 'Slider image uploaded successfully.');
    }

    public function storeMultiple(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'image', 'max:10240'],
            'heading' => ['nullable', 'string', 'max:255'],
            'text' => ['nullable', 'string', 'max:5000'],
        ], [
            'files.required' => 'Please choose one or more slider images to upload.',
        ]);

        $files = $request->file('files', []);
        $this->ensureCapacityFor(count($files));

        foreach ($files as $file) {
            $fileName = $this->storeOptimizedSliderImage($file);
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            DB::table('website_sliders')->insert([
                'img' => $fileName,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
                'heading' => $validated['heading'] ?: Str::headline($originalName),
                'text' => $validated['text'] ?? null,
            ]);
        }

        return redirect()
            ->route('website.slider')
            ->with('success', 'Slider images uploaded successfully.');
    }

    public function destroy(int $sliderId): RedirectResponse
    {
        $slider = DB::table('website_sliders')->where('id', $sliderId)->first();
        abort_unless($slider, 404);

        $imagePath = public_path('uploads/sliders/' . $slider->img);
        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }

        DB::table('website_sliders')->where('id', $sliderId)->delete();

        return redirect()
            ->route('website.slider')
            ->with('success', 'Slider image deleted successfully.');
    }

    public function storeVideo(Request $request): RedirectResponse
    {
        $this->ensureVideoCapacity();

        $validated = $request->validate([
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/webm', 'max:51200'],
            'heading' => ['nullable', 'string', 'max:255'],
            'text' => ['nullable', 'string', 'max:5000'],
        ], [
            'video.required' => 'Please choose a hero slider video to upload.',
            'video.mimetypes' => 'Only MP4 and WebM videos are supported for the hero slider.',
        ]);

        $fileName = $this->storeSliderVideo($request->file('video'));

        DB::table('website_slider_videos')->insert([
            'video' => $fileName,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
            'heading' => $validated['heading'] ?? null,
            'text' => $validated['text'] ?? null,
        ]);

        return redirect()
            ->route('website.slider')
            ->with('success', 'Slider video uploaded successfully.');
    }

    public function destroyVideo(int $videoId): RedirectResponse
    {
        $videoSlide = DB::table('website_slider_videos')->where('id', $videoId)->first();
        abort_unless($videoSlide, 404);

        $videoPath = public_path('uploads/sliders/videos/' . $videoSlide->video);
        if (File::exists($videoPath)) {
            File::delete($videoPath);
        }

        DB::table('website_slider_videos')->where('id', $videoId)->delete();

        return redirect()
            ->route('website.slider')
            ->with('success', 'Slider video deleted successfully.');
    }

    private function ensureCapacityFor(int $incomingCount): void
    {
        $activeCount = Schema::hasTable('website_sliders')
            ? DB::table('website_sliders')->where('status', 'active')->count()
            : 0;

        $remainingSlots = $this->expectedCount() - $activeCount;

        if ($incomingCount > $remainingSlots) {
            throw ValidationException::withMessages([
                'files' => sprintf(
                    'Only %d slider slot(s) remain. The homepage slider expects exactly %d images.',
                    max($remainingSlots, 0),
                    $this->expectedCount()
                ),
            ]);
        }
    }

    private function expectedCount(): int
    {
        return (int) config('app.website_slider_image_count', 3);
    }

    private function expectedVideoCount(): int
    {
        return (int) config('app.website_slider_video_count', 1);
    }

    private function storeOptimizedSliderImage(UploadedFile $file): string
    {
        $uploadDirectory = public_path('uploads/sliders');

        if (! File::isDirectory($uploadDirectory)) {
            File::makeDirectory($uploadDirectory, 0755, true);
        }

        $fileName = Str::uuid() . '.webp';
        $targetPath = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;

        if ($this->convertToWebp($file, $targetPath)) {
            return $fileName;
        }

        $fallbackName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($uploadDirectory, $fallbackName);

        return $fallbackName;
    }

    private function storeSliderVideo(UploadedFile $file): string
    {
        $uploadDirectory = public_path('uploads/sliders/videos');

        if (! File::isDirectory($uploadDirectory)) {
            File::makeDirectory($uploadDirectory, 0755, true);
        }

        $fileName = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
        $file->move($uploadDirectory, $fileName);

        return $fileName;
    }

    private function ensureVideoCapacity(): void
    {
        $activeCount = Schema::hasTable('website_slider_videos')
            ? DB::table('website_slider_videos')->where('status', 'active')->count()
            : 0;

        if ($activeCount >= $this->expectedVideoCount()) {
            throw ValidationException::withMessages([
                'video' => sprintf(
                    'The homepage slider expects exactly %d active video slide. Remove the current video before uploading another one.',
                    $this->expectedVideoCount()
                ),
            ]);
        }
    }

    private function convertToWebp(UploadedFile $file, string $targetPath): bool
    {
        if (! function_exists('imagewebp')) {
            return false;
        }

        $imageInfo = @getimagesize($file->getPathname());
        if (! $imageInfo) {
            return false;
        }

        [$sourceWidth, $sourceHeight, $sourceType] = $imageInfo;
        $sourceImage = match ($sourceType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file->getPathname()),
            IMAGETYPE_PNG => @imagecreatefrompng($file->getPathname()),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getPathname()) : false,
            default => false,
        };

        if (! $sourceImage) {
            return false;
        }

        $maxWidth = 1920;
        $maxHeight = 1080;
        $scale = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight, 1);
        $targetWidth = max((int) round($sourceWidth * $scale), 1);
        $targetHeight = max((int) round($sourceHeight * $scale), 1);

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        imagecopyresampled(
            $canvas,
            $sourceImage,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        $saved = imagewebp($canvas, $targetPath, 82);

        imagedestroy($canvas);
        imagedestroy($sourceImage);

        return $saved;
    }
}
