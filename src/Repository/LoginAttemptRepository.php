<?php

namespace App\Repository;

use App\Entity\LoginAttempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LoginAttempt|null find($id, $lockMode = null, $lockVersion = null)
 * @method LoginAttempt|null findOneBy(array $criteria, array $orderBy = null)
 * @method LoginAttempt[]    findAll()
 * @method LoginAttempt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoginAttemptRepository extends ServiceEntityRepository
{
    public const DELAY_IN_MINUTES = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginAttempt::class);
    }

    public function countRecentLoginAttempts(string $ipAddress): int
    {
        $timeAgo = new \DateTimeImmutable(
            sprintf(
                '-%d minutes',
                self::DELAY_IN_MINUTES
            ),
            new \DateTimeZone('Europe/Paris')
        );

        return $this->createQueryBuilder('la')
            ->select('COUNT(la)')
            ->where('la.date >= :date')
            ->andWhere('la.ipAddress = :ipAddress')
            ->getQuery()
            ->setParameters([
                'date'      => $timeAgo,
                'ipAddress' => $ipAddress,
            ])
            ->getSingleScalarResult()
            ;
    }
}
