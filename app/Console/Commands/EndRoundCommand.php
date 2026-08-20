<?php

namespace App\Console\Commands;

use App\Models\Round;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EndRoundCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:end-rounds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and end expired rounds';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $now = Carbon::now();
        
        // Find active rounds that have expired
        $expiredRounds = Round::where('status', 'active')
            ->where('ends_at', '<=', $now)
            ->get();

        foreach ($expiredRounds as $round) {
            // End the round and set to voting
            $round->update([
                'status' => 'voting',
                'ends_at' => $now,
            ]);

            Log::info("Round {$round->id} has been ended and set to voting");
        }

        $this->info("Checked " . count($expiredRounds) . " expired rounds");
    }
}
