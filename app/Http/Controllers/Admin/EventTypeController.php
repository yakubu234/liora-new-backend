<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class EventTypeController extends Controller
{
    public function index(): View
    {
        $eventTypes = EventType::query()
            ->where('status', 'enabled')
            ->orderBy('name')
            ->get();

        return view('pages.settings.event-types.index', [
            'eventTypes' => $eventTypes,
            'stats' => [
                'enabled' => $eventTypes->count(),
                'total' => EventType::count(),
                'disabled' => EventType::where('status', 'disabled')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->ensureAdminAccess();

        return view('pages.settings.event-types.form', [
            'eventType' => new EventType([
                'status' => 'enabled',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess();

        $validated = $this->validateEventType($request);
        $eventType = EventType::create($validated);

        $this->writeAudit("Created event type {$eventType->name}.");

        return redirect()
            ->route('settings.event-types')
            ->with('success', 'Event type was created successfully.');
    }

    public function edit(int $eventTypeId): View
    {
        $this->ensureAdminAccess();

        return view('pages.settings.event-types.form', [
            'eventType' => EventType::findOrFail($eventTypeId),
        ]);
    }

    public function update(Request $request, int $eventTypeId): RedirectResponse
    {
        $this->ensureAdminAccess();

        $eventType = EventType::findOrFail($eventTypeId);
        $validated = $this->validateEventType($request, $eventType);
        $eventType->update($validated);

        $this->writeAudit("Updated event type {$eventType->name}.");

        return redirect()
            ->route('settings.event-types')
            ->with('success', 'Event type was updated successfully.');
    }

    public function destroy(int $eventTypeId): RedirectResponse
    {
        $this->ensureAdminAccess();

        $eventType = EventType::findOrFail($eventTypeId);
        $eventType->update([
            'status' => 'disabled',
        ]);

        $this->writeAudit("Disabled event type {$eventType->name}.");

        return redirect()
            ->route('settings.event-types')
            ->with('success', 'Event type was disabled successfully.');
    }

    private function validateEventType(Request $request, ?EventType $eventType = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:200',
                Rule::unique('event_type', 'name')->ignore($eventType?->id),
            ],
            'status' => ['required', Rule::in(['enabled', 'disabled'])],
        ]);
    }

    private function ensureAdminAccess(): void
    {
        abort_unless(((int) (Auth::user()?->type ?? 0)) > 0, 403);
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
