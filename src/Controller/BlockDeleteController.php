<?php

namespace App\Controller;

use App\Entity\Zeitblock;
use App\Service\AnmeldeEmailService;
use App\Service\BlockDeleteService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BlockDeleteController extends AbstractController
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }
    /**
     * @Route("/org_block_delete/schule/block/delete", name="block_schule_deleteBlocks",methods={"PUT"})
     */
    public function deleteBlock(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, BlockDeleteService $blockDeleteService)
    {
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('id'));
        if ($block->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Fehler: Falsche Organisation');
            return new JsonResponse(array('error' => 1, 'snack' => $text));
        }

        $text = $blockDeleteService->deleteBlock($block);
        return new JsonResponse(array('error' => 0, 'snack' => $text));
    }
    /**
     * @Route("/org_block_delete/schule/block/restore", name="block_schule_restoreBlocks",methods={"GET"})
     */
    public function restoreBlock(Request $request, ValidatorInterface $validator, TranslatorInterface $translator, BlockDeleteService $blockDeleteService)
    {
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('id'));
        if ($block->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Fehler: Falsche Organisation');
            return new JsonResponse(array('error' => 1, 'snack' => $text));
        }

        $text = $blockDeleteService->restoreBlock($block);
        return new JsonResponse(array('error' => 0, 'snack' => $text));
    }
}
