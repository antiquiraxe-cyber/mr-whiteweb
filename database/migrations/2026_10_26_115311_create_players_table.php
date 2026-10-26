<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->uuid('token')->unique();
            $table->string('name', 20);
            $table->enum('role', ['civilian', 'undercover', 'mr_white'])->nullable();
            $table->string('word')->nullable();
            $table->boolean('is_host')->default(false);
            $table->boolean('is_alive')->default(true);
            $table->unsignedInteger('turn_order');
            $table->timestamps();

            $table->unique(['room_id', 'name']);
            $table->index(['room_id', 'is_alive']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
