<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MessageController extends Controller
{
    public function index(): View
    {
        $messages = Message::query()
            ->orderByDesc('created_at')
            ->get();

        return view('pages.messages.index', [
            'messages' => $messages,
            'stats' => $this->stats(),
            'mode' => 'all',
            'canDelete' => $this->isSuperAdmin(),
        ]);
    }

    public function unread(): View
    {
        $messages = Message::query()
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->get();

        return view('pages.messages.index', [
            'messages' => $messages,
            'stats' => $this->stats(),
            'mode' => 'new',
            'canDelete' => $this->isSuperAdmin(),
        ]);
    }

    public function show(int $messageId): View
    {
        $message = Message::query()->findOrFail($messageId);

        if (! $message->is_read) {
            $message->forceFill(['is_read' => true])->save();
        }

        return view('pages.messages.show', [
            'messageRecord' => $message->fresh(),
            'canDelete' => $this->isSuperAdmin(),
        ]);
    }

    public function destroy(int $messageId): RedirectResponse
    {
        abort_unless($this->isSuperAdmin(), 403);

        $message = Message::query()->findOrFail($messageId);
        $summary = trim(($message->subject ?: 'Message') . ' / ' . ($message->name ?: 'Guest'));

        $message->delete();
        $this->writeAudit("Deleted message {$summary}.");

        return redirect()
            ->route('messages.index')
            ->with('success', 'Message deleted successfully.');
    }

    private function stats(): array
    {
        return [
            'total' => Message::query()->count(),
            'new' => Message::query()->where('is_read', false)->count(),
            'read' => Message::query()->where('is_read', true)->count(),
            'today' => Message::query()->whereDate('created_at', today())->count(),
        ];
    }

    private function isSuperAdmin(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (((int) ($user->type ?? 0)) >= 5) {
            return true;
        }

        return $user->roles()
            ->where('slug', 'super-admin')
            ->exists();
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
