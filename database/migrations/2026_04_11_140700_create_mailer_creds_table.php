<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailer_creds', function (Blueprint $table) {
            $table->string('username', 200);
            $table->text('password');
            $table->string('hosts');
            $table->string('port', 20);
            $table->string('receiver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailer_creds');
    }
};
