<?php

namespace App\Controller;

use App\Entity\KindFerienblock;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class FerienCheckinController extends AbstractController
{
    /**
     * @Route("/org_ferien/checkin/{checkinID}", name="ferien_checkin")
     */
    public function index(TranslatorInterface $translator, $checkinID)
    {
        $kindFerienblock = $this->getDoctrine()->getRepository(KindFerienblock::class)->findOneBy(array('checkinID' => $checkinID));
        $today = new \DateTime('today');

        $error = false;
        $errorText = $translator->trans('Kind erfolgreich eingecheckt');

        if ($kindFerienblock == null) {
            $error = true;
            $errorText = $translator->trans('Ticket ist falsch oder ungültig');

            return $this->render('ferien_checkin/index.html.twig', [
                'controller_name' => 'FerienCheckinController',
                'error' => $error,
                'errorText' => $errorText,
            ]);
        }

        $startdateFerienblock = $kindFerienblock->getFerienblock()->getStartDate();
        $enddateFerienblock = $kindFerienblock->getFerienblock()->getEndDate();

        if ($today < $startdateFerienblock && $today > $enddateFerienblock) {
            $error = true;
            $errorText = $translator->trans('Dieses Ticket ist an einem anderen Tag gültig');
        }

        $checkinDate = $today->format('Y-m-d');
        $status = $kindFerienblock->getCheckinStatus() != null ? $kindFerienblock->getCheckinStatus() : array();

        if (in_array($checkinDate, $status)) {
            $error = true;
            $errorText = $translator->trans('Kind für den heutigen Tag bereits eingecheckt');
        }

        if ($kindFerienblock->getState() >= 20) {
            $error = true;
            $errorText = $translator->trans('Das Ticket wurde Storniert und kann nicht eingcheckt werden');
        }
        if ($error === false) {
            $status[] = $checkinDate;
            $kindFerienblock->setCheckinStatus($status);

            $em = $this->getDoctrine()->getManager();
            $em->persist($kindFerienblock);
            $em->flush();
        }
        return $this->render('ferien_checkin/index.html.twig', [
            'controller_name' => 'FerienCheckinController',
            'error' => $error,
            'block' => $kindFerienblock,
            'kind' => $kindFerienblock->getKind(),
            'errorText' => $errorText,
        ]);
    }
}
