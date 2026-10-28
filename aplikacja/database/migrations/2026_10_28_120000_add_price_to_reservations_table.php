<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The price of the service at the time of booking, so a later price change
     * doesn't rewrite past income.
     */
    public function up(): void
    {
        // databases rebuilt while this column was still part of create_reservations_table already have it
        if (Schema::hasColumn('reservations', 'price')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0)->after('status');
        });

        // existing reservations get the service's current price, the closest thing to what was paid
        DB::table('reservations')->update([
            'price' => DB::raw('coalesce((select services.price from services where services.uuid = reservations.service_id), 0)'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
