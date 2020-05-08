<?php

namespace App\Controller;

use App\Entity\Organisation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrganisationProfilController extends AbstractController
{
    /**
     * @Route("/profil/{slug}", name="organisation_profil")
     * @ParamConverter("organisation", options={"mapping"={"slug"="slug"}})
     */
    public function index(Request $request, Organisation $organisation, TranslatorInterface $translator)
    {
        return $this->render('organisation_profil/index.html.twig', [
            'organisation' => $organisation,
            'stadt' => $organisation->getStadt(),
            'metaDescription' => $organisation->getInfoText(),
            'title' => $organisation->getName(),
        ]);
    }
}
