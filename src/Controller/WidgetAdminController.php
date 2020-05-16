<?php

namespace App\Controller;

use App\Entity\EmailResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;

class WidgetAdminController extends AbstractController
{
    /**
     * @Route("/admin/show/mailgun/stats", name="admin_mailgun_stats")
     */
    public function index(Request $request, TranslatorInterface $translator)
    {
        $alert = $this->getDoctrine()->getRepository(EmailResponse::class)->findBy(array('allert'=>true));
        $delivered = $this->getDoctrine()->getRepository(EmailResponse::class)->findBy(array('warning'=>false,'allert'=>false));
        $warning = $this->getDoctrine()->getRepository(EmailResponse::class)->findBy(array('warning'=>true));

    }


}
