<?php

namespace App\Repository;

use App\Entity\ActivityReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActivityReport>
 */
class ActivityReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivityReport::class);
    }

    public function findByLicense(string $licenseId): array
    {
        return $this->findBy(['licenseId' => $licenseId], ['reportedAt' => 'DESC']);
    }

    public function save(ActivityReport $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ActivityReport $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findRecentActivities(int $hours = 24): array
    {
        $since = new \DateTime();
        $since->modify("-{$hours} hours");

        return $this->createQueryBuilder('ar')
            ->where('ar.reportedAt > :since')
            ->setParameter('since', $since)
            ->orderBy('ar.reportedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}