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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_method_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('promo_code_id')->nullable()->after('shipping_method_id')->constrained()->nullOnDelete();
            $table->decimal('subtotal_amount', 10, 2)->default(0)->after('total_amount');
            $table->decimal('shipping_amount', 10, 2)->default(0)->after('subtotal_amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('shipping_amount');
            $table->string('promo_code')->nullable()->after('discount_amount');
            $table->string('shipping_method_name')->nullable()->after('promo_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_method_id');
            $table->dropConstrainedForeignId('promo_code_id');
            $table->dropColumn([
                'subtotal_amount',
                'shipping_amount',
                'discount_amount',
                'promo_code',
                'shipping_method_name',
            ]);
        });
    }
};
