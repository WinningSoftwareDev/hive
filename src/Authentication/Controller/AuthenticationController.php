<?php

declare(strict_types=1);

namespace App\Authentication\Controller;

use App\Authentication\DTO\PasswordResetDTO;
use App\Authentication\DTO\RequestPasswordResetDTO;
use App\Authentication\Entity\PasswordResetToken;
use App\Authentication\Entity\User;
use App\Authentication\Form\LoginForm;
use App\Authentication\Form\PasswordResetForm;
use App\Authentication\Form\RegistrationForm;
use App\Authentication\Form\RequestPasswordResetLinkForm;
use App\Authentication\Service\Email\PasswordResetService;
use App\Core\Controller\AbstractApplicationController;
use App\Core\Entity\OauthProvider;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class AuthenticationController extends AbstractApplicationController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PasswordResetService $passwordResetService,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route(path: '/authenticate', name: 'authenticate', methods: [Request::METHOD_GET])]
    public function authenticate(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_index');
        }

        $tab = $request->query->get('form') ?? 'false';

        if ($tab && !in_array($tab, ['LoginForm', 'RegistrationForm'])) {
            $tab = 'false';
        }

        $registrationFormCsrfToken = $this->createForm(RegistrationForm::class)->createView()->children['_token']->vars['value'];
        $loginFormCsrfToken = $this->createForm(LoginForm::class)->createView()->children['_token']->vars['value'];

        return $this->renderTemplate(
            'Authentication/authenticate',
            [
                'title' => 'Authenticate',
                'form' => $tab,
                'registrationToken' => $registrationFormCsrfToken,
                'loginToken' => $loginFormCsrfToken,
            ]
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/authenticate/password-reset', name: 'authenticate_request_password_reset', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function requestPasswordReset(Request $request): Response
    {
        $data = new RequestPasswordResetDTO();
        $form = $this->createForm(RequestPasswordResetLinkForm::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data->getEmail()]);

            if ($user instanceof User) {
                $existingTokens = $this->entityManager->getRepository(PasswordResetToken::class)->findBy(['user' => $user]);

                if (count($existingTokens)) {
                    foreach ($existingTokens as $token) {
                        $this->entityManager->remove($token);
                    }

                    $this->entityManager->flush();
                }

                $this->passwordResetService->sendResetEmail($user);
            }

            $this->addFlash('success', 'If your email exists, a reset link has been sent.');

            return $this->json([
                'success' => true,
                'errors' => [],
                'redirect' => $this->generateUrl('authenticate', ['form' => 'LoginForm']),
            ]);
        }

        return $this->renderTemplate(
            'Authentication/request-password-reset',
            [
                'title' => 'Password Reset',
                'csrfToken' => $form->createView()->children['_token']->vars['value'],
            ]
        );
    }

    #[Route(
        path: '/authenticate/password-reset/reset',
        name: 'authenticate_password_reset',
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    public function passwordReset(Request $request): Response
    {
        $token = (string) $request->query->get('token');
        $user = $this->passwordResetService->validateToken($token);

        if (!$user instanceof User) {
            $this->addFlash('error', 'Reset link is invalid or has expired.');

            return $this->redirectToRoute('app_index');
        }

        $data = new PasswordResetDTO();
        $form = $this->createForm(PasswordResetForm::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid() && $data->validate()) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $data->getPassword()));
                $this->entityManager->flush();
                $this->passwordResetService->consumeToken($token);
                $this->addFlash('success', 'Password successfully reset. You can now log in.');

                return $this->json([
                    'success' => true,
                    'errors' => [],
                    'redirect' => $this->generateUrl('authenticate', ['form' => 'LoginForm']),
                ]);
            }

            return $this->json([
                'success' => false,
                'errors' => [],
            ]);
        }

        return $this->renderTemplate(
            'Authentication/password-reset',
            [
                'title' => 'Password Reset',
                'token' => $token,
                'csrfToken' => $form->createView()->children['_token']->vars['value'],
            ]
        );
    }

    #[Route(path: '/authenticate/login', name: 'authenticate_login', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function login(): Response
    {
        return $this->redirectToRoute('authenticate', ['form' => 'LoginForm']);
    }

    #[Route(path: '/authenticate/logout', name: 'authenticate_logout', methods: [Request::METHOD_GET])]
    public function logout(): void
    {
        throw new \LogicException('Logout is handled by the firewall.');
    }

    #[Route(path: '/authenticate/current-user', name: 'authenticate_get_logged_in_user', methods: [Request::METHOD_GET])]
    public function getLoggedInUser(): Response
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            return $this->json(['email' => $user->getEmail(), 'roles' => $user->getRoles(), 'verified' => $user->isVerified()]);
        }

        return $this->json([
            'email' => '',
            'roles' => [],
            'verified' => false,
        ]);
    }

    #[Route(path: '/connect/{service}', name: 'connect_oauth_start', methods: [Request::METHOD_GET])]
    public function connect(string $service, ClientRegistry $clientRegistry): Response
    {
        if (!in_array(strtoupper($service), OauthProvider::PROVIDERS, true)) {
            throw $this->createNotFoundException();
        }

        $defaultUri = isset($_ENV['DEFAULT_URI']) && is_string($_ENV['DEFAULT_URI']) ? $_ENV['DEFAULT_URI'] : 'http://localhost';
        $redirectUrl = rtrim($defaultUri, '/') . '/connect/' . $service . '/check';

        return $clientRegistry->getClient($service)->redirect([], [
            'redirect_uri' => $redirectUrl,
        ]);
    }
}
