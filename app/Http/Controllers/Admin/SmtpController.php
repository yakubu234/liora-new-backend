<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SmtpController extends Controller
{
    private const DEFAULT_RECIPIENT = 'info@lioracityeventcenter.com';
    private const DEFAULT_HOST = 'smtp.zohocloud.ca';
    private const DEFAULT_PORT = '587';

    public function edit(): View
    {
        $config = DB::table('mailer_creds')->first();

        return view('pages.website.smtp', [
            'config' => $config,
            'defaultRecipient' => self::DEFAULT_RECIPIENT,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'username' => ['required', 'email', 'max:200'],
            'password' => ['required', 'string'],
            'receiver_id' => ['required', 'email', 'max:200'],
        ]);

        $existing = DB::table('mailer_creds')->first();
        $payload = [
            'username' => $validated['username'],
            'password' => $validated['password'],
            'receiver_id' => $validated['receiver_id'],
            'hosts' => $existing?->hosts ?: self::DEFAULT_HOST,
            'port' => $existing?->port ?: self::DEFAULT_PORT,
        ];

        if ($existing) {
            DB::table('mailer_creds')->update($payload);
            $this->writeAudit('Updated SMTP notification settings.');
        } else {
            DB::table('mailer_creds')->insert($payload);
            $this->writeAudit('Created SMTP notification settings.');
        }

        return redirect()
            ->route('website.smtp')
            ->with('success', 'SMTP details were saved successfully.');
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
