<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use function GuzzleHttp\Psr7\str;

class ReplaceHttps extends AbstractExtension
{
    private $environment;
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->environment = $parameterBag->get('kernel.environment');
    }

    public function getFilters()
    {
        return [
            new TwigFilter('makeHttps', [$this, 'makeHttps']),
        ];
    }

    public function makeHttps($string)
    {

        if($this->environment == 'prod'){
            $string = str_replace('https','http',$string);
            $string = str_replace('http','https',$string);
        }
        return $string;
    }
}