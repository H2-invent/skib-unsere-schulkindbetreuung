<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(protected RequestStack $requestStack)
    {

    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('dateChecker', array($this, 'getClassName')),
            new TwigFunction('isActiveNav', array($this, 'isActiveNav')),
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

    public function isActiveNav(string $route): bool
    {
        $current = $this->requestStack->getCurrentRequest()->get('_route');

        if ($this->isSpecialRoute($route, $current)) {
            return true;
        }

        $partsRoute = explode('_', $route);
        $partsCurrent = explode('_', $current);

        if (isset($partsRoute[1]) && isset($partsCurrent[1])) {
            $prefixRoute = $partsRoute[0] . '_' . $partsRoute[1];
            $prefixCurrent = $this->fixCurrentRoutePart($partsCurrent[0]) . '_' . $this->fixCurrentRoutePart($partsCurrent[1]);
        } else {
            $prefixRoute = $partsRoute[0];
            $prefixCurrent = $partsCurrent[0];
        }

        return $prefixRoute == $prefixCurrent;
    }

    private function isSpecialRoute(string $routeNav, string $routeCurrent): bool
    {
        if ($routeCurrent === 'kontingent_show_kids' && $routeNav === 'block_schulen_schow') {
            return true;
        }

        if ($routeCurrent === 'edit_stammdaten_edit' && $routeNav === 'accounting_showdata') {
            return true;
        }

        if ($routeCurrent === 'accounting_sepa_detail' && $routeNav === 'accounting_overview') {
            return true;
        }

        return false;
    }

    private function fixCurrentRoutePart(string $part): string
    {
        if ('organisation' === $part) {
            return 'city';
        }

        if ('schule' === $part) {
            return 'schulen';
        }

        return $part;
    }
}