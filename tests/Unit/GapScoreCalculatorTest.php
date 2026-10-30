<?php

use App\Services\Analysis\GapScoreCalculator;
use InvalidArgumentException;

beforeEach(function () {
    $this->calculator = new GapScoreCalculator;
});

it('calculates a percentage gap from required and current levels', function () {
    expect($this->calculator->skillGap(4, 1.0))->toBe(75.0);
    expect($this->calculator->skillGap(4, 0.0))->toBe(100.0);
    expect($this->calculator->skillGap(4, 4.0))->toBe(0.0);
});

it('caps current skill level at the required level for alignment', function () {
    expect($this->calculator->alignmentContribution(3, 5.0))->toBe(1.0);
    expect($this->calculator->alignmentContribution(4, 2.0))->toBe(0.5);
});

it('does not treat an unassessed skill as a zero score', function () {
    expect($this->calculator->skillGap(4, null))->toBeNull();
    expect($this->calculator->resultStatus(4, null))->toBe('not_assessed');
    expect($this->calculator->weightedAlignment([
        ['required_level' => 4, 'current_level' => null, 'weight' => 3],
    ]))->toBeNull();
});

it('calculates weighted alignment and excludes unassessed evidence', function () {
    $score = $this->calculator->weightedAlignment([
        ['required_level' => 4, 'current_level' => 4.0, 'weight' => 4],
        ['required_level' => 4, 'current_level' => 2.0, 'weight' => 2],
        ['required_level' => 5, 'current_level' => null, 'weight' => 10],
    ]);

    expect($score)->toBe(83.33);
});

it('classifies gap severity consistently', function () {
    expect($this->calculator->resultStatus(4, 4.0))->toBe('aligned');
    expect($this->calculator->resultStatus(4, 3.0))->toBe('minor_gap');
    expect($this->calculator->resultStatus(5, 2.1))->toBe('moderate_gap');
    expect($this->calculator->resultStatus(5, 2.0))->toBe('critical_gap');
    expect($this->calculator->resultStatus(5, 1.0))->toBe('critical_gap');
});

it('rejects skill levels outside the defined range', function () {
    expect(fn () => $this->calculator->skillGap(0, 1.0))->toThrow(InvalidArgumentException::class);
    expect(fn () => $this->calculator->skillGap(4, 5.1))->toThrow(InvalidArgumentException::class);
});