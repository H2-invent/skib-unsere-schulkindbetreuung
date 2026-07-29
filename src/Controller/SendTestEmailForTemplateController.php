<?php

namespace App\Controller;

use App\Repository\StadtRepository;
use App\Service\MailerService;
use App\Service\TemplatePreview\PreviewFixtureFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SendTestEmailForTemplateController extends AbstractController
{
    public function __construct(
        private MailerService $mailerService,
    private StadtRepository $stadtRepository,
    private PreviewFixtureFactory $previewFixtureFactory)
    {

    }

    #[Route('/city_edit/stadtverwaltung/test/email/{template}', name: 'app_send_test_email_for_template')]
    public function index(string $template, Request $request): Response
    {
        $stadt = $this->stadtRepository->find($request->get('stadt'));
        $fixture = $this->previewFixtureFactory->create();

        try {
            $html = $this->renderView("email/{$template}.html.twig", [
                'eltern' => $fixture->eltern,
                'stammdaten'=>$fixture->eltern,
                'kind' => $fixture->kind,
                'stadt' => $stadt,
                'organisation'=>$fixture->organisation
            ]);

            $this->mailerService->sendEmail(
                'test@unsere-schulkindbetreuung.de',
                'Mailtester',
                $this->getUser()->getEmail(),
                'Test-Email',
                $html,
                'noreplay@unsere-schulkindbetreuung.de'
            );
        }catch(\Exception $exception) {
            return new Response("Fehler: " . $exception->getMessage());
        }

        return new Response("Test-E-Mail für Template '{$template}' wurde gesendet.");
    }
}
