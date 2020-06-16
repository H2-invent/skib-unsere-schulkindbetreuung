<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\CheckinSchulkindservice;
use App\Service\MailerService;
use App\Service\UserConnectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserAppController extends AbstractController
{
    /*
    * Workflow für den register Vorang der App:
     * Der Uuser üffnet die Seite /login/connect/user und scannt den Code mit der App
     * Die App macht einen Request auf die URL welche im QR Code lesbar ist. Der Token ändert sich mit jedem neuen refresh der Seite
     * und ist somit nur einmal gültig
     * Der Request genereiert einen ConfirmationToken welcher per EMail verschickt wird und einen Identitfication Code welcher per Json an die App gesendet wird.
     * Der Request gibt im Json ebenfalls noch die URL mit über welche sich die App denn Kommunication Token holen kann
     * somit ist die Response:
     * per EMail:
     *  Confirmation Code
     * per Json:
     *  URL für die Kommunikation
     *  Identification Code
     * Der USer gibt seinen Email code ein, und in Combi mit dem IdentificationCode kann sich die App den Communication Token holen
     *  URL (POST)
     *  requestToken:Identification TOken
     *  confirmationToken: Email Token
     * Als Response auf diesen Request bekommt die App nun die URL für die Information des Users und den Communication Token.
     * Die URL enthält dann weitere URLs mit Informationen sowie die Infos zu dem USer
    *
    */

    /**
     * @Route("/login/connect/user", name="connection_app_start", methods={"GET"})
     */
    public function generateTOken(Request $request, TranslatorInterface $translator, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $user = $this->getUser();

        $user->setAppToken(md5(uniqid()));
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->render('user_app/index.html.twig', array('user' => $user));
    }

    /**
     * @Route("/connect/user/confirmation/{appToken}", name="connect_User", methods={"GET"})
     */
    public function confirmationToken(UserConnectionService $userConnectionService, MailerService $mailerService, TranslatorInterface $translator, $appToken, CheckinSchulkindservice $checkinSchulkindservice)
    {
        try {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('appToken' => $appToken));
        } catch (\Exception $e) {
            return new JsonResponse(array('error' => true));
        }
        return new JsonResponse($userConnectionService->generateConfirmationToken($user));
    }

    /**
     * @Route("/connect/user/communicationToken", name="connect_communication_token", methods={"POST"})
     */
    public function communicationToken(UserConnectionService $userConnectionService, Request $request, MailerService $mailerService, TranslatorInterface $translator, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $imei = '';
        $os = '';
        $device='';
        try {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(
                array(
                    'confirmationTokenApp' => $request->get('confirmationToken'),
                    'appDetectionToken' => $request->get('requestToken')));
            $user->setAppOS($request->get('os'));
            $user->setAppDevice($request->get("device"));
            $user->setAppImei($request->get('imei'));

        } catch (\Exception $e) {
            return new JsonResponse(array('error' => true));

        }
        return new JsonResponse($userConnectionService->generateCommunicationToken($user));

    }

    /**
     * @Route("/get/user/information", name="connect_user_information", methods={"POST"})
     */
    public function userInformation(UserConnectionService $userConnectionService, Request $request, MailerService $mailerService, TranslatorInterface $translator, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array(
                'appCommunicationToken' => $request->get('communicationToken')
            )
        );
        return new JsonResponse($userConnectionService->userInfo($user));

    }
    /**
     * @Route("/login/disconnect/user", name="connection_app_disconnect", methods={"GET"})
     */
    public function deleteConnection(Request $request, TranslatorInterface $translator, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $user = $this->getUser();
        $user->setAppToken(null);
        $user->setAppCommunicationToken(null);
        $user->setAppDetectionToken(null);
        $user->setConfirmationTokenApp(null);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('login_apptoken');

    }
}
