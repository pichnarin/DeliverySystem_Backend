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
        Schema::create('driver_trackings', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->string('latitude');
            $table->string('longitude');

            $table->foreignId('driver_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('order_id')->references('id')->on('orders')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('address_id')->references('id')->on('addresses')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_trackings');
    }
};
