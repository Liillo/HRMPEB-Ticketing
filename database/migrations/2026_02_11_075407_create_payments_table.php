<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->string('checkout_request_id')->unique();
            $table->string('merchant_request_id')->nullable();
            $table->string('mpesa_receipt')->nullable();
            $table->string('phone_number');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->text('response_description')->nullable();
            $table->timestamps();
            
            $table->index('checkout_request_id');
            $table->index(['ticket_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
