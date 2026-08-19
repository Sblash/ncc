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
            $table->string('name');
            $table->foreignId('creator_id')->constrained('users');
            $table->integer('max_players')->default(8);
            $table->foreignId('current_round_id')->nullable()->constrained('rounds');
            $table->enum('status', ['waiting', 'started', 'finished'])->default('waiting');
            $table->json('settings')->default(json_encode([
                'letters' => ['A', 'B', 'C', 'D', 'E'],
                'categories' => [1, 2, 3],
                'rounds' => 5,
                'round_duration' => 60
            ]));
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
