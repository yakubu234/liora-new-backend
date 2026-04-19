<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContactPageController extends Controller
{
    public function edit(): View
    {
        return view('pages.website.contact', [
            'contact' => ContactPage::query()->first() ?? new ContactPage(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:200'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:200'],
        ]);

        $contact = ContactPage::query()->first();

        if ($contact) {
            $contact->update($validated);
            $this->writeAudit('Updated website contact information.');
        } else {
            ContactPage::create($validated);
            $this->writeAudit('Created website contact information.');
        }

        return redirect()
            ->route('website.contact')
            ->with('success', 'Contact information was saved successfully.');
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
