<?php

declare(strict_types=1);

namespace App\Authentication\Controller;

use App\Authentication\Entity\EmailVerificationToken;
use App\Authentication\Entity\Role;
use App\Authentication\Entity\User;
use App\Authentication\Repository\EmailVerificationTokenRepository;
use App\Authentication\Service\Email\EmailVerificationService;
use App\Core\Controller\AbstractApplicationController;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EmailVerificationController extends AbstractApplicationController
{
    public function __construct(
        private readonly EmailVerificationService $emailVerificationService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/authenticate/verify', name: 'authenticate_verify_email', methods: [Request::METHOD_GET])]
    public function verify(Request $request): Response
    {
        $token = $request->query->get('token');

        if (!is_string($token)) {
            $this->addFlash('error', 'Invalid verification link.');

            return $this->redirectToRoute('authenticate_login');
        }

        $user = $this->emailVerificationService->verifyToken($token);

        if (!$user instanceof User) {
            $this->addFlash('error', 'Verification link is invalid or expired.');

            return $this->redirectToRoute('authenticate_login');
        }

        $this->addFlash('success', 'Your account is now verified. You can log in.');

        return $this->redirectToRoute('authenticate_login');
    }

    /**
     * @throws \DateMalformedStringException|RandomException|TransportExceptionInterface
     */
    #[IsGranted(Role::ROLE_USER)]
    #[Route(path: '/authenticate/resend-verification-email', name: 'authenticate_resend_verification_email', methods: [Request::METHOD_POST])]
    public function resendVerificationEmail(Request $request): Response
    {
        $csrfToken = $request->toArray()['_token'] ?? null;

        if (!is_string($csrfToken) || !$this->isCsrfTokenValid('resend_verification_email', $csrfToken)) {
            return new JsonResponse([
                'message' => 'Something went wrong.',
                'success' => false,
            ], 403);
        }

        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'message' => 'User not found.',
                'success' => false,
            ], 403);
        }

        /** @var EmailVerificationTokenRepository $repository */
        $repository = $this->entityManager->getRepository(EmailVerificationToken::class);
        $existingToken = $repository->findRecentByUser($user);

        if ($existingToken instanceof EmailVerificationToken) {
            return new JsonResponse([
                'message' => 'Please wait before resending verification email.',
                'success' => false,
            ], 403);
        }

        $this->emailVerificationService->sendVerificationEmail($user);

        return new JsonResponse(['success' => true], 200);
    }
}
