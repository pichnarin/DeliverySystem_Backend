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
        Schema::create('order_details', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->string('food_name');
            $table->string('quantity');
            $table->string('price');
            $table->string('sub_total');
            $table->string('note')->nullable();

            $table->foreignId('food_id')->constrained('food')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
