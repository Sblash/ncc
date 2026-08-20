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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->integer('max_players')->default(8);
            $table->foreignId('current_round_id')->nullable()->constrained('rounds')->onDelete('set null');
            $table->enum('status', ['waiting', 'started', 'finished'])->default('waiting');
            $table->json('settings')->nullable(); // {letters: [...], categories: [...], rounds: N}
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
