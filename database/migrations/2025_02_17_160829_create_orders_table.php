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
                'assigning', // Order assigned to a driver
                'delivering', // Order is out for delivery
                'completed', // Order delivered successfully
                'declined', // Order was rejected
            ])->default('pending');
            $table->integer('quantity');
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);

            $table->enum('payment_method', ['aba', 'paid_out', 'un_paid'])->default('un_paid');

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
