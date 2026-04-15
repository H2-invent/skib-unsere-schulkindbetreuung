<?php

namespace App\Controller;

use App\Entity\Content;
use App\Entity\Stadt;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LandingController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[Route(path: '/', name: 'welcome_landing')]
    public function welcomeAction(TranslatorInterface $translator, Request $request)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findBy(['deleted' => false, 'active' => true]);
        $title = $translator->trans('Schulkindbetreuung SKiB') . ' | ' . $translator->trans('Online Anmeldung und Verwaltung');
        $metaDescription = $translator->trans('SKiB ist bisher einzige online Lösung für die Anmeldung und Verwaltung der Schulkindbetreuung und Ferienbetreuung. Die Webanwendung ermöglicht eine direkte Vernetzung zwischen Erziehungsberechtigten, externen Organisationen und der städtischen Verwaltung bzw. Schulträger.');
        $contentAll = $this->managerRegistry->getRepository(Content::class)->findBy(['activ' => true], ['reihenfolge' => 'ASC']);
        $content = $this->managerRegistry->getRepository(Content::class)->findOneBy(['activ' => true], ['reihenfolge' => 'ASC']);

        return $this->render('landing/landing.html.twig', ['content' => $contentAll, 'contentSelect' => $content, 'metaDescription' => $metaDescription, 'title' => $title, 'stadt' => $stadt]);
    }

    #[Route(path: '/feature/{content}', name: 'welcome_landing_slug')]
    #[ParamConverter('content', options: ['mapping' => ['content' => 'slug']])]
    public function welcomeFeatureAction(Content $content, TranslatorInterface $translator, Request $request)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findBy(['deleted' => false, 'active' => true]);
        $title = $content->translate()->getTitle() . ' | unsere-Schulkindbetreuung.de';
        $metaDescription = $content->translate()->getMeta();

        $contentAll = $this->managerRegistry->getRepository(Content::class)->findBy(['activ' => true], ['reihenfolge' => 'ASC']);

        return $this->render('landing/landing.html.twig', ['content' => $contentAll, 'contentSelect' => $content, 'metaDescription' => $metaDescription, 'title' => $title, 'stadt' => $stadt]);
    }
}
