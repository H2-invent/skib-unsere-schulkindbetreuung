<?php


namespace App\Security;


use App\Entity\FosUser;
use App\Entity\MyUser;
use App\Entity\User;
use App\Service\IndexUserService;
use App\Service\ThemeService;
use App\Service\UserCreatorService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\KeycloakClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class KeycloakAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    private $clientRegistry;
    private $em;
    private $router;
    private $tokenStorage;
    private $userManager;
    private $paramterBag;
    private $logger;

    public function __construct(
        LoggerInterface        $logger,
        ParameterBagInterface  $parameterBag,
        TokenStorageInterface  $tokenStorage,
        ClientRegistry         $clientRegistry,
        EntityManagerInterface $em,
        RouterInterface        $router,)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->paramterBag = $parameterBag;
        $this->logger = $logger;

    }

    public function supports(Request $request): bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_keycloak_check';
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getauth0Client());
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('keycloak_main');
        $accessToken = $this->fetchAccessToken($client);
        $request->getSession()->set('id_token',$accessToken->getValues()['id_token']);
        $passport =  new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var KeycloakUser $keycloakUser */
                $keycloakUser = $client->fetchUserFromToken($accessToken);
                $id = $keycloakUser->getId();
                $existingUser = $this->em->getRepository(User::class)->findOneBy(array('keycloakId' => $id));
                $firstName = $keycloakUser->toArray()['given_name'];
                $lastName = $keycloakUser->toArray()['family_name'];
                $email = $keycloakUser->getEmail();
                if ($existingUser) {
                    $existingUser->setLastLogin(new \DateTime());
                    $existingUser->setEmail($email);
                    $existingUser->setVorname($firstName);
                    $existingUser->setNachname($lastName);
                    $this->em->persist($existingUser);
                    $this->em->flush();
                    if ($existingUser->getEnabled() == false){
                        echo "This user is disabled. Please contact your admin or support@h2-invent.com";
                        return null;
                    }
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
                    if ($existingUser->getEnabled() == false){
                        echo "This user is disabled. Please contact your admin or support@h2-invent.com";
                        return null;
                    }
                    return $existingUser;
                }

                // the user never logged in with this email adress
                $myUser = new User();
                $myUser->setEmail($email);
                $myUser->setCreatedAt(new \DateTime());
                $myUser->setAuth0Id(md5(uniqid()));
                $myUser->setVorname($firstName);
                $myUser->setNachname($lastName);
                $myUser->setKeycloakId($id);
                $myUser->setLastLogin(new \DateTime());
                $this->em->persist($myUser);
                $this->em->flush();
                return $myUser;

            })
        );
        $passport->setAttribute('id_token','null');
        $passport->setAttribute('scope', 'openid');

        return $passport;
    }



    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {

        // change "app_homepage" to some route in your app
        $targetUrl = $this->getTargetPath($request->getSession(), 'main');
        if (!$targetUrl) {
            $targetUrl = $this->router->generate('dashboard');
        }

        return new RedirectResponse($targetUrl);

    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->router->generate('welcome_landing'));
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



