<?php
declare(strict_types=1);

namespace App\Monolog\Handler;

use Monolog\Handler\FilterHandler;
use Monolog\LogRecord;

class ExcludeHealthCheckHandler extends FilterHandler
{
    private const EXCLUDED_ROUTES = [
        'app_health_liveness',
        'app_health_readiness',
    ];

    public function isHandling(LogRecord $record): bool
    {
        $route = $record->context['route'] ?? $record->extra['route'] ?? '';
        if (in_array($route, self::EXCLUDED_ROUTES, true)) {
            return false;
        }

        return parent::isHandling($record);
    }
}
