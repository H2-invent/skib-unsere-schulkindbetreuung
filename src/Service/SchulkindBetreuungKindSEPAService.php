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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SchulkindBetreuungKindSEPAService
{
    private $em;
    private $translator;
    private $validator;
    private $generator;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, ValidatorInterface $validator, UrlGeneratorInterface $urlGenerator)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->generator = $urlGenerator;
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
