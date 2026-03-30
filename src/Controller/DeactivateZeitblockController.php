<?php

namespace App\Controller;

use App\Entity\Zeitblock;
use Doctrine\Persistence\ManagerRegistry;
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

    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }
    /**
     * @Route("deactivate", name="zeitblock_index")
     */
    public function index(Request $request, TranslatorInterface $translator): Response
    {
        if (!$this->getUser()->hasRole('ROLE_ORG_BLOCK_DEACTIVATE')){
            return new JsonResponse(array('error' => 1, 'snack' => 'Fehler, Keine Berechtigung'));
        }

        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('id'));
        if ($block->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Fehler: Falsche Organisation');
            return new JsonResponse(array('error' => 1, 'snack' => $text));
        }
        if ($block->getDeaktiviert()){
            $block->setDeaktiviert(false);
        }else{
            $block->setDeaktiviert(true);
        }

        $this->syncDirektbuchungDeaktivierteVorgaenger($block);

        $em = $this->managerRegistry->getManager();
        $em->persist($block);
        $em->flush();
        $text = $translator->trans('Erfolgreich geändert');
        return new JsonResponse(array('error' => 0, 'snack' => $text));
    }

    private function syncDirektbuchungDeaktivierteVorgaenger(Zeitblock $zeitblock, array &$visited = []): void
    {
        if (isset($visited[$zeitblock->getId()])) {
            return;
        }
        $visited[$zeitblock->getId()] = true;

        $em = $this->managerRegistry->getManager();
        foreach ($zeitblock->getVorganger() as $vorgaenger) {
            if (!$vorgaenger->getDirektbuchungDeaktiviert()) {
                continue;
            }

            $hasActiveNachfolger = false;
            foreach ($vorgaenger->getNachfolger() as $nachfolger) {
                if (!$nachfolger->getDeleted() && !$nachfolger->getDeaktiviert()) {
                    $hasActiveNachfolger = true;
                    break;
                }
            }

            $vorgaenger->setDeaktiviert(!$hasActiveNachfolger);
            $em->persist($vorgaenger);
            $this->syncDirektbuchungDeaktivierteVorgaenger($vorgaenger, $visited);
        }
    }
}
