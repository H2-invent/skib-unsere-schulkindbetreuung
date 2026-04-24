<?php
declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use Monolog\Handler\NullHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ToggleLokiHandlerPass implements CompilerPassInterface
{
    private const LOKI_SERVICES = [
        'monolog.handler.loki_filtered',
        'monolog.handler.loki_healthcheck_filter',
        'monolog.handler.loki_buffer',
        'monolog.handler.loki_failuregroup',
        'monolog.handler.loki',
    ];

    public function process(ContainerBuilder $container): void
    {
        $enabled = $container->resolveEnvPlaceholders(
            $container->getParameter('app.log.loki.enabled'),
            true,
        );

        if ($enabled) {
            return;
        }

        foreach (self::LOKI_SERVICES as $service) {
            if ($container->hasDefinition($service)) {
                $nullhandler = new Definition(NullHandler::class);
                $nullhandler->setPublic(false);
                $container->setDefinition($service, $nullhandler);
            }
        }
    }
}
