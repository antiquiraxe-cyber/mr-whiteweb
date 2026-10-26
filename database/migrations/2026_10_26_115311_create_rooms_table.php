<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 4)->unique();
            $table->enum('mode', ['pass', 'multiplayer']);
            $table->enum('status', ['lobby', 'reveal', 'playing', 'white_guess', 'ended'])->default('lobby');
            $table->string('civilian_word')->nullable();
            $table->string('undercover_word')->nullable();
            $table->enum('winner', ['civilians', 'infiltrators', 'mr_white'])->nullable();
            $table->unsignedInteger('reveal_index')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
