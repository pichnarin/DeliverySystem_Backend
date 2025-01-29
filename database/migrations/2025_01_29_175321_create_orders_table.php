<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('address_id');
            $table->string('status');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('payment_method');
            $table->integer('quantity');
            $table->decimal('amount', 8, 2);

            $table->foreign('customer_id')->references('id')->on('people')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('people')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('address_id')->references('id')->on('addresses')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
