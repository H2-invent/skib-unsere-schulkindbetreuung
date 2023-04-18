<?php

namespace App\Tests\Service\Widget;

use App\Entity\Kind;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use App\Repository\KindRepository;
use App\Service\ChildInBlockService;
use App\Service\WidgetService;
use App\Twig\Eltern;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChildInBlockSizeTest extends KernelTestCase
{
    private $k1;
    private $k2;
    private $k3;
    private $k4;
    private $k5;
    private $zeitblock;


    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->k1 = (new Kind())->setEltern((new Stammdaten())->setCreatedAt(new \DateTime('01.01.2022')))->setStartDate(new \DateTime('01.01.2022'));
        $this->k2 = (new Kind())->setEltern((new Stammdaten())->setCreatedAt(new \DateTime('01.02.2022')))->setStartDate(new \DateTime('01.03.2022'));
        $this->k3 = (new Kind())->setEltern((new Stammdaten())->setCreatedAt(new \DateTime('16.03.2022')))->setStartDate(new \DateTime('01.04.2022'));
        $this->k4 = (new Kind())->setEltern((new Stammdaten())->setCreatedAt(new \DateTime('15.03.2022')))->setStartDate(new \DateTime('01.04.2022'));
        $this->k5 = (new Kind())->setEltern((new Stammdaten())->setCreatedAt(new \DateTime('01.01.2022')))->setStartDate(new \DateTime('01.02.2022'));
        $this->zeitblock = (new Zeitblock())->addKind($this->k1)->addKind($this->k5)->addKind($this->k3);
        $this->k1->addZeitblock($this->zeitblock);
        $this->k5->addZeitblock($this->zeitblock);
        $this->k3->addZeitblock(zeitblock: $this->zeitblock);
    }

    public function testSomething(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());
        $widgetService = self::getContainer()->get(ChildInBlockService::class);
        $widgetService = self::getContainer()->get(ChildInBlockService::class);
        $kinder = [
            $this->k1,
            $this->k2,
            $this->k3,
            $this->k5,
        ];
        $res = $widgetService->sortChilds($kinder);
        self::assertEquals([
            $this->k1,
            $this->k5,
            $this->k2,
            $this->k3
        ], $res);
    }

    public function testwithTwoDates(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());
        $widgetService = self::getContainer()->get(ChildInBlockService::class);
        $kinder = [
            $this->k1,
            $this->k2,
            $this->k3,
            $this->k4,
            $this->k5,
        ];

        $res = $widgetService->sortChilds($kinder);
        self::assertEquals([
            $this->k1,
            $this->k5,
            $this->k2,
            $this->k4,
            $this->k3,

        ], $res);
    }

    public function testinBlock()
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());
        $widgetService = self::getContainer()->get(ChildInBlockService::class);
        $kinder = [
            $this->k1,
            $this->k5,
            $this->k2,
            $this->k4,
            $this->k3,

        ];

        self::assertEquals(null, $widgetService->checkIfChildIsNow($kinder, $this->zeitblock, new \DateTime('31.12.2021')));
        self::assertEquals($this->k1, $widgetService->checkIfChildIsNow($kinder, $this->zeitblock, new \DateTime('02.01.2022')));
        self::assertEquals($this->k1, $widgetService->checkIfChildIsNow($kinder, $this->zeitblock, new \DateTime('01.01.2022')));
        self::assertEquals(null, $widgetService->checkIfChildIsNow($kinder, $this->zeitblock, new \DateTime('01.03.2022')));
        self::assertEquals($this->k3, $widgetService->checkIfChildIsNow($kinder, $this->zeitblock, new \DateTime('01.04.2022')));
        self::assertEquals($this->k3, $widgetService->checkIfChildIsNow($kinder, $this->zeitblock, new \DateTime('01.06.2022')));
    }


    public function testNumberBlock()
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $kindRepo = $this->createMock(KindRepository::class);
        // use getMock() on PHPUnit 5.3 or below
        // $employeeRepository = $this->getMock(ObjectRepository::class);
        $kindRepo->expects($this->any())
            ->method('findHistoryOfThisChild')
            ->willReturn(
                [
                    $this->k1,
                    $this->k2,
                    $this->k3,
                    $this->k4,
                    $this->k5,
                ]
            );
        $objectManager = $this->createMock(EntityManagerInterface::class);

        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($kindRepo);
        $widgetService = new ChildInBlockService($objectManager);

        self::assertEquals([], $widgetService->getCurrentChildOfZeitblock($this->zeitblock, new \DateTime('31.12.2021')));
        self::assertEquals([$this->k1], $widgetService->getCurrentChildOfZeitblock($this->zeitblock, new \DateTime('01.01.2022')));
        self::assertEquals([$this->k5], $widgetService->getCurrentChildOfZeitblock($this->zeitblock, new \DateTime('01.02.2022')));
        self::assertEquals([], $widgetService->getCurrentChildOfZeitblock($this->zeitblock, new \DateTime('01.03.2022')));
        self::assertEquals([$this->k3], $widgetService->getCurrentChildOfZeitblock($this->zeitblock, new \DateTime('01.04.2022')));
    }

}
