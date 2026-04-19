<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id')->nullable();
            $table->string('user_email', 244)->nullable();
            $table->text('action')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->string('user_name', 200)->nullable();
            $table->string('booking_id', 250)->nullable();
        });

        DB::statement("ALTER TABLE `audits` AUTO_INCREMENT = 1470");
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
