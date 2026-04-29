<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->increments('id');
            $table->text('quote');
            $table->string('name', 255);
            $table->string('role', 255);
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('status', 50)->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
