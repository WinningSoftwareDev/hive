<?php

declare(strict_types=1);

namespace App\Authentication\Repository;

use App\Authentication\Entity\EmailVerificationToken;
use App\Authentication\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<EmailVerificationToken>
 */
class EmailVerificationTokenRepository extends EntityRepository
{
    /**
     * @throws \DateMalformedStringException
     */
    public function findRecentByUser(User $user): ?EmailVerificationToken
    {
        $limit = new \DateTime()->modify('-5 minutes')->format('Y-m-d H:i:s');
        $token = $this->createQueryBuilder('vt')
            ->select()
            ->where('vt.user = :user')
            ->andWhere('vt.createdAt >= :created')
            ->andWhere('vt.verifiedAt IS NULL')
            ->andWhere('vt.expiresAt >= :currentDate')
            ->setParameter('user', $user)
            ->setParameter('created', $limit)
            ->setParameter('currentDate', new \DateTime()->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getOneOrNullResult();

        if (!$token instanceof EmailVerificationToken) {
            return null;
        }

        return $token;
    }
}
