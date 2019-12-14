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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use function Doctrine\ORM\QueryBuilder;


// <- Add this

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
    public function stornoAbschluss(Stammdaten $stammdaten){
        $qb = $this->em->getRepository(KindFerienblock::class)->createQueryBuilder('kinderBlock');
        $qb->innerJoin('kinderBlock.kind','kind')
            ->andWhere('kind.eltern = :eltern')
            ->andWhere('kinderBlock.markedAsStorno = true ')
            ->andWhere($qb->expr()->lt('kinderBlock.state',20))
            ->setParameter('eltern',$stammdaten);
        $query = $qb->getQuery();
        $blocks = $query->getResult();
        dump($blocks);
        foreach ($blocks as $data){
            $data->setState(20);
            $this->em->persist($data);
        }
        $this->em->flush();
    }

}
