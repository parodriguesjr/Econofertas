<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flyers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supermarket_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flyers');
    }
};
