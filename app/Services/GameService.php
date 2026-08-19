<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Round;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;

class GameService
{
    protected ScoreCalculator $scoreCalculator;

    public function __construct(ScoreCalculator $scoreCalculator)
    {
        $this->scoreCalculator = $scoreCalculator;
    }

    /**
     * Create a new game.
     */
    public function createGame(User $user, array $data): Game
    {
        $game = Game::create([
            'name' => $data['name'],
            'creator_id' => $user->id,
            'max_players' => $data['max_players'] ?? 8,
            'status' => 'waiting',
            'settings' => [
                'letters' => $data['letters'] ?? ['A', 'B', 'C', 'D', 'E'],
                'categories' => $data['categories'] ?? [1, 2, 3],
                'rounds' => $data['rounds'] ?? 5,
                'round_duration' => $data['round_duration'] ?? 60
            ]
        ]);

        // Add creator as first player
        $game->players()->attach($user->id, [
            'score' => 0,
            'status' => 'joined'
        ]);

        return $game;
    }

    /**
     * Join a game.
     */
    public function joinGame(User $user, Game $game): bool
    {
        // Check if game is full
        if ($game->isFull()) {
            return false;
        }

        // Check if game has already started
        if ($game->hasStarted()) {
            return false;
        }

        // Check if user is already in the game
        if ($game->hasPlayer($user)) {
            return false;
        }

        $game->players()->attach($user->id, [
            'score' => 0,
            'status' => 'joined'
        ]);

        return true;
    }

    /**
     * Leave a game.
     */
    public function leaveGame(User $user, Game $game): bool
    {
        // Check if user is in the game
        if (!$game->hasPlayer($user)) {
            return false;
        }

        // Check if game has already started (can't leave after start)
        if ($game->hasStarted()) {
            return false;
        }

        $game->players()->detach($user->id);

        // If creator leaves, transfer ownership or delete game
        if ($game->isCreator($user)) {
            $otherPlayers = $game->players()->where('users.id', '!=', $user->id)->get();
            if ($otherPlayers->isEmpty()) {
                // Delete game if no other players
                $game->delete();
            } else {
                // Transfer ownership to first player
                $newCreator = $otherPlayers->first();
                $game->update(['creator_id' => $newCreator->id]);
            }
        }

        return true;
    }

    /**
     * Start a game.
     */
    public function startGame(User $user, Game $game): bool
    {
        // Check if user is the creator
        if (!$game->isCreator($user)) {
            return false;
        }

        // Check if game has enough players (at least 2)
        if ($game->players()->count() < 2) {
            return false;
        }

        // Check if game has already started
        if ($game->hasStarted()) {
            return false;
        }

        // Update game status
        $game->update(['status' => 'started']);

        // Create first round
        $this->createNextRound($game);

        return true;
    }

    /**
     * Create the next round for a game.
     */
    public function createNextRound(Game $game): Round
    {
        $roundNumber = $game->rounds()->count() + 1;
        $letter = $game->getNextLetter();
        $categoryId = $game->getNextCategoryId();
        $roundDuration = $game->settings['round_duration'] ?? 60;

        $round = Round::create([
            'game_id' => $game->id,
            'letter' => $letter,
            'category_id' => $categoryId,
            'round_number' => $roundNumber,
            'status' => 'pending',
            'starts_at' => now(),
            'ends_at' => now()->addSeconds($roundDuration)
        ]);

        // Update game's current round
        $game->update(['current_round_id' => $round->id]);

        return $round;
    }

    /**
     * Start a round.
     */
    public function startRound(Round $round): bool
    {
        if ($round->status !== 'pending') {
            return false;
        }

        $round->update([
            'status' => 'active',
            'starts_at' => now()
        ]);

        return true;
    }

    /**
     * End a round and start voting phase.
     */
    public function endRound(Round $round): bool
    {
        if ($round->status !== 'active') {
            return false;
        }

        $round->update([
            'status' => 'voting',
            'ends_at' => now()
        ]);

        return true;
    }

    /**
     * Complete a round and move to next.
     */
    public function completeRound(Round $round): bool
    {
        if ($round->status !== 'voting') {
            return false;
        }

        // Calculate scores for this round
        $this->scoreCalculator->calculateRoundScores($round);

        $round->update(['status' => 'completed']);

        $game = $round->game;
        $totalRounds = $game->getTotalRounds();

        // Check if this was the last round
        if ($round->round_number >= $totalRounds) {
            // Game is finished
            $game->update(['status' => 'finished']);
            
            // Update user stats
            $this->scoreCalculator->updateUserStatsAfterGame($game);
        } else {
            // Create next round
            $this->createNextRound($game);
        }

        return true;
    }

    /**
     * Submit a word for a round.
     */
    public function submitWord(User $user, Round $round, string $word, int $categoryId): Word
    {
        // Check if round is active
        if (!$round->isActive()) {
            throw new \Exception('Round is not active');
        }

        // Check if user is in the game
        if (!$round->game->hasPlayer($user)) {
            throw new \Exception('User is not in the game');
        }

        // Check if user has already submitted for this category
        $existingWord = $round->words()
            ->where('user_id', $user->id)
            ->where('category_id', $categoryId)
            ->first();

        if ($existingWord) {
            // Update existing word
            $existingWord->update(['word' => $word]);
            return $existingWord;
        }

        // Create new word
        $wordModel = $round->words()->create([
            'user_id' => $user->id,
            'category_id' => $categoryId,
            'word' => $word,
            'is_valid' => null
        ]);

        return $wordModel;
    }

    /**
     * Vote on a word.
     */
    public function voteOnWord(User $user, Word $word, bool $isValid): Vote
    {
        // Check if word's round is in voting phase
        if (!$word->round->isVoting()) {
            throw new \Exception('Round is not in voting phase');
        }

        // Check if user is in the game
        if (!$word->round->game->hasPlayer($user)) {
            throw new \Exception('User is not in the game');
        }

        // Check if user is not the word submitter
        if ($word->user_id === $user->id) {
            throw new \Exception('Cannot vote on your own word');
        }

        // Check if user has already voted on this word
        $existingVote = $word->votes()->where('user_id', $user->id)->first();

        if ($existingVote) {
            // Update existing vote
            $existingVote->update(['is_valid' => $isValid]);
            return $existingVote;
        }

        // Create new vote
        $vote = $word->votes()->create([
            'user_id' => $user->id,
            'is_valid' => $isValid
        ]);

        return $vote;
    }

    /**
     * Check if all players have submitted words for a round.
     */
    public function allPlayersSubmitted(Round $round): bool
    {
        $game = $round->game;
        $players = $game->players()->count();
        $categories = count($game->getCategoryIds());
        
        // Each player should submit one word per category
        $expectedSubmissions = $players * $categories;
        $actualSubmissions = $round->words()->count();

        return $actualSubmissions >= $expectedSubmissions;
    }

    /**
     * Check if all players have voted in a round.
     */
    public function allPlayersVoted(Round $round): bool
    {
        $game = $round->game;
        $players = $game->players()->count();
        $words = $round->words()->count();
        $votes = $round->votes()->count();

        // Each player should vote on each word (except their own)
        // For simplicity, check if all words have been voted on by all other players
        $words = $round->words()->withCount(['votes'])->get();
        
        foreach ($words as $word) {
            $expectedVotes = $players - 1; // All players except the submitter
            if ($word->votes_count < $expectedVotes) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get games that are waiting for players.
     */
    public function getWaitingGames(): \Illuminate\Database\Eloquent\Collection
    {
        return Game::where('status', 'waiting')
            ->where('max_players', '>', function($query) {
                $query->selectRaw('COUNT(*)')
                    ->from('player_game')
                    ->whereColumn('player_game.game_id', 'games.id');
            })
            ->with(['creator', 'players', 'currentRound'])
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Get games that a user is part of.
     */
    public function getUserGames(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Game::whereHas('players', function($query) use ($user) {
            $query->where('users.id', $user->id);
        })
            ->with(['creator', 'players', 'currentRound'])
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Get available categories.
     */
    public function getCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::orderBy('name')->get();
    }

    /**
     * Create default categories.
     */
    public function createDefaultCategories(): void
    {
        $defaultCategories = [
            'Nomi',
            'Cose',
            'Città',
            'Animali',
            'Piante',
            'Cibi',
            'Mestieri',
            'Film',
            'Libri',
            'Personaggi famosi'
        ];

        foreach ($defaultCategories as $categoryName) {
            Category::firstOrCreate(['name' => $categoryName]);
        }
    }
}
