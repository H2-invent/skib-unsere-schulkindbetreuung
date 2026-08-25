<?php
declare(strict_types=1);

namespace App\Twig\Extension;

use App\Service\FeatureFlagService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FeatureFlagExtension extends AbstractExtension
{
    public function __construct(
        private readonly FeatureFlagService $featureFlagService,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isFeatureEnabled', [$this, 'isFeatureEnabled']),
        ];
    }

    public function isFeatureEnabled(string $feature): bool
    {
        return $this->featureFlagService->isEnabled($feature);
    }
}
