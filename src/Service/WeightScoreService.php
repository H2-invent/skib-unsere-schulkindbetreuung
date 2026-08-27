<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Kind;
use App\Entity\Organisation;
use RuntimeException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class WeightScoreService
{
    private const EXPRESSION_VARIABLES = ['kind', 'eltern', 'schule', 'organisation'];

    public function __construct(
        private readonly ExpressionLanguage $expressionLanguage,
    )
    {
    }

    /**
     * Use this to calculate multiple scores efficiently
     * @return callable(Kind): float
     */
    public function createCalculator(Organisation $organisation, ?string $formulaString = null): callable
    {
        $stadt = $organisation->getStadt();
        if ($stadt === null) {
            throw new RuntimeException("No stadt found for organisation: " . $organisation->getId());
        }

        $formulaString ??= $stadt->getAutoAssignFormula();
        $formulaParsed = $this->expressionLanguage->parse($formulaString, self::EXPRESSION_VARIABLES);

        return function (Kind $kind) use ($organisation, $formulaParsed): float {
            return (float) $this->expressionLanguage->evaluate($formulaParsed, [
                'kind' => $kind,
                'eltern' => $kind->getEltern(),
                'schule' => $kind->getSchule(),
                'organisation' => $organisation,
            ]);
        };
    }

    public function calculateSingle(Kind $kind, Organisation $organisation): float
    {
        return $this->createCalculator($organisation)($kind);
    }

    public function calculateSingleWithFormula(Kind $kind, Organisation $organisation, string $formula): float
    {
        return $this->createCalculator($organisation, $formula)($kind);
    }

    /**
     * Calculate scores for multiple children with percentile for gradient coloring.
     *
     * @param Kind[] $kinder
     * @return array<int, array{score: float, pct: float}>
     */
    public function calculateScoresForView(array $kinder, Organisation $organisation): array
    {
        if (empty($kinder)) {
            return [];
        }

        $calculator = $this->createCalculator($organisation);
        $scores = [];

        foreach ($kinder as $kind) {
            $scores[$kind->getId()] = ['score' => $calculator($kind), 'pct' => 0.0];
        }

        // Find min/max for percentile calculation
        $allScores = array_column($scores, 'score');
        $minScore = min($allScores);
        $maxScore = max($allScores);
        $range = $maxScore - $minScore;

        // Calculate percentile for each score (0 = lowest/red, 1 = highest/green)
        // If all scores are the same (range = 0), default to 1.0 (green)
        foreach ($scores as $kindId => &$data) {
            $data['pct'] = $range > 0 ? ($data['score'] - $minScore) / $range : 1.0;
        }

        return $scores;
    }
}
