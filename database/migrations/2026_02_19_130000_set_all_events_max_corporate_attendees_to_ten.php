<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('events')->update([
            'max_corporate_attendees' => 10,
        ]);
    }

    public function down(): void
    {
        // No-op: cannot safely restore previous per-event values.
    }
};
