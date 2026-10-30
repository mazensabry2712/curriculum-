<?php

namespace App\Services\Analysis;

use InvalidArgumentException;

class GapScoreCalculator
{
    public function skillGap(int $requiredLevel, ?float $currentLevel): ?float
    {
        $this->assertLevel($requiredLevel);

        if ($currentLevel === null) {
            return null;
        }

        $this->assertLevel((int) ceil($currentLevel));

        return round(max(0, ($requiredLevel - min($currentLevel, $requiredLevel)) / $requiredLevel) * 100, 2);
    }

    public function alignmentContribution(int $requiredLevel, ?float $currentLevel): ?float
    {
        $this->assertLevel($requiredLevel);

        if ($currentLevel === null) {
            return null;
        }

        $this->assertLevel((int) ceil($currentLevel));

        return round(min($currentLevel, $requiredLevel) / $requiredLevel, 4);
    }

    /**
     * @param array<int, array{required_level:int,current_level:?float,weight:float}> $items
     */
    public function weightedAlignment(array $items): ?float
    {
        $weightedScore = 0.0;
        $totalWeight = 0.0;

        foreach ($items as $item) {
            $weight = (float) $item['weight'];

            if ($weight <= 0 || $item['current_level'] === null) {
                continue;
            }

            $contribution = $this->alignmentContribution(
                (int) $item['required_level'],
                (float) $item['current_level'],
            );

            $weightedScore += $contribution * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? round(($weightedScore / $totalWeight) * 100, 2) : null;
    }

    public function resultStatus(int $requiredLevel, ?float $currentLevel): string
    {
        if ($currentLevel === null) {
            return 'not_assessed';
        }

        $gap = $this->skillGap($requiredLevel, $currentLevel);

        return match (true) {
            $gap <= 0 => 'aligned',
            $gap < 30 => 'minor_gap',
            $gap < 60 => 'moderate_gap',
            default => 'critical_gap',
        };
    }

    private function assertLevel(int $level): void
    {
        if ($level < 1 || $level > 5) {
            throw new InvalidArgumentException('Skill levels must be between 1 and 5.');
        }
    }
}