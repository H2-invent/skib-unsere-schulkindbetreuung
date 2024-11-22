<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\WartelisteExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class WartelisteExtension extends AbstractExtension
{


    public function getFunctions(): array
    {
        return [
            new TwigFunction('showWarteListForChild', [WartelisteExtensionRuntime::class, 'showWarteListForChild']),
            new TwigFunction('findLatestChildForChild', [WartelisteExtensionRuntime::class, 'findLatestChildForChild']),
        ];
    }
}
