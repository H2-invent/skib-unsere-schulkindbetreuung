<?php

namespace App\Controller;

use App\Form\Type\SupportForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use it\thecsea\osticket_php_client\OsticketPhpClient;
use it\thecsea\osticket_php_client\OsticketPhpClientException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SupportController extends AbstractController
{
    /**
     * @Route("/login/support/new", name="support")
     */
    public function index(Request $request, ParameterBagInterface $parameterBag)
    {
        // todo create form for support
        $arr = array(
            'name' => $this->getUser()->getVorname() . '-' . $this->getUser()->getNachname(),
            'email' => $this->getUser()->getEmail(),
            'phone' => '',
            'subject' => 'Betreff',
            'message' => 'Nachricht',
            'datenschutz' => true,
            'topicId' => '17',
            'user' => $this->getUser()->getUsername(),
        );
        $form = $this->createForm(SupportForm::class, $arr);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $arr = $form->getData();
            dump($arr);
            $support = new OsticketPhpClient($parameterBag->get('osTicketUrl'), $parameterBag->get('osTicketApi'));
             try{
            $support->request('api/tickets.json', $arr);

            return $this->redirectToRoute('dashboard',array('snack'=>"Es wurde erfolgreich ein Ticket angelegt"));
             }catch(OsticketPhpClientException $e){
                 return $this->redirectToRoute('dashboard',array('snack'=>"Fehler beim Anlegen des Tickets."));

             }
        }
        return $this->render('administrator/neuValidate.html.twig', array('errors' => $errors, 'form' => $form->createView(), 'title' => "Melden Sie hier ein Problem oder machen Sie einen Verbesserungsvorschlag"));

    }
}
