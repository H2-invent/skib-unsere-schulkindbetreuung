<?php

namespace App\Controller;

use App\Entity\Schule;
use App\Entity\Stadt;
use App\Service\PreisListeService;
use App\Service\SchuljahrService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PreislisteController extends AbstractController
{
    #[Route(path: '/{slug}/preisliste/{schule}', name: 'trager_preisliste', methods: ['GET'])]
    public function adresseAction(PreisListeService $preisListeService, #[MapEntity(mapping: ['slug' => 'slug'])] Stadt $stadt, Schule $schule, Request $request, SchuljahrService $schuljahrService)
    {
        $gehaltIst = $request->get('gehalt', sizeof($stadt->getGehaltsklassen()) - 1);
        $artIst = $request->get('art', 1);

        return new Response($preisListeService->preisliste($stadt, $schule, $gehaltIst, $artIst));
    }
}
