<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;
use App\Entity\Organisation;
use App\Entity\Stammdaten;
use Doctrine\ORM\EntityManagerInterface;

class SchulkindBetreuungKindSEPAService
{
    private $em;




    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;


    }

    public function findOrg(Stammdaten $adresse)
    {
        $qb = $this->em->getRepository(Organisation::class)->createQueryBuilder('organisation');
        $qb->innerJoin('organisation.schule', 'schule')
            ->innerJoin('schule.kinder', 'kinder')
            ->andWhere('kinder.eltern = :stammdaten')
            ->setParameter('stammdaten', $adresse);
        $query = $qb->getQuery();
        return $query->getResult();
    }

}
