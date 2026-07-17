<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pre_tax_deductions', function (Blueprint $table): void {
            $table->boolean('is_default')->default(false)->after('is_active');
        });

        DB::table('pre_tax_deductions')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['traffic managers'])
            ->update(['is_default' => true]);
    }

    public function down(): void
    {
        Schema::table('pre_tax_deductions', function (Blueprint $table): void {
            $table->dropColumn('is_default');
        });
    }
};
