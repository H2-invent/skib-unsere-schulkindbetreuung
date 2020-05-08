<?php

namespace App\Controller;

use App\Entity\Organisation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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
            'metaDescription' => $this->buildMeta($organisation->getInfoText()),
            'title' => $organisation->getName(),
        ]);
    }
    private function buildMeta($sentenceArray)
    {
        $count = 0;
        $res = '';
        $array = explode('. ', $sentenceArray);
        foreach ($array as $data) {

            if ($count <= 160) {
                $res .= $data . '. ';
            } else {
                break;
            }
            $count += strlen($data);
        }
        return $res;
    }
}
