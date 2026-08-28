<?php
declare(strict_types=1);

namespace App\Service;

use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class FeatureFlagService
{
    public const FEATURE_LATE_REGISTRATION_FINISH_MAIL = 'late_registration_finish_mail';
    public const FEATURE_LIVE_SCORING = 'live_scoring';

    /**
     * @param array<string, bool> $flags
     */
    public function __construct(
        #[Autowire('%app.feature_flags%')]
        private readonly array $flags,
    )
    {
    }

    public function isEnabled(string $feature): bool
    {
        if (!isset($this->flags[$feature])) {
            throw new RuntimeException("Feature Flag not found: {$feature}");
        }

        return $this->flags[$feature];
    }
}

