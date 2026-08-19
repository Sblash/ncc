<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Round;
use App\Services\GameService;
use Carbon\Carbon;

class EndExpiredRounds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:end-expired-rounds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'End rounds that have expired and move them to voting phase';

    protected $gameService;

    /**
     * Execute the console command.
     */
    public function handle(GameService $gameService): void
    {
        $this->gameService = $gameService;
        
        $now = Carbon::now();
        
        // Find active rounds that have ended
        $expiredRounds = Round::where('status', 'active')
            ->where('ends_at', '<=', $now)
            ->with('game')
            ->get();

        $this->info('Found ' . $expiredRounds->count() . ' expired rounds');

        foreach ($expiredRounds as $round) {
            $this->info('Ending round ' . $round->id . ' for game ' . $round->game->name);
            
            try {
                $this->gameService->endRound($round);
                $this->info('Successfully ended round ' . $round->id);
            } catch (\Exception $e) {
                $this->error('Failed to end round ' . $round->id . ': ' . $e->getMessage());
            }
        }

        // Also check for voting rounds that should be completed
        // (This would be based on your game logic - e.g., if all players have voted)
        $votingRounds = Round::where('status', 'voting')
            ->where('ends_at', '<=', $now)
            ->with('game')
            ->get();

        foreach ($votingRounds as $round) {
            $this->info('Completing voting round ' . $round->id . ' for game ' . $round->game->name);
            
            try {
                $this->gameService->completeRound($round);
                $this->info('Successfully completed round ' . $round->id);
            } catch (\Exception $e) {
                $this->error('Failed to complete round ' . $round->id . ': ' . $e->getMessage());
            }
        }
    }
}
