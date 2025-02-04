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
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('address_id');
            $table->string('latitude');
            $table->string('longitude');
            $table->foreign('driver_id')->references('id')->on('people')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('address_id')->references('id')->on('addresses')->onUpdate('cascade')->onDelete('cascade');
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
