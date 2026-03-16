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
        return [
            new TwigFunction('dateChecker', [$this, 'getClassName']),
            new TwigFunction('isActiveNav', [$this, 'isActiveNav']),
        ];
    }

    public function getClassName($object)
    {
        if($object === ''){
            return false;
        }
        try {
            return new \DateTime($object);
        }catch (\Exception $e){
            return false;
        }
    }

    public function isActiveNav(string $route): bool
    {
        $current = $this->requestStack->getCurrentRequest()->get('_route');

        // Exact match
        if ($route === $current) {
            return true;
        }

        return $this->isRelatedRoute($route, $current);
    }

    private function isRelatedRoute(string $navRoute, string $currentRoute): bool
    {
        // Define route groups: nav route => array of related routes that should highlight it
        $routeGroups = [
            'accounting_overview' => ['accounting_sepa_detail'],
            'accounting_showdata' => ['edit_stammdaten_edit'],
            'admin_stadt' => ['admin_stadt_detail', 'admin_stadt_edit_admin'],
            'block_schulen_schow' => ['kontingent_show_kids'],
            'child_show' => ['child_detail', 'child_edit', 'child_show_child'],
            'city_admin_news_anzeige' => ['city_admin_news_neu', 'city_admin_news_edit'],
            'city_admin_organisation_detail' => ['city_admin_organisation_edit'],
            'city_admin_schule_show' => ['city_admin_schule_new', 'city_admin_schule_edit', 'city_admin_schule_detail'],
            'city_admin_schuljahr_anzeige' => ['city_admin_schuljahr_neu', 'city_admin_schuljahr_edit'],
            'city_employee_org_show' => ['city_employee_org_new', 'city_employee_org_edit', 'org_admin_mitarbeiter_roles'],
            'city_employee_show' => ['city_employee_new', 'city_employee_edit', 'city_admin_mitarbeiter_roles'],
            'ferien_management_orders' => ['ferien_management_order_detail'],
            'ferien_management_show' => ['ferien_management_new', 'ferien_management_edit', 'ferien_management_detail'],
            'org_child_auto_assign' => ['org_child_auto_assign_edit'],
            'org_news_anzeige' => ['org_news_neu', 'org_news_edit'],
        ];

        if (isset($routeGroups[$navRoute])) {
            return in_array($currentRoute, $routeGroups[$navRoute], true);
        }

        return false;
    }
}
