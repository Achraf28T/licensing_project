<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MachineActivationRepository;

#[ORM\Entity(repositoryClass: MachineActivationRepository::class)]
#[ORM\Table(name: 'machine_activations')]
class MachineActivation
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 36)]
    private string $licenseId;

    #[ORM\Column(type: 'string', length: 64)]
    private string $machineFingerprint;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $activatedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $lastValidatedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $expiresAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updatedAt;

    // Getters and setters...
    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getLicenseId(): string { return $this->licenseId; }
    public function setLicenseId(string $licenseId): void { $this->licenseId = $licenseId; }

    public function getMachineFingerprint(): string { return $this->machineFingerprint; }
    public function setMachineFingerprint(string $machineFingerprint): void { $this->machineFingerprint = $machineFingerprint; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getActivatedAt(): \DateTime { return $this->activatedAt; }
    public function setActivatedAt(\DateTime $activatedAt): void { $this->activatedAt = $activatedAt; }

    public function getLastValidatedAt(): ?\DateTime { return $this->lastValidatedAt; }
    public function setLastValidatedAt(?\DateTime $lastValidatedAt): void { $this->lastValidatedAt = $lastValidatedAt; }

    public function getExpiresAt(): ?\DateTime { return $this->expiresAt; }
    public function setExpiresAt(?\DateTime $expiresAt): void { $this->expiresAt = $expiresAt; }

    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function setCreatedAt(\DateTime $createdAt): void { $this->createdAt = $createdAt; }

    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function setUpdatedAt(\DateTime $updatedAt): void { $this->updatedAt = $updatedAt; }
}