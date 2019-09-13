<?php

namespace App\Controller;

use App\Entity\Stadt;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StadtadminController extends AbstractController
{
    /**
     * @Route("/admin/stadtUser", name="admin_stadtadmin")
     */
    public function index(Request $request)
    {
        $city= $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        $user = $this->getDoctrine()->getRepository(User::class)->findBy(array('stadt'=>$city));
        dump($user);
        return $this->render('admin/user.html.twig', [
            'user' => $user,
        ]);
    }
}
