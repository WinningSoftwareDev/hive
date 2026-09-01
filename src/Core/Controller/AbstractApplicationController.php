<?php

declare(strict_types=1);

namespace App\Core\Controller;

use App\Authentication\Entity\User;
use LatteHelper\Controller\AbstractLatteController;

class AbstractApplicationController extends AbstractLatteController
{
    protected string $templateDir = '/templates';

    protected function getUser(): ?User
    {
        $user = parent::getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
