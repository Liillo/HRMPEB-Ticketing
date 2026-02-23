<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->uuid('corporate_booking_ref')
                ->nullable()
                ->after('attendee_details');

            $table->index('corporate_booking_ref');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['corporate_booking_ref']);
            $table->dropColumn('corporate_booking_ref');
        });
    }
};
