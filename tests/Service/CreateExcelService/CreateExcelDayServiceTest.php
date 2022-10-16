<?php

namespace App\Tests\Service\CreateExcelService;

use App\Entity\Kind;
use App\Entity\Zeitblock;
use App\Helper\ChildDateExcel;
use App\Service\ExcelExport\CreateExcelDayService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CreateExcelDayServiceTest extends KernelTestCase
{
    public function testaneinander(): void
    {
        $kernel = self::bootKernel();
        $kind = new Kind();


        $zb1 = new Zeitblock();
        $zb1->setVon(new \DateTime('12:00:00'))
            ->setBis(new \DateTime('13:00:00'))
            ->setWochentag(1)
            ->setGanztag(1);


        $zb2 = new Zeitblock();
        $zb2->setVon(new \DateTime('13:00:00'));
        $zb2->setBis(new \DateTime('14:00:00'))
            ->setWochentag(1)
            ->setGanztag(1);
        $kind->addZeitblock($zb2);
        $kind->addZeitblock($zb2);

        $childDay = self::getContainer()->get(CreateExcelDayService::class);


        $res = $childDay->createmergedDateTime(array($zb1, $zb2));
        $this->assertSame(720,$res[0]->getVon());
        $this->assertSame(840,$res[0]->getBis());
        self::assertEquals(1, sizeof($res));
    }
    public function testineinander(): void
    {
        $kernel = self::bootKernel();
        $kind = new Kind();


        $zb1 = new Zeitblock();
        $zb1->setVon(new \DateTime('12:00:00'))
            ->setBis(new \DateTime('13:00:00'))
            ->setWochentag(1)
            ->setGanztag(1);


        $zb2 = new Zeitblock();
        $zb2->setVon(new \DateTime('12:30:00'));
        $zb2->setBis(new \DateTime('12:40:00'))
            ->setWochentag(1)
            ->setGanztag(1);
        $kind->addZeitblock($zb2);
        $kind->addZeitblock($zb2);

        $childDay = self::getContainer()->get(CreateExcelDayService::class);


        $res = $childDay->createmergedDateTime(array($zb1, $zb2));
        $this->assertSame(720,$res[0]->getVon());
        $this->assertSame(780,$res[0]->getBis());
        self::assertEquals(1, sizeof($res));
    }
    public function testaueinander(): void
    {
        $kernel = self::bootKernel();
        $kind = new Kind();


        $zb1 = new Zeitblock();
        $zb1->setVon(new \DateTime('12:00:00'))
            ->setBis(new \DateTime('13:00:00'))
            ->setWochentag(1)
            ->setGanztag(1);


        $zb2 = new Zeitblock();
        $zb2->setVon(new \DateTime('14:00:00'));
        $zb2->setBis(new \DateTime('15:00:00'))
            ->setWochentag(1)
            ->setGanztag(1);
        $kind->addZeitblock($zb2);
        $kind->addZeitblock($zb2);

        $childDay = self::getContainer()->get(CreateExcelDayService::class);


        $res = $childDay->createmergedDateTime(array($zb1, $zb2));
        $this->assertSame(720,$res[0]->getVon());
        $this->assertSame(780,$res[0]->getBis());
        $this->assertSame(840,$res[1]->getVon());
        $this->assertSame(900,$res[1]->getBis());
        self::assertEquals(2, sizeof($res));
    }

    public function testmixed(): void
    {
        $kernel = self::bootKernel();
        $kind = new Kind();


        $zb1 = new Zeitblock();
        $zb1->setVon(new \DateTime('12:00:00'))
            ->setBis(new \DateTime('13:00:00'))
            ->setWochentag(1)
            ->setGanztag(1);


        $zb2 = new Zeitblock();
        $zb2->setVon(new \DateTime('13:00:00'));
        $zb2->setBis(new \DateTime('13:30:00'))
            ->setWochentag(1)
            ->setGanztag(1);

        $zb3 = new Zeitblock();
        $zb3->setVon(new \DateTime('14:00:00'));
        $zb3->setBis(new \DateTime('15:00:00'))
            ->setWochentag(1)
            ->setGanztag(1);

        $childDay = self::getContainer()->get(CreateExcelDayService::class);


        $res = $childDay->createmergedDateTime(array($zb1, $zb2, $zb3));
        $this->assertSame(720,$res[0]->getVon());
        $this->assertSame(810,$res[0]->getBis());
        $this->assertSame(840,$res[1]->getVon());
        $this->assertSame(900,$res[1]->getBis());
        self::assertEquals(2, sizeof($res));
    }
    public function testmixed2(): void
    {
        $kernel = self::bootKernel();
        $kind = new Kind();


        $zb1 = new Zeitblock();
        $zb1->setVon(new \DateTime('12:00:00'))
            ->setBis(new \DateTime('13:00:00'))
            ->setWochentag(1)
            ->setGanztag(1);


        $zb2 = new Zeitblock();
        $zb2->setVon(new \DateTime('13:00:00'));
        $zb2->setBis(new \DateTime('13:30:00'))
            ->setWochentag(1)
            ->setGanztag(1);

        $zb3 = new Zeitblock();
        $zb3->setVon(new \DateTime('12:00:00'));
        $zb3->setBis(new \DateTime('15:00:00'))
            ->setWochentag(1)
            ->setGanztag(1);

        $childDay = self::getContainer()->get(CreateExcelDayService::class);


        $res = $childDay->createmergedDateTime(array($zb1, $zb2, $zb3));
        $this->assertSame(720,$res[0]->getVon());
        $this->assertSame(900,$res[0]->getBis());

        self::assertEquals(1, sizeof($res));
    }
    public function testmixed3(): void
    {
        $kernel = self::bootKernel();
        $kind = new Kind();


        $zb1 = new Zeitblock();
        $zb1->setVon(new \DateTime('12:00:00'))
            ->setBis(new \DateTime('13:00:00'))
            ->setWochentag(1)
            ->setGanztag(1);


        $zb2 = new Zeitblock();
        $zb2->setVon(new \DateTime('13:00:00'));
        $zb2->setBis(new \DateTime('13:30:00'))
            ->setWochentag(1)
            ->setGanztag(1);

        $zb3 = new Zeitblock();
        $zb3->setVon(new \DateTime('11:00:00'));
        $zb3->setBis(new \DateTime('15:00:00'))
            ->setWochentag(1)
            ->setGanztag(1);

        $childDay = self::getContainer()->get(CreateExcelDayService::class);


        $res = $childDay->createmergedDateTime(array($zb1, $zb2, $zb3));
        $this->assertSame(660,$res[0]->getVon());
        $this->assertSame(900,$res[0]->getBis());
        self::assertEquals(1, sizeof($res));
    }
}
