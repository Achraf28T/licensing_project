<?php

namespace App\Repository;

use App\Entity\License;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<License>
 */
class LicenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, License::class);
    }

    public function findByLicenseKeyHash(string $hash): ?License
    {
        return $this->findOneBy(['licenseKeyHash' => $hash]);
    }

    public function findActiveLicenses(): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.status = :status')
            ->andWhere('l.expiresAt IS NULL OR l.expiresAt > :now')
            ->setParameter('status', 'active')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function save(License $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(License $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}