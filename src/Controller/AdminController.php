<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\AdminUserType;
use App\Security\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    public function __construct(
        private UserManagerInterface $userManager,
    ) {
    }

    #[Route('/admin/user/edit/{user}', name: 'admin_user_edit')]
    public function userEdit(Request $request, User $user): Response
    {
        $errors = [];
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $defaultData = $form->getData();
                $this->userManager->updateUser($defaultData);

                return $this->redirectToRoute('admin_user_edit', ['user' => $user->getId()]);
            } catch (\Exception $e) {
                return $this->render(
                    'administrator/error.html.twig',
                    ['error' => $e->getMessage()]
                );
            }
        }

        return $this->render('administrator/neu.html.twig', [
            'title' => 'User bearbeiten',
            'form' => $form->createView(),
            'errors' => $errors,
        ]);
    }
}
