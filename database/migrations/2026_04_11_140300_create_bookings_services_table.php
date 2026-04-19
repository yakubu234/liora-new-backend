<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings_services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('service_id', 200);
            $table->string('name', 200);
            $table->string('bookings_id', 200);
            $table->string('amount', 200);
            $table->string('status', 200)->nullable();
            $table->string('discount', 200)->nullable();
            $table->string('description', 200);
            $table->string('quantity', 200);
            $table->string('customer_email')->nullable();
            $table->string('customer_fullname')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('customer_contact_person_fullname')->nullable();
            $table->string('customer_contact_person_phone')->nullable();
        });

        DB::statement("ALTER TABLE `bookings_services` AUTO_INCREMENT = 680");
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings_services');
    }
};
