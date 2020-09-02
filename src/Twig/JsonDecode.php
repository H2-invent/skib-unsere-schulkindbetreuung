<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use function GuzzleHttp\Psr7\str;

class JsonDecode extends AbstractExtension
{

    public function __construct(ParameterBagInterface $parameterBag)
    {

    }

    public function getFilters()
    {
        return [
            new TwigFilter('json_decode', [$this, 'json_decode']),
        ];
    }

    public function json_decode($string)
    {
        $var = json_decode($string,true);
        return $var ;
    }
}