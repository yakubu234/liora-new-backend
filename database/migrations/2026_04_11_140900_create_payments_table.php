<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('amount');
            $table->string('booking_id');
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement("ALTER TABLE `payments` AUTO_INCREMENT = 387");
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
