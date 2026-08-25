<?php

declare(strict_types=1);

namespace App\Authentication\Service\Authentication;

use App\Authentication\Entity\User;
use App\Authentication\Entity\UserOauth;
use App\Core\Entity\OauthProvider;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class OAuthAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private ClientRegistry $clientRegistry,
    ) {
    }

    public function supports(Request $request): bool
    {
        $route = $request->attributes->get('_route');

        return $route === 'connect_' . OauthProvider::GITHUB . '_check'
            || $route === 'connect_' . OauthProvider::GOOGLE . '_check';
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $route = $request->attributes->get('_route');
        $service = match ($route) {
            'connect_' . OauthProvider::GITHUB . '_check' => OauthProvider::GITHUB,
            'connect_' . OauthProvider::GOOGLE . '_check' => OauthProvider::GOOGLE,
            default => throw new AuthenticationException('Unknown OAuth service.'),
        };

        $provider = $this->entityManager->getRepository(OauthProvider::class)->findOneBy(['handle' => $service]);

        if (!$provider instanceof OauthProvider) {
            throw new AuthenticationException('OAuth provider not found.');
        }

        $defaultUri = isset($_ENV['DEFAULT_URI']) && is_string($_ENV['DEFAULT_URI']) ? $_ENV['DEFAULT_URI'] : 'http://localhost';
        $redirectUrl = rtrim($defaultUri, '/') . '/connect/' . $service . '/check';

        $client = $this->clientRegistry->getClient($service);
        $accessToken = $this->fetchAccessToken($client, ['redirect_uri' => $redirectUrl]);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($client, $accessToken, $service, $provider) {
                $oauthUser = $client->fetchUserFromToken($accessToken);
                $oauthUserData = $oauthUser->toArray();
                $email = isset($oauthUserData['email']) && is_string($oauthUserData['email']) ? $oauthUserData['email'] : null;
                $oauthId = $oauthUser->getId();

                if ($service === OauthProvider::GITHUB && is_string($email) && str_ends_with($email, '@users.noreply.github.com')) {
                    $realEmail = $this->getGitHubEmail($accessToken);

                    if (is_string($realEmail)) {
                        $email = $realEmail;
                    }
                }

                if ($email === null) {
                    throw new AuthenticationException('Email not provided by OAuth provider.');
                }

                if (!is_string($oauthId) && !is_int($oauthId)) {
                    throw new AuthenticationException('Invalid OAuth user ID.');
                }

                $oauthIdStr = (string) $oauthId;

                $userOauth = $this->entityManager->getRepository(UserOauth::class)->findOneBy([
                    'provider' => $provider,
                    'oauthProviderId' => $oauthIdStr,
                ]);

                if ($userOauth instanceof UserOauth) {
                    return $userOauth->getUser();
                }

                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if (!$user instanceof User) {
                    $user = new User();

                    $user->setEmail($email);
                    $user->verify();

                    $this->entityManager->persist($user);
                }

                $user->linkOauth($provider, $oauthIdStr);
                $this->entityManager->flush();

                return $user;
            }),
        );
    }

    private function getGitHubEmail(AccessToken $accessToken): ?string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer {$accessToken->getToken()}\r\nAccept: application/vnd.github.v3+json\r\nUser-Agent: Symfony-App\r\n",
                'timeout' => 5,
            ],
        ]);

        $response = @file_get_contents('https://api.github.com/user/emails', false, $context);

        if ($response === false) {
            return null;
        }

        $emails = json_decode($response, true);

        if (!is_array($emails)) {
            return null;
        }

        foreach ($emails as $emailData) {
            if (is_array($emailData) && isset($emailData['primary'], $emailData['verified']) && $emailData['primary'] === true && $emailData['verified'] === true) {
                return is_string($emailData['email']) ? $emailData['email'] : null;
            }
        }

        return null;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $flashBag = $request->getSession()->getBag('flashes');

        if ($flashBag instanceof FlashBag) {
            $flashBag->add('success', 'You have been logged in successfully.');
        }

        return new RedirectResponse($this->router->generate('app_index'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $flashBag = $request->getSession()->getBag('flashes');

        if ($flashBag instanceof FlashBag) {
            $flashBag->add('error', 'Authentication failed. Please try again.');
        }

        return new RedirectResponse($this->router->generate('authenticate'));
    }
}
