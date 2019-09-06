<?php
namespace App\Controller;
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 06.09.2019
 * Time: 12:21
 */

use App\Entity\Schule;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
class workflowController  extends AbstractController
{
    /**
    * @Route("/{stadt}/start",name="workflow_start",methods={"GET"})
    */
    public function welcomeAction(Request $request, $stadt)
    {

       $city = $this->getDoctrine()->getRepository(Schule::class)->findOneBy(array('slug'=>$stadt));
       dump($city);
        return $this->render('workflow/start.html.twig',array('stadt'=>$city));
    }


}