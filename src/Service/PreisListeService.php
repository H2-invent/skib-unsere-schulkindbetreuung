<?php

/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01.
 */

namespace App\Service;

use App\Entity\Active;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class PreisListeService
{
    protected $parameterBag;

    public function __construct(
        private EntityManagerInterface $em,
        private SchuljahrService $schuljahrService,
        private UrlGeneratorInterface $generator,
        private Environment $templating,
        private TranslatorInterface $translator,
        ParameterBagInterface $parameterBag,
    ) {
        $this->parameterBag = $parameterBag;
    }

    public function preisliste(Stadt $stadt, Schule $schule, $gehaltIst, $artIst)
    {
        $schuljahr = $this->em->getRepository(Active::class)->findSchuljahrFromCity($stadt, new \DateTime());
        $schulen = $this->em->getRepository(Schule::class)->findBy(['stadt' => $stadt, 'deleted' => false]);
        $gehalt = $stadt->getGehaltsklassen();
        $onlyOneType = null;
        $art = [
            $this->translator->trans('Ganztag') => 1,
            $this->translator->trans('Halbtag') => 2,
        ];

        $req = [
            'deleted' => false,
            'active' => $schuljahr,
            'schule' => $schule,
        ];

        $req['ganztag'] = $artIst;

        $qb = $this->em->getRepository(Zeitblock::class)->createQueryBuilder('zeitblock');
        $qb
            ->andWhere('zeitblock.deleted = false')
            ->andWhere('zeitblock.active = :active')
            ->andWhere('zeitblock.schule = :schule')
            ->setParameter('active', $schuljahr)
            ->setParameter('schule', $schule);
        $query = $qb->getQuery();
        $checkBlock = $query->getResult();

        $onlyOneType = true;
        $first = $checkBlock[0]->getGanztag();
        foreach ($checkBlock as $data) {
            if ($first != $data->getGanztag()) {
                $onlyOneType = false;
                break;
            }
        }

        if ($onlyOneType == true) {
            $artIst = $first;
            $req['ganztag'] = $artIst;
        }
        $block = $this->em->getRepository(Zeitblock::class)->findBy($req, ['von' => 'asc']);

        $renderBlocks = [];
        foreach ($block as $data) {
            $renderBlocks[$data->getWochentag()][] = $data;
        }

        return $this->templating->render('preisliste/index.html.twig', [
            'schulen' => $schulen,
            'gehalt' => $gehalt,
            'art' => array_flip($art),
            'schule' => $schule,
            'gehaltIst' => $gehaltIst,
            'blocks' => $renderBlocks,
            'artIst' => $artIst,
            'onlyOneType' => $onlyOneType,
        ]);
    }
}
