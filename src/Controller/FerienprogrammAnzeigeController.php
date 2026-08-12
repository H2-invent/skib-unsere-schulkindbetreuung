<?php

namespace App\Controller;

use App\Repository\FerienblockRepository;
use App\Repository\StadtRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FerienprogrammAnzeigeController extends AbstractController
{
    public function __construct(
        private readonly FerienblockRepository $ferienblockRepository,
        private readonly StadtRepository $stadtRepository,
    ) {
    }

    #[Route('/{slug}/ferienprogramm', name: 'ferienprogramm_anzeige', methods: ['GET'])]
    public function __invoke(string $slug): Response
    {
        $stadt = $this->stadtRepository->findOneBy(['slug' => $slug, 'active' => true, 'deleted' => false]);
        if ($stadt === null) {
            throw $this->createNotFoundException();
        }

        $today = new \DateTimeImmutable('today');

        return $this->render('ferienprogramm_anzeige/index.html.twig', [
            'stadt' => $stadt,
            'ferienprogramme' => $this->ferienblockRepository->findBookableUpcomingForCity($stadt, $today),
        ]);
    }
}
