<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount_paid', 12, 2)->default(0)->after('amount_to_pay');
            $table->decimal('to_pay', 12, 2)->default(0)->after('amount_paid');
            $table->string('reference_number')->nullable()->after('to_pay');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['reference_number', 'to_pay', 'amount_paid']);
        });
    }
};
