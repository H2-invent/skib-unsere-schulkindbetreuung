<?php

namespace App\Controller;

use App\Entity\Stadt;
use App\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EmployeeController extends AbstractController
{
    private $manager;

    public function __construct(UserManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/city/admin/mitarbeiter/stadt", name="employee")
     */
    public function index(Request $request)
    {
        $city= $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        $user =
        $user = $this->getDoctrine()->getRepository(User::class)->findBy(array('stadt'=>$city));
        dump($user);
        return $this->render('employee/user.html.twig', [
            'user' => $user,
            'city'=>$city
        ]);
    }
}
