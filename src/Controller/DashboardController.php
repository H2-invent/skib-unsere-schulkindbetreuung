<?php

namespace App\Controller;

use App\Repository\ChildSickReportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function __construct(private ChildSickReportRepository $sickReportRepository)
    {
    }

    /**
     * @Route("/login/dashboard", name="dashboard")
     */
    public function dashboard()
    {
        $user = $this->getUser();
        $todaySickReports = [];

        if ($user && method_exists($user, 'getOrganisation') && $user->getOrganisation()?->getStadt()?->isParentSickReportsEnabled()) {
            $todaySickReports = $this->sickReportRepository->findForTodayByOrganisation($user->getOrganisation());
        }

        return $this->render('dashboard/index.html.twig', [
            'todaySickReports' => $todaySickReports,
        ]);
    }
}
