<?php

namespace App\Controller;

use App\Form\Type\SupportForm;
use it\thecsea\osticket_php_client\OsticketPhpClient;
use it\thecsea\osticket_php_client\OsticketPhpClientException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SupportController extends AbstractController
{
    #[Route(path: '/login/support/new', name: 'support')]
    public function index(Request $request, ParameterBagInterface $parameterBag)
    {
        $arr = [
            'name' => $this->getUser()->getVorname() . '-' . $this->getUser()->getNachname(),
            'email' => $this->getUser()->getEmail(),
            'phone' => '',
            'subject' => 'Betreff',
            'message' => '',
            'datenschutz' => true,
            'topicId' => '17',
            'user' => $this->getUser()->getUsername(),
        ];
        $form = $this->createForm(SupportForm::class, $arr);
        $form->handleRequest($request);

        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $arr = $form->getData();
            $arr['message'] = 'data:text/html,' . $arr['message'];
            $support = new OsticketPhpClient($parameterBag->get('osTicketUrl'), $parameterBag->get('osTicketApi'));
            try {
                $support->request('api/tickets.json', $arr);

                return $this->redirectToRoute('dashboard', ['snack' => 'Es wurde erfolgreich ein Ticket angelegt']);
            } catch (OsticketPhpClientException) {
                return $this->redirectToRoute('dashboard', ['snack' => 'Fehler beim Anlegen des Tickets.']);
            }
        }

        return $this->render('administrator/neuValidate.html.twig', ['errors' => $errors, 'form' => $form->createView(), 'title' => 'Melden Sie hier ein Problem oder machen Sie einen Verbesserungsvorschlag']);
    }
}
