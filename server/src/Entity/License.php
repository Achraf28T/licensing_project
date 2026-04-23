<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\LicenseRepository;

#[ORM\Entity(repositoryClass: LicenseRepository::class)]
#[ORM\Table(name: 'licenses')]
class License
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 36)]
    private string $productId;

    #[ORM\Column(type: 'string', length: 36)]
    private string $customerId;

    #[ORM\Column(type: 'string', length: 128, unique: true)]
    private string $licenseKeyHash;

    #[ORM\Column(type: 'string', length: 32)]
    private string $plan;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status;

    #[ORM\Column(type: 'integer')]
    private int $maxActivations;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $startsAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $expiresAt;

    #[ORM\Column(type: 'integer')]
    private int $gracePeriodHours;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updatedAt;

    // Getters and setters...
    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getProductId(): string { return $this->productId; }
    public function setProductId(string $productId): void { $this->productId = $productId; }

    public function getCustomerId(): string { return $this->customerId; }
    public function setCustomerId(string $customerId): void { $this->customerId = $customerId; }

    public function getLicenseKeyHash(): string { return $this->licenseKeyHash; }
    public function setLicenseKeyHash(string $licenseKeyHash): void { $this->licenseKeyHash = $licenseKeyHash; }

    public function getPlan(): string { return $this->plan; }
    public function setPlan(string $plan): void { $this->plan = $plan; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getMaxActivations(): int { return $this->maxActivations; }
    public function setMaxActivations(int $maxActivations): void { $this->maxActivations = $maxActivations; }

    public function getStartsAt(): \DateTime { return $this->startsAt; }
    public function setStartsAt(\DateTime $startsAt): void { $this->startsAt = $startsAt; }

    public function getExpiresAt(): ?\DateTime { return $this->expiresAt; }
    public function setExpiresAt(?\DateTime $expiresAt): void { $this->expiresAt = $expiresAt; }

    public function getGracePeriodHours(): int { return $this->gracePeriodHours; }
    public function setGracePeriodHours(int $gracePeriodHours): void { $this->gracePeriodHours = $gracePeriodHours; }

    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function setCreatedAt(\DateTime $createdAt): void { $this->createdAt = $createdAt; }

    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function setUpdatedAt(\DateTime $updatedAt): void { $this->updatedAt = $updatedAt; }
}