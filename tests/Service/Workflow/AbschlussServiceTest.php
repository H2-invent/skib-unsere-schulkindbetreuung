<?php

namespace App\Tests;

use App\Entity\Kind;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use App\Repository\KindRepository;
use App\Repository\StammdatenRepository;
use App\Service\WorkflowAbschluss;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbschlussServiceTest extends KernelTestCase
{
    public function testNeANmeldung(): void
    {
        $kernel = self::bootKernel();

        $abschlussService = self::getContainer()->get(WorkflowAbschluss::class);

        $stammdaten = new Stammdaten();
        $stammdaten->setVorname('testEltern')
            ->setUid('test123')
            ->setName('test1')
            ->setAngemeldet(false);
        $kind1 = new Kind();
        $kind1->setStartDate(new \DateTime('1.10.2022'))
            ->setVorname('test1')
            ->setNachname('test1')
            ->setEltern($stammdaten)
            ->setGeburtstag(new \DateTime('1.1.2010'));
        $kind2 = new Kind();
        $kind2->setStartDate(new \DateTime('1.10.2022'))
            ->setVorname('test2')
            ->setNachname('test2')
            ->setEltern($stammdaten)
            ->setGeburtstag(new \DateTime('1.1.2010'));
        $zeitblock1 = new Zeitblock();
        $zeitblock1->setPreise(array('1,2,3,4'));
        $zeitblock2 = new Zeitblock();
        $zeitblock2->setPreise(array('2,3,4,5'));
        $zeitblock3 = new Zeitblock();
        $zeitblock3->setPreise(array('3,4,5,6'));
        $manager = self::getContainer()->get(EntityManagerInterface::class);
        $kind1->addZeitblock($zeitblock1);
        $kind1->addBeworben($zeitblock2);
        $kind2->addZeitblock($zeitblock3);
        $kind2->addBeworben($zeitblock1);
        $stadt = new Stadt();
        $stadt->setSecCodeAlwaysNew(false);
        $eltern =  $abschlussService->abschluss($stammdaten, $stadt);
        $stammdatenRepo = self::getContainer()->get(StammdatenRepository::class);
        $kindRepo = self::getContainer()->get(KindRepository::class);
        $allEltern = $stammdatenRepo->findBy(array('tracing'=>$eltern->getTracing()),array('created_at'=>'ASC'));
        self::assertEquals(2, sizeof($allEltern));
        self::assertNull($allEltern[0]->getCreatedAt());
        //$routerService = static::getContainer()->get('router');
        //$myCustomService = static::getContainer()->get(CustomService::class);
    }
}
