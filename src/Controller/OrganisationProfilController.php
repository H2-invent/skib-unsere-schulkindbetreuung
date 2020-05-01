<?php

namespace App\Controller;

use App\Entity\Organisation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class OrganisationProfilController extends AbstractController
{
    /**
     * @Route("/profil/{slug}", name="organisation_profil")
     * @ParamConverter("organisation", options={"mapping"={"slug"="slug"}})
     */
    public function index(Request $request, Organisation $organisation)
    {
        return $this->render('organisation_profil/index.html.twig', [
            'organisation' => $organisation,
            'stadt' => $organisation->getStadt(),
        ]);
    }
}
