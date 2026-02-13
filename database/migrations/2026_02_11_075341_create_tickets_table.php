<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->enum('type', ['individual', 'corporate'])->default('individual');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->integer('number_of_attendees')->default(1);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->string('qr_code')->nullable();
            $table->integer('scan_count')->default(0);
            $table->integer('max_scans')->default(1);
            $table->timestamps();
            
            $table->index(['uuid', 'status']);
            $table->index('email');
            $table->index('phone');
            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};