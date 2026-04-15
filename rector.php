<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Symfony34\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withImportNames(
        importNames: true,
        importDocBlockNames: true,
        importShortClasses: false,
        removeUnusedImports: true,
    )
    ->withPhpSets(php84: true)
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        SensiolabsSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::SYMFONY_64,
    ])
    ->withSkip([
        ReadOnlyPropertyRector::class,
        LongArrayToShortArrayRector::class,
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ])
    ->withRules([
        ReplaceSensioRouteAnnotationWithSymfonyRector::class,
    ])
    ->withSymfonyContainerPhp(__DIR__ . '/tests/symfony-container.php')
;
