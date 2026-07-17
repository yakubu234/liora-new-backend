<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_settings', function (Blueprint $table): void {
            $table->id();
            $table->decimal('rate', 8, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('pre_tax_deductions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->decimal('amount', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->decimal('tax_rate', 8, 4)->default(0)->after('tax');
            $table->decimal('taxable_amount', 15, 2)->default(0)->after('tax_rate');
            $table->json('pre_tax_deductions')->nullable()->after('taxable_amount');
        });

        DB::table('tax_settings')->insert([
            'rate' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('pre_tax_deductions')->insert([
            'name' => 'Traffic Managers',
            'amount' => 50000,
            'is_active' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn(['tax_rate', 'taxable_amount', 'pre_tax_deductions']);
        });

        Schema::dropIfExists('pre_tax_deductions');
        Schema::dropIfExists('tax_settings');
    }
};
