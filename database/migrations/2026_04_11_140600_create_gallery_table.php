<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery', function (Blueprint $table) {
            $table->increments('id');
            $table->string('img', 200);
            $table->string('status', 200)->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('unpdated_at')->useCurrent();
            $table->text('heading')->nullable();
            $table->text('text')->nullable();
        });

        DB::statement("ALTER TABLE `gallery` AUTO_INCREMENT = 55");
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery');
    }
};
