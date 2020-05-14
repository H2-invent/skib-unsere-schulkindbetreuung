<?php

namespace App\Controller;

use App\Service\MailgunWebhockService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MailgunWebhockController extends AbstractController
{
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

}
