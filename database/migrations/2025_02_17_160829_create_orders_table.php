<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->engine('InnoDB');
            $table->id();
            $table->string('order_number');

            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('address_id')->constrained('addresses')->onDelete('cascade')->onUpdate('cascade');

            $table->enum('status', [
                'pending', // Order placed, waiting for restaurant approval
                'accepted', // Restaurant accepted order
                'preparing', // Restaurant is preparing the order
                'ready_for_delivery', // Order is ready for pickup
                'assigned_to_driver', // Driver has been assigned
                'picked_up', // Driver picked up the order
                'delivering', // Order is in transit
                'completed', // Order delivered successfully
                'declined', // Order was rejected
                'canceled' // Order canceled by customer
            ])->default('pending');
            $table->integer('quantity');
            $table->decimal('total', 10, 2);
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('discount', 10, 2);

            $table->enum('payment_method', ['ABA', 'PAID OUT', 'UN PAID'])->default('UN PAID');

            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();


            $table->timestamp('estimated_delivery_time')->nullable();
            $table->text('notes')->nullable();
            
            // Add timestamps
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
