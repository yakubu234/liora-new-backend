<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('bookign_id', 200);
            $table->string('user_id', 200)->nullable();
            $table->string('date_start', 200)->nullable();
            $table->string('time_start', 200)->nullable();
            $table->string('time_end', 200)->nullable();
            $table->string('event_type', 200)->nullable();
            $table->string('number_of_guest', 200)->nullable();
            $table->text('message')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status', 200)->nullable();
            $table->string('admin_id', 200)->nullable();
            $table->string('tax', 200)->nullable();
            $table->string('total_amount', 200)->nullable();
            $table->string('discount', 200)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->string('date_of_application', 200)->nullable();
            $table->string('customer_contact_person_phone')->nullable();
            $table->string('customer_contact_person_fullname')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_fullname')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('payment_status', 200)->nullable();
        });

        DB::statement("ALTER TABLE `bookings` AUTO_INCREMENT = 102");
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
