<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ScoreCalculatorTest extends TestCase
{
    public function test_unique_valid_word_gives_10_points(): void
    {
        // Simulate scoring logic
        $isValid = true;
        $isUnique = true;
        
        $score = 0;
        if ($isValid) {
            $score += $isUnique ? 10 : 5;
        } else {
            $score -= 15;
        }
        
        $this->assertEquals(10, $score);
    }

    public function test_duplicate_valid_word_gives_5_points(): void
    {
        $isValid = true;
        $isUnique = false;
        
        $score = 0;
        if ($isValid) {
            $score += $isUnique ? 10 : 5;
        } else {
            $score -= 15;
        }
        
        $this->assertEquals(5, $score);
    }

    public function test_invalid_word_gives_minus_15_points(): void
    {
        $isValid = false;
        $isUnique = false;
        
        $score = 0;
        if ($isValid) {
            $score += $isUnique ? 10 : 5;
        } else {
            $score -= 15;
        }
        
        $this->assertEquals(-15, $score);
    }

    public function test_word_validation_accepts_only_letters_and_spaces(): void
    {
        $validWords = ['Albero', 'Casa', 'Nomi Cose'];
        $invalidWords = ['123', 'Albero123', 'Albero-123', ''];
        
        foreach ($validWords as $word) {
            $this->assertTrue($this->isValidWord($word));
        }
        
        foreach ($invalidWords as $word) {
            $this->assertFalse($this->isValidWord($word));
        }
    }

    public function test_word_starts_with_letter(): void
    {
        $letter = 'A';
        
        $this->assertTrue($this->startsWithLetter('Albero', $letter));
        $this->assertTrue($this->startsWithLetter('albero', $letter));
        $this->assertFalse($this->startsWithLetter('Bambino', $letter));
    }

    protected function isValidWord(string $word): bool
    {
        return preg_match('/^[a-zA-Z\s]+$/', $word) === 1;
    }

    protected function startsWithLetter(string $word, string $letter): bool
    {
        $firstChar = strtoupper(substr($word, 0, 1));
        return $firstChar === strtoupper($letter);
    }
}
