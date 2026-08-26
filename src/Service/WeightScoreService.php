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
}
