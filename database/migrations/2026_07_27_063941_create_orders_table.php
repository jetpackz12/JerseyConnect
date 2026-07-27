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
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('design_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('template_name');
            $table->string('template_image');
            $table->string('team_name');
            $table->string('primary_color', 7);
            $table->string('secondary_color', 7);
            $table->string('accent_color', 7);
            $table->string('font_style')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price');

            $table->enum('status', [
                'processing',
                'in_production',
                'ready_for_delivery',
                'shipped',
                'delivered',
                'completed',
            ])->default('processing');

            $table->unsignedInteger('shipping_fee')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_receipts');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('orders');
    }
};
