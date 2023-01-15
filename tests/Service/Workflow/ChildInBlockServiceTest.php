<?php

namespace App\Tests\Service\Workflow;

use App\Entity\Kind;
use App\Entity\Zeitblock;
use App\Service\ChildInBlockService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChildInBlockServiceTest extends KernelTestCase
{
    public function testSomething(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $zeitblock = new Zeitblock();
        $kind1 = new Kind();
        $kind1->setVorname('test1')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind2 = new Kind();
        $kind2->setVorname('test2')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind3 = new Kind();
        $kind3->setVorname('test3')
            ->setStartDate(new \DateTime('01.11.2022'));

        $kind4 = new Kind();
        $kind4->setVorname('test4')
            ->setStartDate(new \DateTime('01.12.2022'));

        $zeitblock->addKind($kind1)->addKind($kind2)->addKind($kind3)->addKind($kind4);
        $kind1->addZeitblock($zeitblock);
        $kind2->addZeitblock($zeitblock);
        $kind3->addZeitblock($zeitblock);
        $kind4->addZeitblock($zeitblock);
        $childInBlockService= self::getContainer()->get(ChildInBlockService::class);

        self::assertEquals($kind4,$childInBlockService->checkIfChildIsNowOrInFuture(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.11.2022')));

        self::assertEquals($kind3,$childInBlockService->checkIfChildIsNow(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.11.2022')));

        //$routerService = static::getContainer()->get('router');
        //$myCustomService = static::getContainer()->get(CustomService::class);
    }

    public function testendinDecember(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $zeitblock = new Zeitblock();
        $kind1 = new Kind();
        $kind1->setVorname('test1')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind2 = new Kind();
        $kind2->setVorname('test2')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind3 = new Kind();
        $kind3->setVorname('test3')
            ->setStartDate(new \DateTime('01.11.2022'));

        $kind4 = new Kind();
        $kind4->setVorname('test4')
            ->setStartDate(new \DateTime('01.12.2022'));

        $zeitblock->addKind($kind1)->addKind($kind2)->addKind($kind3);
        $kind1->addZeitblock($zeitblock);
        $kind2->addZeitblock($zeitblock);
        $kind3->addZeitblock($zeitblock);

        $childInBlockService= self::getContainer()->get(ChildInBlockService::class);

        self::assertEquals($kind3,$childInBlockService->checkIfChildIsNowOrInFuture(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.11.2022')));
        self::assertEquals($kind3,$childInBlockService->checkIfChildIsNow(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.11.2022')));
        //$routerService = static::getContainer()->get('router');
        //$myCustomService = static::getContainer()->get(CustomService::class);
    }
    public function testendinNovember(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $zeitblock = new Zeitblock();
        $kind1 = new Kind();
        $kind1->setVorname('test1')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind2 = new Kind();
        $kind2->setVorname('test2')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind3 = new Kind();
        $kind3->setVorname('test3')
            ->setStartDate(new \DateTime('01.11.2022'));

        $kind4 = new Kind();
        $kind4->setVorname('test4')
            ->setStartDate(new \DateTime('01.12.2022'));

        $zeitblock->addKind($kind1)->addKind($kind2);
        $kind1->addZeitblock($zeitblock);
        $kind2->addZeitblock($zeitblock);


        $childInBlockService= self::getContainer()->get(ChildInBlockService::class);

        self::assertEquals(null,$childInBlockService->checkIfChildIsNowOrInFuture(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.11.2022')));
        self::assertEquals(null,$childInBlockService->checkIfChildIsNow(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.11.2022')));

        //$routerService = static::getContainer()->get('router');
        //$myCustomService = static::getContainer()->get(CustomService::class);
    }
    public function testendinNovemberButPresentisInOktober(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $zeitblock = new Zeitblock();
        $kind1 = new Kind();
        $kind1->setVorname('test1')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind2 = new Kind();
        $kind2->setVorname('test2')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind3 = new Kind();
        $kind3->setVorname('test3')
            ->setStartDate(new \DateTime('01.11.2022'));

        $kind4 = new Kind();
        $kind4->setVorname('test4')
            ->setStartDate(new \DateTime('01.12.2022'));

        $zeitblock->addKind($kind1)->addKind($kind2);
        $kind1->addZeitblock($zeitblock);
        $kind2->addZeitblock($zeitblock);


        $childInBlockService= self::getContainer()->get(ChildInBlockService::class);

        self::assertEquals($kind2,$childInBlockService->checkIfChildIsNowOrInFuture(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.10.2022')));
        self::assertEquals($kind2,$childInBlockService->checkIfChildIsNow(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.10.2022')));
        //$routerService = static::getContainer()->get('router');
        //$myCustomService = static::getContainer()->get(CustomService::class);
    }

    public function testendinOctober(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $zeitblock = new Zeitblock();
        $kind1 = new Kind();
        $kind1->setVorname('test1')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind2 = new Kind();
        $kind2->setVorname('test2')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind3 = new Kind();
        $kind3->setVorname('test3')
            ->setStartDate(new \DateTime('01.11.2022'));

        $kind4 = new Kind();
        $kind4->setVorname('test4')
            ->setStartDate(new \DateTime('01.12.2022'));

        $zeitblock->addKind($kind1);
        $kind1->addZeitblock($zeitblock);



        $childInBlockService= self::getContainer()->get(ChildInBlockService::class);

        self::assertEquals(null,$childInBlockService->checkIfChildIsNowOrInFuture(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.10.2022')));
        self::assertEquals(null,$childInBlockService->checkIfChildIsNow(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.10.2022')));
        //$routerService = static::getContainer()->get('router');
        //$myCustomService = static::getContainer()->get(CustomService::class);
    }

    public function teststartinDezeember(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $zeitblock = new Zeitblock();
        $kind1 = new Kind();
        $kind1->setVorname('test1')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind2 = new Kind();
        $kind2->setVorname('test2')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind3 = new Kind();
        $kind3->setVorname('test3')
            ->setStartDate(new \DateTime('01.12.2022'));

        $kind4 = new Kind();
        $kind4->setVorname('test4')
            ->setStartDate(new \DateTime('01.12.2022'));

        $zeitblock->addKind($kind4);
        $kind4->addZeitblock($zeitblock);



        $childInBlockService= self::getContainer()->get(ChildInBlockService::class);

        self::assertEquals($kind4,$childInBlockService->checkIfChildIsNowOrInFuture(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.10.2022')));
        self::assertEquals(null,$childInBlockService->checkIfChildIsNow(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.10.2022')));
        //$routerService = static::getContainer()->get('router');
        //$myCustomService = static::getContainer()->get(CustomService::class);
    }

    public function teststartinDezeemberfirst(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $zeitblock = new Zeitblock();
        $kind1 = new Kind();
        $kind1->setVorname('test1')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind2 = new Kind();
        $kind2->setVorname('test2')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind3 = new Kind();
        $kind3->setVorname('test3')
            ->setStartDate(new \DateTime('01.12.2022'));

        $kind4 = new Kind();
        $kind4->setVorname('test4')
            ->setStartDate(new \DateTime('01.12.2022'));

        $zeitblock->addKind($kind3);
        $kind3->addZeitblock($zeitblock);



        $childInBlockService= self::getContainer()->get(ChildInBlockService::class);

        self::assertEquals(null,$childInBlockService->checkIfChildIsNowOrInFuture(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.10.2022')));
        self::assertEquals(null,$childInBlockService->checkIfChildIsNow(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.10.2022')));
        //$routerService = static::getContainer()->get('router');
        //$myCustomService = static::getContainer()->get(CustomService::class);
    }
    public function teststartinDezeemberfirstandactober(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $zeitblock = new Zeitblock();
        $kind1 = new Kind();
        $kind1->setVorname('test1')
            ->setStartDate(new \DateTime('01.10.2022'));

        $kind2 = new Kind();
        $kind2->setVorname('test2')
            ->setStartDate(new \DateTime('01.11.2022'));

        $kind3 = new Kind();
        $kind3->setVorname('test3')
            ->setStartDate(new \DateTime('01.12.2022'));

        $kind4 = new Kind();
        $kind4->setVorname('test4')
            ->setStartDate(new \DateTime('01.12.2022'));

        $zeitblock->addKind($kind1)->addKind($kind3);
        $kind3->addZeitblock($zeitblock);
        $kind1->addZeitblock($zeitblock);



        $childInBlockService= self::getContainer()->get(ChildInBlockService::class);

        self::assertEquals(null,$childInBlockService->checkIfChildIsNowOrInFuture(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.11.2022')));
        self::assertEquals(null,$childInBlockService->checkIfChildIsNow(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.11.2022')));
        //$routerService = static::getContainer()->get('router');
        //$myCustomService = static::getContainer()->get(CustomService::class);
    }

    public function teststartinDezeemberfirstandanovemberfirst(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $zeitblock = new Zeitblock();
        $kind1 = new Kind();
        $kind1->setVorname('test1')
            ->setStartDate(new \DateTime('01.11.2022'));

        $kind2 = new Kind();
        $kind2->setVorname('test2')
            ->setStartDate(new \DateTime('01.11.2022'));

        $kind3 = new Kind();
        $kind3->setVorname('test3')
            ->setStartDate(new \DateTime('01.12.2022'));

        $kind4 = new Kind();
        $kind4->setVorname('test4')
            ->setStartDate(new \DateTime('01.12.2022'));

        $zeitblock->addKind($kind1)->addKind($kind3);
        $kind3->addZeitblock($zeitblock);
        $kind1->addZeitblock($zeitblock);



        $childInBlockService= self::getContainer()->get(ChildInBlockService::class);

        self::assertEquals(null,$childInBlockService->checkIfChildIsNowOrInFuture(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.11.2022')));
        self::assertEquals(null,$childInBlockService->checkIfChildIsNow(array($kind1,$kind2,$kind3,$kind4),$zeitblock,new \DateTime('15.11.2022')));
        //$routerService = static::getContainer()->get('router');
        //$myCustomService = static::getContainer()->get(CustomService::class);
    }

}
