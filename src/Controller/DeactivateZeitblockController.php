<?php

namespace App\Controller;

use App\Entity\Zeitblock;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/org_block_deactivate/block/deactivate/", name="app_deactivate_")
 */
class DeactivateZeitblockController extends AbstractController
{

    /**
     * @Route("deactivate", name="zeitblock_index")
     */
    public function index(Request $request, TranslatorInterface $translator): Response
    {
        if (!$this->getUser()->hasRole('ROLE_ORG_BLOCK_DEACTIVATE')){
            return new JsonResponse(array('error' => 1, 'snack' => 'Fehler, Keine Berechtigung'));
        }

        $block = $this->getDoctrine()->getRepository(Zeitblock::class)->find($request->get('id'));
        if ($block->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Fehler: Falsche Organisation');
            return new JsonResponse(array('error' => 1, 'snack' => $text));
        }
        if ($block->getDeaktiviert()){
            $block->setDeaktiviert(false);
        }else{
            $block->setDeaktiviert(true);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($block);
        $em->flush();
        $text = $translator->trans('Erfolgreich geändert');
        return new JsonResponse(array('error' => 0, 'snack' => $text));
    }
}
