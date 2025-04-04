<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\Auth0Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LoginControllerKeycloak extends AbstractController
{
    /**
     * @Route("/login", name="login_keycloak")
     */
    public function index(ClientRegistry $clientRegistry): Response
    {
      return $clientRegistry->getClient('keycloak_main')->redirect(['email','openid','profile']);
    }


    public function check(ClientRegistry $clientRegistry, Request $request)
    {
        //return $this->redirectToRoute('dashboard');
    }


    /**
     * @Route("/login/keycloak_edit", name="connect_keycloak_edit")
     */
    public function edit(ClientRegistry $clientRegistry, Request $request)
    {
        $url = $this->getParameter('KEYCLOAK_URL').'/realms/'.$this->getParameter('KEYCLOAK_REALM').'/account/#/personal-info';
        return $this->redirect($url);
    }
    /**
     * @Route("/login/keycloak_password", name="connect_keycloak_password")
     */
    public function password(ClientRegistry $clientRegistry, Request $request)
    {
        $url = $this->getParameter('KEYCLOAK_URL').'/realms/'.$this->getParameter('KEYCLOAK_REALM').'/account/#/security/signingin';
        return $this->redirect($url);
    }
}
