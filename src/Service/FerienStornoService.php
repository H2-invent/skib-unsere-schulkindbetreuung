<?php

namespace App\Service;
use App\Entity\KindFerienblock;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;


class FerienStornoService
{


    private $em;
    private $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
    }

    public function toggleBlock(KindFerienblock $kindFerienblock, Stammdaten $stammdaten)
    {
        if ($stammdaten === null) {
            return 0;
        }

        if ($kindFerienblock->getState() == 20) {
            $result['text'] = 'Ferienprogram bereits storniert';
        }
        if ($kindFerienblock->getMarkedAsStorno() === false) {
            $kindFerienblock->setMarkedAsStorno(true);
            $result['cardText'] = $this->translator->trans('Als Storniert vorgemerkt');
            $result['state'] = 20;
        } else {
            $kindFerienblock->setMarkedAsStorno(false);
            $result['cardText'] = $this->translator->trans('Stornieren');
            $result['state'] = 0;
        }

        $result['error'] = 0;
        $this->em->persist($kindFerienblock);
        $this->em->flush();
        return $result;
    }

    public function stornoAbschluss(Stammdaten $stammdaten)
    {
        $qb = $this->em->getRepository(KindFerienblock::class)->createQueryBuilder('kinderBlock');
        $qb->innerJoin('kinderBlock.kind', 'kind')
            ->andWhere('kind.eltern = :eltern')
            ->andWhere('kinderBlock.markedAsStorno = true ')
            ->andWhere($qb->expr()->lt('kinderBlock.state', 20))
            ->setParameter('eltern', $stammdaten);
        $query = $qb->getQuery();
        $blocks = $query->getResult();
        foreach ($blocks as $data) {
            $data->setState(20);
            $this->em->persist($data);
        }
        $this->em->flush();
    }

}
