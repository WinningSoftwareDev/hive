<?php

declare(strict_types=1);

namespace App\Authentication\Controller;

use App\Authentication\DTO\RegistrationDTO;
use App\Authentication\Entity\Role;
use App\Authentication\Entity\User;
use App\Authentication\Form\RegistrationForm;
use App\Authentication\Service\Email\EmailVerificationService;
use App\Core\Controller\AbstractApplicationController;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractApplicationController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * @throws TransportExceptionInterface|RandomException
     */
    #[Route(path: '/authenticate/register', name: 'authenticate_register', methods: [Request::METHOD_POST])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EmailVerificationService $verificationService
    ): JsonResponse {
        if ($this->getUser()) {
            $this->addFlash('info', 'You are already logged in.');

            return $this->json([
                'success' => false,
                'errors' => [],
            ]);
        }

        $data = new RegistrationDTO();
        $form = $this->createForm(RegistrationForm::class, $data);
        $form->handleRequest($request);
        $errors = $this->validator->validate($data);

        if ($form->isSubmitted()) {
            try {
                $this->entityManager->getRepository(User::class)->find(1);
            } catch (\Exception $e) {
                return $this->json([
                    'message' => '',
                    'success' => false,
                    'errors' => ['email' => ['It looks like your database isn\'t set up yet.']],
                ]);
            }

            if ($form->isValid()) {
                $existingUser = $this->entityManager
                    ->getRepository(User::class)
                    ->findOneBy(['email' => $data->getEmail()]);
                $data->setUserExists($existingUser instanceof User);

                if (!$data->validate() || count($errors)) {
                    return $this->json([
                        'success' => false,
                        'errors' => $this->getValidationErrors($data, $errors),
                    ]);
                }

                $roleUser = $this->entityManager->getRepository(Role::class)->findOneBy(['handle' => Role::ROLE_USER]);
                $user = User::create($data, $passwordHasher);

                if ($roleUser instanceof Role) {
                    $user->addRole($roleUser);
                }

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $verificationService->sendVerificationEmail($user);

                $this->addFlash('success', 'Account created. You can now log in.');

                return $this->json([
                    'success' => true,
                    'errors' => [],
                    'redirect' => $this->generateUrl('authenticate', ['form' => 'LoginForm']),
                ]);
            }
        }

        return $this->json([
            'success' => false,
            'errors' => $this->getValidationErrors($data, $errors),
        ]);
    }

    /**
     * @return array<string, array<int, string|\Stringable>>
     */
    private function getValidationErrors(RegistrationDTO $data, ConstraintViolationListInterface $errors): array
    {
        $validationErrors = [];

        foreach ($errors as $error) {
            $validationErrors[$error->getPropertyPath()][] = $error->getMessage();
        }

        if (!$data->passwordsMatch()) {
            $validationErrors['confirmPassword'][] = 'Passwords do not match.';
        }

        if ($data->userExists()) {
            $validationErrors['email'][] = 'A user with this email address already exists.';
        }

        return $validationErrors;
    }
}
