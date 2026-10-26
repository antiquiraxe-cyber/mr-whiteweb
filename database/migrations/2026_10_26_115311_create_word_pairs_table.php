<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_pairs', function (Blueprint $table): void {
            $table->id();
            $table->string('word_a');
            $table->string('word_b');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_pairs');
    }
};
