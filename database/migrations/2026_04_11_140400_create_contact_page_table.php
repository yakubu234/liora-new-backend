<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_page', function (Blueprint $table) {
            $table->increments('id');
            $table->text('address');
            $table->string('phone', 200);
            $table->string('email', 200);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        DB::statement("ALTER TABLE `contact_page` AUTO_INCREMENT = 2");
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_page');
    }
};
