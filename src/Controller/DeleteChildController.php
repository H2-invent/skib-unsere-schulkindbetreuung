<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\Organisation;
use App\Service\ChildDeleteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteChildController extends AbstractController
{
    private $deleteChildService;
    private $translator;
    public function __construct(ChildDeleteService $deleteChildService,TranslatorInterface $translator)
    {
        $this->deleteChildService = $deleteChildService;
        $this->translator = $translator;
    }

    /**
     * @Route("/org_child/delete/", name="delete_child_delete", methods={"DELETE"})
     */
    public function index(Request $request)
    {
        $child = $this->getDoctrine()->getRepository(Kind::class)->find($request->get('kind_id'));
        if ($child->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $res = $this->deleteChildService->deleteChild($child)?$this->translator->trans('Erfolgreich gelöscht'):$this->translator->trans('Fehler. Bitte versuchen Sie es erneut.');
        return new JsonResponse(array('redirect'=>$this->generateUrl('child_show',array('id'=>$child->getSchule()->getOrganisation()->getId(),'snack'=>$res))));
    }
}
