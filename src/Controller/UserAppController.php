<?php

namespace App\Controller;

use App\Entity\Kind;
use App\Entity\User;
use App\Service\CheckinSchulkindservice;
use App\Service\ChildSearchService;
use App\Service\MailerService;
use App\Service\SchuljahrService;
use App\Service\UserConnectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserAppController extends AbstractController
{
    private $daymapper = array();

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
    public function __construct()
    {
        $this->daymapper = array(
            1 => 0,
            2 => 1,
            3 => 2,
            4 => 3,
            5 => 4,
            6 => 5,
            0 => 6,
        );
    }

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

        try {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(
                array(
                    'confirmationTokenApp' => $request->get('confirmationToken'),
                    'appDetectionToken' => $request->get('requestToken')));
            if (!$user) {
                return new JsonResponse(array('error' => true));
            }
            $user->setAppOS($request->get('os'));
            $user->setAppDevice($request->get("device"));
            $user->setAppImei($request->get('imei'));

        } catch (\Exception $e) {
            return new JsonResponse(array('error' => true));

        }
        return new JsonResponse($userConnectionService->generateCommunicationToken($user));

    }

    /**
     * @Route("/connect/user/save", name="connect_communication_save", methods={"POST"})
     */
    public function saveToken(UserConnectionService $userConnectionService, Request $request, MailerService $mailerService, TranslatorInterface $translator, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('appCommunicationToken' => $request->get('token')));
        return new JsonResponse($userConnectionService->saveSetting($user));


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
     * @Route("/get/user/kidsCheckin", name="connect_user_checkinKids", methods={"GET"})
     */
    public function userCheckinKids(CheckinSchulkindservice $checkinSchulkindservice, Request $request, MailerService $mailerService, TranslatorInterface $translator)
    {
        $user = null;
        if ($request->get('communicationToken')) {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array(
                    'appCommunicationToken' => $request->get('communicationToken')
                )
            );
        }

        if ($user) {
            $today = new \DateTime();
            $kinder = $checkinSchulkindservice->getAllKidsToday($user->getOrganisation(), $today, $user);
            $kinderSend = array();
            foreach ($kinder as $data) {
                $tmp = array(
                    'name' => $data->getNachname(),
                    'vorname' => $data->getVorname(),
                    'schule' => $data->getSchule()->getName(),
                    'erziehungsberechtigter' => $data->getEltern()->getVorname() . ' ' . $data->getEltern()->getName(),
                    'notfallkontakt' => $data->getEltern()->getNotfallkontakt(),
                    'klasse' => $data->getKlasse(),
                    'checkin' => true,
                    'schuleId' => $data->getSchule()->getId(),
                    'detail' => $this->makeHttps($this->generateUrl('connect_user_kidsDetails', array('id' => $data->getId()), UrlGenerator::ABSOLUTE_URL))

                );
                $kinderSend[] = $tmp;
            }
            return new JsonResponse(array(
                'error' => false,
                'number' => sizeof($kinderSend),
                'result' => $kinderSend));
        } else {
            return new JsonResponse(array('error' => true, 'errorText' => 'Fehler, bitte versuchen Sie es erneut oder melden Sie das Gerät bei SKIB an'));
        }

    }

    /**
     * @Route("/get/user/kidsHeuteDa", name="connect_user_kidsDa", methods={"GET"})
     */
    public function userKidsHeuteDa(SchuljahrService $schuljahrService, ChildSearchService $childSearchService, UserConnectionService $userConnectionService, Request $request, MailerService $mailerService, TranslatorInterface $translator, CheckinSchulkindservice $checkinSchulkindservice)
    {
        $user = null;
        if ($request->get('communicationToken')) {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array(
                    'appCommunicationToken' => $request->get('communicationToken')
                )
            );
        }

        if ($user) {
            $today = new \DateTime();
            $schuljahr = $schuljahrService->getSchuljahr($user->getStadt());
            $kinder = $childSearchService->searchChild(array('wochentag' => $this->daymapper[$today->format("w")]), $user->getOrganisation(), true, $user);
            $kinderCheckin = $checkinSchulkindservice->getAllKidsToday($user->getOrganisation(), $today, $user);
            $kinderSend = array();
            foreach ($kinder as $data) {
                $tmp = array(
                    'name' => $data->getNachname(),
                    'vorname' => $data->getVorname(),
                    'schule' => $data->getSchule()->getName(),
                    'erziehungsberechtigter' => $data->getEltern()->getVorname() . ' ' . $data->getEltern()->getName(),
                    'notfallkontakt' => $data->getEltern()->getNotfallkontakt(),
                    'klasse' => $data->getKlasse(),
                    'checkin' => in_array($data, $kinderCheckin),
                    'schuleId' => $data->getSchule()->getId(),
                    'detail' => $this->makeHttps($this->generateUrl('connect_user_kidsDetails', array('id' => $data->getId()), UrlGenerator::ABSOLUTE_URL))
                );
                $kinderSend[] = $tmp;
            }
            return new JsonResponse(array(
                'error' => false,
                'number' => sizeof($kinderSend),
                'result' => $kinderSend));
        } else {
            return new JsonResponse(array('error' => true, 'errorText' => 'Fehler, bitte versuchen Sie es erneut oder melden Sie das Gerät bei SKIB an'));
        }
    }

    /**
     * @Route("/get/user/kindDetail/{id}", name="connect_user_kidsDetails", methods={"GET"})
     */
    public function userKidsDetail($id, CheckinSchulkindservice $checkinSchulkindservice, UserConnectionService $userConnectionService, Request $request, MailerService $mailerService, TranslatorInterface $translator)
    {
        $user = null;
        if ($request->get('communicationToken')) {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array(
                    'appCommunicationToken' => $request->get('communicationToken')
                )
            );
        }
        $kind = $this->getDoctrine()->getRepository(Kind::class)->find($id);
        if ($user && in_array($kind->getSchule(), $user->getSchulen()->toArray())) {
            return new JsonResponse(array(
                    'vorname' => $kind->getVorname(),
                    'name' => $kind->getNachname(),
                    'allergie' => $kind->getAllergie(),
                    'notfallkontakt' => $kind->getEltern()->getNotfallkontakt(),
                    'notfallName' => $kind->getEltern()->getNotfallName(),
                    'elternVorname' => $kind->getEltern()->getVorname(),
                    'elterName' => $kind->getEltern()->getName(),
                    'abholberechtigter' => $kind->getEltern()->getAbholberechtigter(),
                    'geburtstag' => $kind->getGeburtstag()->format('d.m.Y'),
                    'medikamente' => $kind->getMedikamente(),
                    'schule' => $kind->getSchule()->getName(),
                    'boolean' => array(
                        array('name' => 'Glutenintollerant', 'value' => $kind->getGluten()),
                        array('name' => 'Laktoseintollerant', 'value' => $kind->getLaktose()),
                        array('name' => 'Isst kein Schweinefleisch', 'value' => $kind->getSchweinefleisch()),
                        array('name' => 'Ernährt sich vegetraisch', 'value' => $kind->getVegetarisch()),
                        array('name' => 'Kind darf alleine nach Hause', 'value' => $kind->getAlleineHause()),
                        array('name' => 'Darf an Ausflügen Teilnehmen', 'value' => $kind->getAusfluege()),
                        array('name' => 'Darf mit Sonnencreme eingecremt werden', 'value' => $kind->getSonnencreme()),
                        array('name' => 'Fotos dürfen veröffentlicht werden', 'value' => $kind->getFotos()),
                    ),
                    'bemerkung' => $kind->getBemerkung()
                )
            );
        } else {
            return new JsonResponse(array('error' => true, 'errorText' => "Kein Kind gefunden"));
        }
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
        $user->setAppOS(null);
        $user->setAppDevice(null);
        $user->setAppImei(null);
        $user->setAppSettingsSaved(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('connection_app_start');
    }

    private function makeHttps($input)
    {
        $out = str_replace('http', 'https',
            str_replace('https', 'http', $input));
        return $out;
    }
}
