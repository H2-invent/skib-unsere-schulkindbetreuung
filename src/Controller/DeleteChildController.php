<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Service\ChildDeleteService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteChildController extends AbstractController
{
    public function __construct(private ChildDeleteService $deleteChildService, private TranslatorInterface $translator, private ManagerRegistry $managerRegistry)
    {
    }

    #[Route(path: '/org_child/delete', name: 'delete_child_delete', methods: ['DELETE'])]
    public function index(Request $request)
    {
        $child = $this->managerRegistry->getRepository(Kind::class)->find($request->get('kind_id'));
        if ($child->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $res = $this->deleteChildService->deleteChild($child,$this->getUser())?$this->translator->trans('Erfolgreich gelöscht'):$this->translator->trans('Fehler. Bitte versuchen Sie es erneut.');
        return new JsonResponse(array('redirect'=>$this->generateUrl('child_show',array('id'=>$child->getSchule()->getOrganisation()->getId(),'snack'=>$res))));
    }
}
