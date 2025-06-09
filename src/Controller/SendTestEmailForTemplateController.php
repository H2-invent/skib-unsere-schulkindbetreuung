<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stammdaten;
use App\Repository\StadtRepository;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SendTestEmailForTemplateController extends AbstractController
{
    public function __construct(
        private MailerService $mailerService,
    private StadtRepository $stadtRepository)
    {

    }

    #[Route('/city_edit/stadtverwaltung/test/email/{template}', name: 'app_send_test_email_for_template')]
    public function index(string $template, Request $request): Response
    {
        $stadt = $this->stadtRepository->find($request->get('stadt'));
        // Dummy Eltern erstellen
        $eltern = new Stammdaten();
        $eltern->setVorname('Max');
        $eltern->setName('Mustermann');
        $eltern->setEmail('test@example.com');
        $eltern->setStrasse('Musterstraße 1');
        $eltern->setStadt('Musterstadt');
        $eltern->setPlz(12345);
        $eltern->setPhoneNumber('0123456789');
        $eltern->setGdpr(true);
        $eltern->setUid('UID123456');
        $eltern->setSecCode('testcode');
        $eltern->setCreatedAt(new \DateTime());

        // Dummy Kind erstellen
        $kind = new Kind();
        $kind->setVorname('Lisa');
        $kind->setNachname('Mustermann');
        $kind->setGeburtstag(new \DateTime('2015-06-01'));
        $kind->setVegetarisch(true);
        $kind->setAusfluege(true);
        $kind->setEltern($eltern);
        $organisation = new Organisation();
        $organisation->setName('test ORganisation');
        // Dummy-Kind zu Eltern hinzufügen (falls Getter+Collection vorhanden)
        if (method_exists($eltern, 'addKind')) {
            $eltern->addKind($kind);
        }
        try {
            $html = $this->renderView("email/{$template}.html.twig", [
                'eltern' => $eltern,
                'stammdaten'=>$eltern,
                'kind' => $kind,
                'stadt' => $stadt,
                'organisation'=>$organisation
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