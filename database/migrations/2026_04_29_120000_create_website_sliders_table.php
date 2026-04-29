<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_sliders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('img', 200);
            $table->string('status', 200)->default('active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->text('heading')->nullable();
            $table->text('text')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_sliders');
    }
};
