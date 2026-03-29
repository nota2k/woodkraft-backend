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
        Schema::table('clients', function (Blueprint $table) {
            $table->renameColumn('address', 'shipping_address');
            $table->renameColumn('city', 'shipping_city');
            $table->renameColumn('zip_code', 'shipping_zip_code');
            $table->renameColumn('country', 'shipping_country');

            // Add billing
            $table->string('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_zip_code')->nullable();
            $table->string('billing_country')->default('France');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->renameColumn('shipping_address', 'address');
            $table->renameColumn('shipping_city', 'city');
            $table->renameColumn('shipping_zip_code', 'zip_code');
            $table->renameColumn('shipping_country', 'country');

            $table->dropColumn(['billing_address', 'billing_city', 'billing_zip_code', 'billing_country']);
        });
    }
};
