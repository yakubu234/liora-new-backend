<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200);
            $table->string('description', 200);
            $table->string('price', 200);
            $table->string('status', 200)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        DB::statement("ALTER TABLE `services` AUTO_INCREMENT = 58");
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
