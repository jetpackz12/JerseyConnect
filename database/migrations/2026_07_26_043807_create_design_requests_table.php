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
        Schema::create('design_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('jerseys')->cascadeOnDelete();
            $table->string('template_name');
            $table->string('template_image');
            $table->unsignedInteger('template_price');
            $table->string('team_name');
            $table->string('primary_color', 7);
            $table->string('secondary_color', 7);
            $table->string('accent_color', 7);
            $table->string('font_style')->nullable();
            $table->unsignedInteger('estimated_quantity')->nullable();
            $table->text('notes')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('gcash_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('proof_image')->nullable();

            $table->enum('status', [
                'pending_review',
                'in_discussion',
                'revision_requested',
                'waiting_for_down_payment',
                'pending_down_payment_review',
                'approved',
                'cancelled',
            ])->default('pending_review');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_requests');
    }
};
