<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use it\thecsea\osticket_php_client\OsticketPhpClient;
use it\thecsea\osticket_php_client\OsticketPhpClientException;

class SupportController extends AbstractController
{
    /**
     * @Route("/login/support/new", name="support")
     */
    public function index(Request $request, ParameterBagInterface $parameterBag)
    {
     // todo create form for support


        $support = new OsticketPhpClient($parameterBag->get('osTicketUrl'), $parameterBag->get('osTicketApi'));
        try{
            $arr = array(
                'name'=>'test',
                'email'=>'info@h2-invent.com',
                'phone'=>'0912321',
                'subject'=>'subject',
                'message'=>'message',
                'datenschutz'=>true,
                'topicId'=>'17',
                'user'=>'test',
            );
         $support->request('api/tickets.json',$arr);
       }catch(OsticketPhpClientException $e){
            print $e->getMessage();
        }

       return 0;
    }
}
