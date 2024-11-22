<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\Zeitblock;
use App\Service\WartelisteService;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class WartelisteController extends AbstractController
{
    public function __construct(
        private WartelisteService   $wartelisteService,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
    )
    {
    }

    #[Route('/org_accept/warteliste/show/{block_id}', name: 'warteliste_show')]
    public function showWarteliste(
        Request   $request,
        #[MapEntity(id: 'block_id')]
        Zeitblock $zeitblock = null): Response
    {
        if (!$zeitblock) {
            throw new NotFoundHttpException('Zeitblock wurde nicht gefunden');
        }
        $fictiveDate = $request->get('fictiveDate') ? new \DateTime($request->get('fictiveDate')) : (new \DateTime())->modify('first day of next month');

        return $this->render('warteliste/index.html.twig', [
            'kinder' => $zeitblock->getWartelisteKinder(),
            'text' => 'Warteliste',
            'block' => $zeitblock,
            'fictiveDate' => $fictiveDate,
        ]);
    }

    #[Route('/org_accept/warteliste/kid/add/{kind_id}/{block_id}', name: 'warteliste_add_kid')]
    public function add(
        Request   $request,
        #[MapEntity(id: 'kind_id')]
        Kind      $kind = null,
        #[MapEntity(id: 'block_id')]
        Zeitblock $zeitblock = null): Response
    {
        if (!$kind || !$zeitblock) {
            throw new NotFoundHttpException('Kind wurde nicht gefunden');
        }
        try {
            $this->wartelisteService->addKindToWarteliste($kind, $zeitblock);
        } catch (\Exception) {
            return new JsonResponse(['snack' => $this->translator->trans('Fehler, bitte laden Sie die Seite neu')]);

        }
        return new JsonResponse(['snack' => $this->translator->trans('Kind erfolgreich auf Warteliste verschoben.')]);

    }
    #[Route('/org_accept/warteliste/kid/add_complete/{kind_id}', name: 'warteliste_add_completekid')]
    public function addComplete(
        Request   $request,
        #[MapEntity(id: 'kind_id')]
        Kind      $kind = null,
        ): Response
    {
        if (!$kind) {
            throw new NotFoundHttpException('Kind wurde nicht gefunden');
        }
        try {
            foreach ($kind->getBeworben() as $zb){
                $this->wartelisteService->addKindToWarteliste($kind, $zb);
            }

        } catch (\Exception) {
            return new JsonResponse(['snack' => $this->translator->trans('Fehler, bitte laden Sie die Seite neu')]);

        }
        return new JsonResponse(['snack' => $this->translator->trans('Kind erfolgreich auf Warteliste verschoben.')]);

    }


    #[Route('/org_accept/warteliste/kid/remove/{kind_id}/{block_id}', name: 'warteliste_remove_kid')]
    public function remove(
        Request   $request,
        #[MapEntity(id: 'kind_id')]
        Kind      $kind = null,
        #[MapEntity(id: 'block_id')]
        Zeitblock $zeitblock = null): Response
    {
        if (!$kind || !$zeitblock) {
            throw new NotFoundHttpException('Kind wurde nicht gefunden');
        }
        try {
            $this->wartelisteService->removeKindFromWarteliste($kind, $zeitblock);
        } catch (\Exception) {
            return new JsonResponse(['snack' => $this->translator->trans('Fehler, bitte laden Sie die Seite neu')]);

        }
        return new JsonResponse(['snack' => $this->translator->trans('Kind erfolgreich aus die Warteliste entfernt')]);
    }

    #[Route('/org_accept/warteliste/kid/accept/{kind_id}/{block_id}', name: 'warteliste_accept_kid',methods: 'GET')]
    public function accept(
        Request   $request,
        #[MapEntity(id: 'kind_id')]
        Kind      $kind = null,
        #[MapEntity(id: 'block_id')]
        Zeitblock $zeitblock = null): Response
    {
        if (!$kind || !$zeitblock) {
            throw new NotFoundHttpException('Kind wurde nicht gefunden');
        }
        $date = $request->get('date');
        if ($date){
            try {
             $date = new \DateTime($date);
            }catch (\Exception $e){
                $this->logger->error($e->getMessage());
                throw new NotFoundHttpException('Datum nicht gefunden');
            }
        }
        try {
            $this->wartelisteService->acceptChildFromWaitingListForSpecificTime($kind, $zeitblock,$date);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return new JsonResponse(['snack' => $this->translator->trans('Fehler, bitte laden Sie die Seite neu')]);

        }
        return new JsonResponse(['snack' => $this->translator->trans('Kind erfolgreich in Betreuung verschoben')]);

    }
}
