<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{

    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fullname', 200)->nullable();
            $table->string('username', 200)->nullable();
            $table->string('email', 200)->nullable();
            $table->text('password');
            $table->string('phone', 15)->nullable();
            $table->string('status', 200)->nullable();
            $table->string('type', 200);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        // DB::statement("ALTER TABLE `users` AUTO_INCREMENT = 9");
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
