<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $this->ensureAdminAccess();

        $users = User::withTrashed()
            ->with(['roles.permissions', 'permissions'])
            ->orderByRaw('CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END ASC')
            ->orderByRaw("CASE WHEN LOWER(COALESCE(status, '')) = 'active' THEN 0 WHEN LOWER(COALESCE(status, '')) = 'inactive' THEN 1 ELSE 2 END ASC")
            ->orderBy('fullname')
            ->get();

        $roles = Role::withCount('users')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        $permissions = Permission::orderBy('group_name')
            ->orderBy('name')
            ->get();

        return view('pages.settings.users.index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'stats' => [
                'total' => $users->count(),
                'active' => $users->filter(fn (User $user): bool => ! $user->trashed() && $user->status === 'active')->count(),
                'deleted' => $users->filter(fn (User $user): bool => $user->trashed())->count(),
                'admins' => $users->filter(fn (User $user): bool => ! $user->trashed() && (int) $user->type > 0)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->ensureAdminAccess();

        return view('pages.settings.users.form', $this->formData(new User([
            'status' => 'active',
            'type' => '0',
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess();

        $validated = $this->validateUser($request);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create([
                'fullname' => $validated['fullname'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => $validated['status'],
                'type' => $validated['type'],
                'password' => $validated['password'],
            ]);

            $this->syncAccess($user, $validated['roles'] ?? [], $validated['permissions'] ?? []);
            $this->writeAudit("Created user {$user->fullname} ({$user->email}).");

            return $user;
        });

        return redirect()
            ->route('settings.users.show', $user->id)
            ->with('success', 'User account was created successfully.');
    }

    public function show(int $userId): View
    {
        $this->ensureAdminAccess();

        $user = $this->findUser($userId);

        $audits = collect();
        if (Schema::hasTable('audits')) {
            $audits = DB::table('audits')
                ->where(function ($query) use ($user): void {
                    $query->where('user_id', (string) $user->id)
                        ->orWhere('user_email', $user->email);
                })
                ->orderByDesc('created_at')
                ->get();
        }

        return view('pages.settings.users.show', [
            'userRecord' => $user->load(['roles.permissions', 'permissions']),
            'audits' => $audits,
            'effectivePermissions' => $this->effectivePermissions($user),
        ]);
    }

    public function edit(int $userId): View
    {
        $this->ensureAdminAccess();

        return view('pages.settings.users.form', $this->formData($this->findUser($userId)));
    }

    public function update(Request $request, int $userId): RedirectResponse
    {
        $this->ensureAdminAccess();

        $user = $this->findUser($userId);
        $validated = $this->validateUser($request, $user);

        DB::transaction(function () use ($user, $validated): void {
            $user->update([
                'fullname' => $validated['fullname'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => $validated['status'],
                'type' => $validated['type'],
            ]);

            if (! empty($validated['password'])) {
                $user->update([
                    'password' => $validated['password'],
                ]);

                $this->writeAudit("Updated password for user {$user->fullname} ({$user->email}).");
            }

            $this->syncAccess($user, $validated['roles'] ?? [], $validated['permissions'] ?? []);
            $this->writeAudit("Updated user {$user->fullname} ({$user->email}).");
        });

        return redirect()
            ->route('settings.users.show', $user->id)
            ->with('success', 'User record was updated successfully.');
    }

    public function destroy(int $userId): RedirectResponse
    {
        $this->ensureAdminAccess();

        $user = $this->findUser($userId);

        abort_if((int) (Auth::id() ?? 0) === (int) $user->id, 422, 'You cannot delete your own account.');

        $user->delete();
        $this->writeAudit("Soft deleted user {$user->fullname} ({$user->email}).");

        return redirect()
            ->route('settings.users')
            ->with('success', 'User was soft deleted successfully.');
    }

    public function restore(int $userId): RedirectResponse
    {
        $this->ensureAdminAccess();

        $user = $this->findUser($userId);
        $user->restore();

        $this->writeAudit("Restored user {$user->fullname} ({$user->email}).");

        return redirect()
            ->route('settings.users.show', $user->id)
            ->with('success', 'User was restored successfully.');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);
        $this->writeAudit("Created role {$role->name}.");

        return redirect()
            ->route('settings.users')
            ->with('success', 'Role was created successfully.');
    }

    public function storePermission(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200', 'unique:permissions,name'],
            'group_name' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'group_name' => $validated['group_name'] ?? 'General',
            'description' => $validated['description'] ?? null,
        ]);

        $this->writeAudit("Created permission {$permission->name}.");

        return redirect()
            ->route('settings.users')
            ->with('success', 'Permission was created successfully.');
    }

    private function formData(User $user): array
    {
        $user->loadMissing(['roles', 'permissions']);

        return [
            'userRecord' => $user,
            'roles' => Role::orderBy('name')->get(),
            'permissions' => Permission::orderBy('group_name')->orderBy('name')->get()->groupBy(fn (Permission $permission) => $permission->group_name ?: 'General'),
            'selectedRoleIds' => $user->roles->pluck('id')->all(),
            'selectedPermissionIds' => $user->permissions->pluck('id')->all(),
            'legacyTypes' => [
                '0' => 'User',
                '2' => 'Admin',
                '5' => 'Super Admin',
            ],
        ];
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $userId = $user?->id;
        $passwordRules = $user ? ['nullable', 'string', 'min:8', 'confirmed'] : ['required', 'string', 'min:8', 'confirmed'];
        $usernameUnique = Rule::unique('users', 'username')->ignore($userId);
        $emailUnique = Rule::unique('users', 'email')->ignore($userId);

        if (Schema::hasColumn('users', 'deleted_at')) {
            $usernameUnique = $usernameUnique->whereNull('deleted_at');
            $emailUnique = $emailUnique->whereNull('deleted_at');
        }

        return $request->validate([
            'fullname' => ['required', 'string', 'max:200'],
            'username' => ['required', 'string', 'max:200', $usernameUnique],
            'email' => ['required', 'email', 'max:200', $emailUnique],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'type' => ['required', Rule::in(['0', '2', '5'])],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
            'password' => $passwordRules,
        ]);
    }

    private function syncAccess(User $user, array $roleIds = [], array $permissionIds = []): void
    {
        if (empty($roleIds)) {
            $defaultRole = Role::query()
                ->where('slug', $this->legacyRoleSlug((int) $user->type))
                ->value('id');

            if ($defaultRole) {
                $roleIds = [$defaultRole];
            }
        }

        $user->roles()->sync(array_map('intval', $roleIds));
        $user->permissions()->sync(array_map('intval', $permissionIds));
    }

    private function effectivePermissions(User $user): Collection
    {
        $rolePermissions = $user->roles
            ->flatMap(fn (Role $role) => $role->permissions)
            ->keyBy('id');

        return $rolePermissions
            ->merge($user->permissions->keyBy('id'))
            ->sortBy(['group_name', 'name'])
            ->values();
    }

    private function findUser(int $userId): User
    {
        return User::withTrashed()
            ->with(['roles.permissions', 'permissions'])
            ->findOrFail($userId);
    }

    private function legacyRoleSlug(int $type): string
    {
        if ($type >= 5) {
            return 'super-admin';
        }

        if ($type > 0) {
            return 'admin';
        }

        return 'user';
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

    private function ensureAdminAccess(): void
    {
        abort_unless(((int) (Auth::user()?->type ?? 0)) > 0, 403);
    }
}
