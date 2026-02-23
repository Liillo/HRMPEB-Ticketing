<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedInteger('max_corporate_tables')
                ->default(10)
                ->after('max_capacity');
        });

        DB::table('events')
            ->whereNull('max_corporate_tables')
            ->update(['max_corporate_tables' => 10]);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('max_corporate_tables');
        });
    }
};
