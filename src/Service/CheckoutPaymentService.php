<?php

namespace App\Service;

use App\Entity\Active;
use App\Entity\Ferienblock;
use App\Entity\Kind;
use App\Entity\KindFerienblock;
use App\Entity\Organisation;
use App\Entity\Payment;
use App\Entity\PaymentSepa;
use App\Entity\Rechnung;
use App\Entity\Sepa;
use App\Entity\Stadt;

use App\Entity\Stammdaten;

use App\Entity\Zeitblock;
use App\Form\Type\ConfirmType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\Extension\Core\Type\TextType;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use function Doctrine\ORM\QueryBuilder;


// <- Add this

class CheckoutPaymentService
{


    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

    }

   public function getFerienBlocksKinder(Organisation $organisation, Stammdaten $stammdaten) :ArrayCollection
   {
        $qb = $this->em->getRepository(KindFerienblock::class)->createQueryBuilder('kFb');
        $qb->innerJoin('kFb.ferienblock', 'ferienBlock')
            ->andWhere('ferienBlock.organisation = :organisation')
            ->innerJoin('kFb.kind','kind')
            ->andWhere('kind.eltern = :eltern')
            ->setParameter('eltern',$stammdaten)
            ->setParameter('organisation',$organisation);
        $query = $qb->getQuery();
        return new ArrayCollection($query->getResult());
   }
    public function getOrganisationFromStammdaten(Stammdaten $stammdaten) :ArrayCollection
    {
        $qb = $this->em->getRepository(Organisation::class)->createQueryBuilder('org');
        $qb->innerJoin('org.ferienblocks', 'fB')
            ->innerJoin('fB.kindFerienblocks', 'kindFb')
            ->innerJoin('kindFb.kind', 'kind')
            ->andWhere('kind.eltern = :eltern')
            ->setParameter('eltern', $stammdaten);
        $query = $qb->getQuery();
        return new ArrayCollection($query->getResult());
    }
    public function getPaymentWithEmptyNonce(Stammdaten $stammdaten) :ArrayCollection
    {
        $qb = $this->em->getRepository(Payment::class)->createQueryBuilder('pay');
        $qb->innerJoin('pay.braintree', 'bT')
            ->andWhere($qb->expr()->isNull('bT.nonce'))
            ->andWhere('pay.stammdaten = :eltern')
            ->setParameter('eltern', $stammdaten);
        $query = $qb->getQuery();
        return new ArrayCollection($query->getResult());
    }

}
