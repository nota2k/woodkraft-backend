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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('depth', 8, 2)->nullable();
            $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();
            $table->dropColumn(['materials', 'dimensions']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('materials')->nullable();
            $table->text('dimensions')->nullable();
            $table->dropForeign(['material_id']);
            $table->dropColumn(['length', 'width', 'depth', 'material_id']);
        });
    }
};
