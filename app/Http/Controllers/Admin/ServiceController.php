<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::query()
            ->orderByRaw("CASE WHEN LOWER(COALESCE(status, '')) = 'enabled' THEN 0 ELSE 1 END ASC")
            ->orderBy('name')
            ->get();

        return view('pages.services.index', [
            'services' => $services,
            'stats' => [
                'total' => $services->count(),
                'enabled' => $services->where('status', 'enabled')->count(),
                'disabled' => $services->where('status', 'disabled')->count(),
                'value' => $services->sum(fn (Service $service): float => $this->parseMoney($service->price)),
            ],
        ]);
    }

    public function create(): View
    {
        $this->ensureAdminAccess();

        return view('pages.services.form', [
            'service' => new Service([
                'status' => 'enabled',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess();

        $validated = $this->validateService($request);
        $service = Service::create($validated);

        $this->writeAudit("Created service {$service->name}.");

        return redirect()
            ->route('services.index')
            ->with('success', 'Service was created successfully.');
    }

    public function edit(int $serviceId): View
    {
        $this->ensureAdminAccess();

        return view('pages.services.form', [
            'service' => Service::findOrFail($serviceId),
        ]);
    }

    public function update(Request $request, int $serviceId): RedirectResponse
    {
        $this->ensureAdminAccess();

        $service = Service::findOrFail($serviceId);
        $validated = $this->validateService($request, $service);
        $service->update($validated);

        $this->writeAudit("Updated service {$service->name}.");

        return redirect()
            ->route('services.index')
            ->with('success', 'Service was updated successfully.');
    }

    public function destroy(int $serviceId): RedirectResponse
    {
        $this->ensureAdminAccess();

        $service = Service::findOrFail($serviceId);
        $service->update([
            'status' => 'disabled',
        ]);

        $this->writeAudit("Disabled service {$service->name}.");

        return redirect()
            ->route('services.index')
            ->with('success', 'Service was disabled successfully.');
    }

    private function validateService(Request $request, ?Service $service = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:200',
                Rule::unique('services', 'name')->ignore($service?->id),
            ],
            'description' => ['required', 'string', 'max:200'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['enabled', 'disabled'])],
        ]);
    }

    private function ensureAdminAccess(): void
    {
        abort_unless(((int) (Auth::user()?->type ?? 0)) > 0, 403);
    }

    private function parseMoney(string|int|float|null $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) preg_replace('/[^\d.\-]/', '', (string) $value);
    }

    private function writeAudit(string $action): void
    {
        if (! Schema::hasTable('audits')) {
            return;
        }

        $staff = Auth::user();

        DB::table('audits')->insert([
            'user_id' => (string) ($staff?->id ?? ''),
            'user_email' => $staff?->email,
            'action' => $action,
            'created_at' => now(),
            'updated_at' => now(),
            'user_name' => $staff?->fullname ?? $staff?->name,
            'booking_id' => null,
        ]);
    }
}
