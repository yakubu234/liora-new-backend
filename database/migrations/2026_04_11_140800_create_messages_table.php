<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('recepient', 200);
            $table->string('name', 200)->nullable();
            $table->string('email', 200)->nullable();
            $table->integer('user_id')->nullable();
            $table->text('message');
            $table->string('subject', 200);
            $table->text('reply')->nullable();
            $table->integer('responder_id')->nullable();
            $table->string('status', 200)->nullable()->default('active');
            $table->string('responder_name', 200)->nullable();
            $table->boolean('is_read')->nullable()->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        DB::statement("ALTER TABLE `messages` AUTO_INCREMENT = 73");
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
