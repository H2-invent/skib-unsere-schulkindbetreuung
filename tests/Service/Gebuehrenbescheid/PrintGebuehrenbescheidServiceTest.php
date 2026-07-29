<?php

declare(strict_types=1);

namespace App\Tests\Service\Gebuehrenbescheid;

use App\Entity\Stadt;
use App\Service\Gebuehrenbescheid\PrintGebuehrenbescheidService;
use App\Service\TemplatePreview\PreviewFixtureFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PrintGebuehrenbescheidServiceTest extends KernelTestCase
{
    public function testEmptyAndWhitespaceOnlyTemplatesAreNotConsideredPresent(): void
    {
        self::bootKernel();
        $service = self::getContainer()->get(PrintGebuehrenbescheidService::class);

        self::assertFalse($service->hasTemplate($this->stadt(null), 'de'), 'null template');
        self::assertFalse($service->hasTemplate($this->stadt(''), 'de'), 'empty template');
        // What the WYSIWYG editor leaves behind in a field the admin has visually emptied.
        self::assertFalse($service->hasTemplate($this->stadt('<p><br></p>'), 'de'), 'editor leftovers');
        self::assertTrue($service->hasTemplate($this->stadt('<p>Bescheid</p>'), 'de'));
    }

    /**
     * Cities normally have en/fr translation rows even when only the German template is filled, so
     * Doctrine-Behaviors' own translate() fallback does not kick in and the service has to fall back itself.
     */
    public function testAnUnfilledLocaleFallsBackToTheGermanTemplate(): void
    {
        self::bootKernel();
        $service = self::getContainer()->get(PrintGebuehrenbescheidService::class);

        $stadt = $this->stadt('<p>Deutscher Bescheid</p>');
        // The empty row that makes the built-in fallback a no-op.
        $stadt->translate('fr')->setPdftemplateGebuehrenbescheid(null);
        $stadt->mergeNewTranslations();

        self::assertTrue($service->hasTemplate($stadt, 'fr'), 'fr must fall back to the de template');

        $fixtureFactory = self::getContainer()->get(PreviewFixtureFactory::class);
        $fixture = $fixtureFactory->create();
        $pdf = $service->render(
            $stadt,
            $fixture->kind,
            $fixture->eltern,
            $fixture->organisation,
            $fixtureFactory->createStubFeeSummary(),
            'fr',
        );

        self::assertStringStartsWith('%PDF-', $pdf);
    }

    public function testRendersACityAuthoredTemplateIntoAPdf(): void
    {
        self::bootKernel();
        $service = self::getContainer()->get(PrintGebuehrenbescheidService::class);
        $fixtureFactory = self::getContainer()->get(PreviewFixtureFactory::class);

        $fixture = $fixtureFactory->create();
        $pdf = $service->render(
            $this->stadt('<h1>Gebuehrenbescheid {{ gebuehren.gesamt }} fuer {{ kind.vorname }}</h1>'),
            $fixture->kind,
            $fixture->eltern,
            $fixture->organisation,
            $fixtureFactory->createStubFeeSummary(),
            'de',
        );

        self::assertStringStartsWith('%PDF-', $pdf);
    }

    public function testFallsBackToTheBuiltInLayoutWhenNoTemplateIsAuthored(): void
    {
        self::bootKernel();
        $service = self::getContainer()->get(PrintGebuehrenbescheidService::class);
        $fixtureFactory = self::getContainer()->get(PreviewFixtureFactory::class);

        $fixture = $fixtureFactory->create();
        $pdf = $service->render(
            $this->stadt(''),
            $fixture->kind,
            $fixture->eltern,
            $fixture->organisation,
            $fixtureFactory->createStubFeeSummary(),
            'de',
        );

        self::assertStringStartsWith('%PDF-', $pdf);
    }

    /**
     * A broken city template must surface as an exception so that AnmeldeEmailService can swallow it and still
     * send the confirmation mail.
     */
    public function testABrokenTemplateThrows(): void
    {
        self::bootKernel();
        $service = self::getContainer()->get(PrintGebuehrenbescheidService::class);
        $fixtureFactory = self::getContainer()->get(PreviewFixtureFactory::class);

        $fixture = $fixtureFactory->create();

        $this->expectException(\Twig\Error\Error::class);
        $service->render(
            $this->stadt('{% if %}'),
            $fixture->kind,
            $fixture->eltern,
            $fixture->organisation,
            $fixtureFactory->createStubFeeSummary(),
            'de',
        );
    }

    /**
     * Deliberately without a logo, so the renderer never reads from Flysystem.
     */
    private function stadt(?string $template): Stadt
    {
        $stadt = new Stadt();
        $stadt->setName('Musterstadt');
        $stadt->setAdresse('Rathausplatz 1');
        $stadt->setPlz('12345');
        $stadt->setOrt('Musterstadt');
        $stadt->setTelefon('0123456789');
        $stadt->setEmail('stadt@example.com');
        $stadt->translate('de')->setPdftemplateGebuehrenbescheid($template);
        $stadt->mergeNewTranslations();

        return $stadt;
    }
}
