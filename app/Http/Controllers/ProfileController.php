<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user()->loadMissing(['roles', 'permissions']);

        return view('pages.profile.edit', [
            'userRecord' => $user,
            'effectivePermissions' => $this->effectivePermissions($user),
        ]);
    }

    public function updateDetails(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $emailUnique = Rule::unique('users', 'email')->ignore($user->id);
        $usernameUnique = Rule::unique('users', 'username')->ignore($user->id);

        if (Schema::hasColumn('users', 'deleted_at')) {
            $emailUnique = $emailUnique->whereNull('deleted_at');
            $usernameUnique = $usernameUnique->whereNull('deleted_at');
        }

        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:200'],
            'username' => ['required', 'string', 'max:200', $usernameUnique],
            'email' => ['required', 'email', 'max:200', $emailUnique],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $user->update($validated);

        $this->writeAudit("Updated personal profile details for {$user->fullname}.");

        return redirect()
            ->route('profile.user')
            ->with('success', 'Your profile details were updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.confirmed' => 'The new password confirmation does not match.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors(['current_password' => 'The current password you entered is not correct.'])
                ->withInput($request->except(['current_password', 'password', 'password_confirmation']));
        }

        $user->update([
            'password' => $validated['password'],
        ]);

        $this->writeAudit("Changed account password for {$user->fullname}.");

        return redirect()
            ->route('profile.user')
            ->with('success', 'Your password was updated successfully.');
    }

    private function effectivePermissions($user)
    {
        $rolePermissions = $user->roles
            ->flatMap(fn ($role) => $role->permissions)
            ->keyBy('id');

        return $rolePermissions
            ->merge($user->permissions->keyBy('id'))
            ->sortBy(['group_name', 'name'])
            ->values();
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
