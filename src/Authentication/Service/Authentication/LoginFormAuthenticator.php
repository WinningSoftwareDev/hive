<?php

declare(strict_types=1);

namespace App\Authentication\Service\Authentication;

use App\Authentication\DTO\LoginDTO;
use App\Authentication\Entity\User;
use App\Authentication\Form\LoginForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly FormFactoryInterface $formFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        try {
            $this->entityManager->getRepository(User::class)->find(1);
        } catch (\Exception $e) {
            throw new CustomUserMessageAuthenticationException('It looks like your database isn\'t set up yet.');
        }

        $data = new LoginDTO();
        $loginForm = $this->formFactory->create(LoginForm::class, $data);

        $loginForm->handleRequest($request);

        if (!$loginForm->isSubmitted() || !$loginForm->isValid()) {
            throw new CustomUserMessageAuthenticationException('Invalid credentials');
        }

        $loginFormData = $request->request->all()['login_form'];
        $csrfToken = is_array($loginFormData) ? $loginFormData['_token'] : null;

        if (!is_string($csrfToken)) {
            throw new CustomUserMessageAuthenticationException('Invalid CSRF token');
        }

        return new Passport(
            new UserBadge($data->getEmail()),
            new PasswordCredentials($data->getPassword()),
            [
                new CsrfTokenBadge('login_form', $csrfToken),
            ]
        );
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('authenticate_login');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $flashBag = $request->getSession()->getBag('flashes');

        if ($flashBag instanceof FlashBag) {
            $flashBag->add('success', 'You have been logged in successfully.');
        }

        return new JsonResponse([
            'success' => true,
            'errors' => [],
            'redirect' => $this->router->generate('app_index'),
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new JsonResponse([
            'success' => false,
            'errors' => [
                'email' => ['Could not log you in with these credentials'],
            ],
        ]);
    }
}
