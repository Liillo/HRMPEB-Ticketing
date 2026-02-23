<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('payments', 'method')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('method', 20)->default('mpesa')->after('ticket_id');
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'cheque_number')) {
                $table->string('cheque_number')->nullable()->after('mpesa_receipt');
            }
            if (!Schema::hasColumn('payments', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('cheque_number');
            }
            if (!Schema::hasColumn('payments', 'cheque_date')) {
                $table->date('cheque_date')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('payments', 'payer_name')) {
                $table->string('payer_name')->nullable()->after('cheque_date');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'method')) {
                $table->dropIndex(['method', 'status']);
            }

            if (Schema::hasColumn('payments', 'cheque_number')) {
                $table->dropColumn('cheque_number');
            }
            if (Schema::hasColumn('payments', 'bank_name')) {
                $table->dropColumn('bank_name');
            }
            if (Schema::hasColumn('payments', 'cheque_date')) {
                $table->dropColumn('cheque_date');
            }
            if (Schema::hasColumn('payments', 'payer_name')) {
                $table->dropColumn('payer_name');
            }

            if (Schema::hasColumn('payments', 'method')) {
                $table->dropColumn('method');
            }
        });
    }
};
