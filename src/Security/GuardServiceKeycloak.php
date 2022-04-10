<?php


namespace App\Security;


use App\Entity\FosUser;
use App\Entity\MyUser;
use App\Entity\User;
use App\Entity\UserBase;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\Auth0Client;
use KnpU\OAuth2ClientBundle\Client\Provider\GithubClient;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use KnpU\OAuth2ClientBundle\Client\Provider\KeycloakClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class GuardServiceKeycloak extends SocialAuthenticator
{
    use TargetPathTrait;
    private $clientRegistry;
    private $em;
    private $router;
    private $tokenStorage;
    private $userManager;

    public function __construct(TokenStorageInterface $tokenStorage, ClientRegistry $clientRegistry, EntityManagerInterface $em, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    public function supports(Request $request)
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_keycloak_check';
    }

    public function getCredentials(Request $request)
    {

        return $this->fetchAccessToken($this->getauth0Client());
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {

        /** @var KeycloakUser $keycloakUser */
        $keycloakUser = $this->getauth0Client()->fetchUserFromToken($credentials);
        $email = $keycloakUser->getEmail();
        $id = $keycloakUser->getId();
        $firstName = $keycloakUser->toArray()['given_name'];
        $lastName = $keycloakUser->toArray()['family_name'];
        // 1) have they logged in with keycloak befor then login the user
        $existingUser = $this->em->getRepository(User::class)->findOneBy(array('keycloakId' => $id));
        if ($existingUser) {
            $existingUser->setLastLogin(new \DateTime());
            $existingUser->setEmail($email);
            $existingUser->setVorname($firstName);
            $existingUser->setNachname($lastName);
            $this->em->persist($existingUser);
            $this->em->flush();
            return $existingUser;
        }

        // 1) it is an old USer from FOS USer time never loged in from keycloak
        $existingUser = null;
        $existingUser = $this->em->getRepository(User::class)->findOneBy(array('email' => $email));
        if ($existingUser) {
            $existingUser->setKeycloakId($id);
            $existingUser->setLastLogin(new \DateTime());
            $existingUser->setEmail($email);
            $existingUser->setVorname($firstName);
            $existingUser->setNachname($lastName);
            $this->em->persist($existingUser);
            $this->em->flush();
            return $existingUser;
        }

        // the user never logged in with this email adress
        $user = new User();
        $user->setEmail($email);

        $myUser = new User();
        $myUser->setCreatedAt(new \DateTime());
        $myUser->setAuth0Id(md5(uniqid()));
        $myUser->setVorname($firstName);
        $myUser->setNachname($lastName);
        $myUser->setKeycloakId($id);
        $myUser->setLastLogin(new \DateTime());

        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    /**
     * @return KeycloakClient
     */
    private function getauth0Client()
    {
        return $this->clientRegistry
            ->getClient('keycloak_main');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {

        // change "app_homepage" to some route in your app
        $targetUrl = $this->getTargetPath($request->getSession(), 'main');
        if (!$targetUrl) {
            $targetUrl = $this->router->generate('dashboard');
        }

        return new RedirectResponse($targetUrl);

    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $targetUrl = $this->router->generate('login_keycloak');
        return new RedirectResponse($targetUrl);
    }

}



