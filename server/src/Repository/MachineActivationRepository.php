<?php

namespace App\Repository;

use App\Entity\MachineActivation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MachineActivation>
 */
class MachineActivationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MachineActivation::class);
    }

    public function findByLicenseAndFingerprint(string $licenseId, string $fingerprint): ?MachineActivation
    {
        return $this->findOneBy(['licenseId' => $licenseId, 'machineFingerprint' => $fingerprint]);
    }

    public function countActivationsForLicense(string $licenseId): int
    {
        return $this->count(['licenseId' => $licenseId, 'status' => 'active']);
    }

    public function save(MachineActivation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MachineActivation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findExpiredActivations(): array
    {
        return $this->createQueryBuilder('ma')
            ->where('ma.expiresAt IS NOT NULL')
            ->andWhere('ma.expiresAt < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }
}