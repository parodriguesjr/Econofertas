<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supermarket_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->string('type')->default('product');
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('match_key')->nullable()->index();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('original_price', 10, 2)->nullable();
            $table->text('image_url')->nullable();
            $table->text('source_url');
            $table->string('city')->default('Nova Odessa');
            $table->boolean('is_active')->default(true);
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();
            $table->unique(['supermarket_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
