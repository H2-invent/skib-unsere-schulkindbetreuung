<?php

namespace App\Controller;

use App\Entity\Stadt;
use App\Repository\FerienblockRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class FerienprogrammAnzeigeController extends AbstractController
{
    public function __construct(private readonly FerienblockRepository $ferienblockRepository)
    {
    }

    /**
     * @Route("/{slug}/ferienprogramm", name="ferienprogramm_anzeige", methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
     */
    public function __invoke(Stadt $stadt): Response
    {
        if (!$stadt->getActive() || $stadt->getDeleted() || !$stadt->getFerienprogramm()) {
            throw $this->createNotFoundException();
        }

        $today = new \DateTimeImmutable('today');

        return $this->render('ferienprogramm_anzeige/index.html.twig', [
            'stadt' => $stadt,
            'ferienprogramme' => $this->ferienblockRepository->findBookableUpcomingForCity($stadt, $today),
        ]);
    }
}
