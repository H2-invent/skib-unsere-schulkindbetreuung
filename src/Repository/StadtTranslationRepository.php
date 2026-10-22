<?php

namespace App\Repository;

use App\Entity\StadtTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StadtTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method StadtTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method StadtTranslation[]    findAll()
 * @method StadtTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StadtTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StadtTranslation::class);
    }
}
