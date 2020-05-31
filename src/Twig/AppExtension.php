<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return array(
            new TwigFunction('dateChecker', array($this, 'getClassName')),
        );
    }

    public function getClassName($object)
    {
        if($object === ''){
            return false;
        }
        try {
            $date= new \DateTime($object);
            return $date;
        }catch (\Exception $e){
            return false;
        }

    }
}