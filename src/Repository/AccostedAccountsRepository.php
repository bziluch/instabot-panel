<?php

namespace App\Repository;

use App\Entity\AccostedAccounts;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccostedAccounts>
 */
class AccostedAccountsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccostedAccounts::class);
    }

    private const SUCCESS_STATUSES = [
        AccostedAccounts::STATUS_RELATIONS_SUCCESS,
        AccostedAccounts::STATUS_LIKES_SUCCESS,
        AccostedAccounts::STATUS_SUCCESS,
    ];

    /**
     * Liczba zaczepionych kont danego użytkownika w konkretnym dniu.
     */
    public function countByDay(User $user, \DateTimeInterface $date): int
    {
        $start = (clone $date)->setTime(0, 0, 0);
        $end = (clone $date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->join('a.schedule', 's')
            ->join('s.igAccount', 'ia')
            ->where('ia.User = :user')
            ->andWhere('a.status IN (:statuses)')
            ->andWhere('s.date BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('statuses', self::SUCCESS_STATUSES)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Liczba zaczepionych kont danego użytkownika w konkretnym miesiącu.
     */
    public function countByMonth(User $user, \DateTimeInterface $month): int
    {
        $start = (clone $month)->modify('first day of this month')->setTime(0, 0, 0);
        $end = (clone $month)->modify('last day of this month')->setTime(23, 59, 59);

        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->join('a.schedule', 's')
            ->join('s.igAccount', 'ia')
            ->where('ia.User = :user')
            ->andWhere('a.status IN (:statuses)')
            ->andWhere('s.date BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('statuses', self::SUCCESS_STATUSES)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Łączna liczba zaczepionych kont danego użytkownika.
     */
    public function countTotal(User $user): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->join('a.schedule', 's')
            ->join('s.igAccount', 'ia')
            ->where('ia.User = :user')
            ->andWhere('a.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', self::SUCCESS_STATUSES)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
