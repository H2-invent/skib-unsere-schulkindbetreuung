<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Stadt;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
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
        private readonly Security $security,
    )
    {
    }

    public function isEnabled(string $feature, ?Stadt $stadt = null): bool
    {
        $stadt ??= $this->security->getUser()?->getStadt();
        if ($stadt === null) {
            return false;
        }

        return match ($feature) {
            self::FEATURE_LATE_REGISTRATION_FINISH_MAIL => $stadt->isSettingsFeatureLateRegistrationFinishMail(),
            self::FEATURE_LIVE_SCORING => $stadt->isSettingsFeatureLiveScoring(),
            default => false,
        };

        //FIXME will we go back to env vars someday?

        if (!isset($this->flags[$feature])) {
            throw new RuntimeException("Feature Flag not found: {$feature}");
        }

        return $this->flags[$feature];
    }
}

