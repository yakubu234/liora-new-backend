<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200);
            $table->string('status', 200)->nullable()->default('enabled');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        DB::statement("ALTER TABLE `event_type` AUTO_INCREMENT = 32");
    }

    public function down(): void
    {
        Schema::dropIfExists('event_type');
    }
};
