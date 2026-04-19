<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 200);
                $table->string('slug', 200)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name', 200);
                $table->string('slug', 200)->unique();
                $table->string('group_name', 200)->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['permission_id', 'role_id']);
            });
        }

        if (! Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->unsignedInteger('user_id');
                $table->timestamps();
                $table->unique(['role_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('permission_user')) {
            Schema::create('permission_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->unsignedInteger('user_id');
                $table->timestamps();
                $table->unique(['permission_id', 'user_id']);
            });
        }

        $permissions = [
            ['name' => 'View Dashboard', 'slug' => 'view-dashboard', 'group_name' => 'Dashboard', 'description' => 'Can access the admin dashboard and summary cards.'],
            ['name' => 'Manage Bookings', 'slug' => 'manage-bookings', 'group_name' => 'Bookings', 'description' => 'Can create, edit, and review bookings.'],
            ['name' => 'Approve Bookings', 'slug' => 'approve-bookings', 'group_name' => 'Bookings', 'description' => 'Can approve or decline bookings.'],
            ['name' => 'Manage Payments', 'slug' => 'manage-payments', 'group_name' => 'Payments', 'description' => 'Can add balance payments and review payment status.'],
            ['name' => 'Manage Services', 'slug' => 'manage-services', 'group_name' => 'Services', 'description' => 'Can manage services and booking options.'],
            ['name' => 'Manage Settings', 'slug' => 'manage-settings', 'group_name' => 'Settings', 'description' => 'Can manage application settings such as agreements and event types.'],
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'group_name' => 'Users', 'description' => 'Can create users, assign access, and review user history.'],
            ['name' => 'Manage Gallery', 'slug' => 'manage-gallery', 'group_name' => 'Website', 'description' => 'Can upload or delete gallery items.'],
            ['name' => 'View Reports', 'slug' => 'view-reports', 'group_name' => 'Reports', 'description' => 'Can view reports and booking history.'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                array_merge($permission, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $roles = [
            ['name' => 'User', 'slug' => 'user', 'description' => 'Basic staff account with limited access.', 'is_system' => true],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Administrative account for bookings and settings.', 'is_system' => true],
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full access account with delete capability and total control.', 'is_system' => true],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role['slug']],
                array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $permissionIds = DB::table('permissions')->pluck('id', 'slug');
        $roleIds = DB::table('roles')->pluck('id', 'slug');

        $rolePermissions = [
            'user' => ['view-dashboard', 'view-reports'],
            'admin' => ['view-dashboard', 'manage-bookings', 'approve-bookings', 'manage-payments', 'manage-services', 'manage-settings', 'manage-gallery', 'view-reports'],
            'super-admin' => ['view-dashboard', 'manage-bookings', 'approve-bookings', 'manage-payments', 'manage-services', 'manage-settings', 'manage-users', 'manage-gallery', 'view-reports'],
        ];

        foreach ($rolePermissions as $roleSlug => $permissionSlugs) {
            $roleId = $roleIds[$roleSlug] ?? null;
            if (! $roleId) {
                continue;
            }

            foreach ($permissionSlugs as $permissionSlug) {
                $permissionId = $permissionIds[$permissionSlug] ?? null;
                if (! $permissionId) {
                    continue;
                }

                DB::table('permission_role')->updateOrInsert(
                    [
                        'permission_id' => $permissionId,
                        'role_id' => $roleId,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')->select('id', 'type')->get();

            foreach ($users as $user) {
                $roleSlug = ((int) $user->type) >= 5 ? 'super-admin' : (((int) $user->type) > 0 ? 'admin' : 'user');
                $roleId = $roleIds[$roleSlug] ?? null;

                if (! $roleId) {
                    continue;
                }

                DB::table('role_user')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'user_id' => $user->id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
