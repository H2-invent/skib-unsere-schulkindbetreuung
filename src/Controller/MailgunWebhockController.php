<?php

namespace App\Controller;

use App\Entity\EmailResponse;
use App\Service\GroupORMService;
use App\Service\MailgunWebhockService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MailgunWebhockController extends AbstractController
{
    public function __construct(private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {
    }
    /**
     * @Route("/mailgun/webhock/success", name="mailgun_webhock_delived",methods={"POST"})
     */
    public function success(Request $request,MailgunWebhockService $mailgunWebhockService)
    {
        $res = true;
        $parametersAsArray = [];
        try {
            if ($content = $request->getContent()) {
                $parametersAsArray = json_decode($content, true);
            }
            $res =$mailgunWebhockService->saveSuccess($parametersAsArray);
        }catch(\Exception $e) {
            return new JsonResponse(array('error'=>true));
        }
        return new JsonResponse(array('error'=>$res));
    }
    /**
     * @Route("/mailgun/webhock/fail", name="mailgun_webhock_fail",methods={"POST"})
     */
    public function fail(Request $request,MailgunWebhockService $mailgunWebhockService)
    {
        $res = true;
        $parametersAsArray = [];
        try {
            if ($content = $request->getContent()) {
                $parametersAsArray = json_decode($content, true);
            }
          $res= $mailgunWebhockService->saveFailure($parametersAsArray);
        }catch(\Exception $e) {
            return new JsonResponse(array('error'=>true));
        }
        return new JsonResponse(array('error'=>$res));
    }
    /**
     * @Route("/admin/mailgun/index", name="admin_mailgun_index",methods={"GET"})
     */
    public function index(Request $request,MailgunWebhockService $mailgunWebhockService,GroupORMService $groupORMService)
    {
        $mails = $this->managerRegistry->getRepository(EmailResponse::class)->findBy(array(),array('createdAt'=>'desc'));
        $mailsChart =array();

        $mailsChart[]=array('label'=>'Erfolgreich','data'=>$groupORMService->groupData($this->managerRegistry->getRepository(EmailResponse::class)->findBy(array('allert'=>false,'warning'=>false),array('createdAt'=>'desc'))));
        $mailsChart[]=array('label'=>'Permament','data'=>$groupORMService->groupData($this->managerRegistry->getRepository(EmailResponse::class)->findBy(array('allert'=>true),array('createdAt'=>'desc'))));
        $mailsChart[]=array('label'=>'Temporary','data'=> $groupORMService->groupData($this->managerRegistry->getRepository(EmailResponse::class)->findBy(array('warning'=>true),array('createdAt'=>'desc'))));
        return $this->render('mailgun_webhock/index.html.twig',array('emails'=>$mails,'title'=>'Mail-Übersicht','chart'=>$mailsChart));
    }
    /**
     * @Route("/admin/mailgun/history", name="admin_mailgun_history",methods={"GET"})
     */
    public function hist(Request $request,MailgunWebhockService $mailgunWebhockService)
    {
        $email = $this->managerRegistry->getRepository(EmailResponse::class)->findBy(array('messageId'=>$request->get('message-id')),array('createdAt'=>'desc'));
        return $this->render('mailgun_webhock/index.html.twig',array('emails'=>$email,'title'=>$request->get('message-id')));
    }
    /**
     * @Route("/admin/mailgun/detail", name="admin_mailgun_detail",methods={"GET"})
     */
    public function detail(Request $request,MailgunWebhockService $mailgunWebhockService)
    {
        $email = $this->managerRegistry->getRepository(EmailResponse::class)->find($request->get('message-id'));
        $json = json_decode($email->getPayload());

        return $this->render('mailgun_webhock/detail.html.twig',array('json'=>$json,'emails'=>$email,'title'=>'Detail'));

    }
}
