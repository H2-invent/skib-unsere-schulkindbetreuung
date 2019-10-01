<?php
namespace App\Controller;
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 06.09.2019
 * Time: 12:21
 */

use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\StadtType;
use Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType;
use Beelab\Recaptcha2Bundle\Validator\Constraints\Recaptcha2;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
class workflowController  extends AbstractController
{
    /**
    * @Route("/{slug}/start",name="workflow_start",methods={"GET"})
     * @ParamConverter("stadt", options={"mapping"={"slug"="slug"}})
    */
    public function welcomeAction(Request $request, Stadt $stadt)
    {
        return $this->render('workflow/start.html.twig',array('stadt'=>$stadt));
    }


}