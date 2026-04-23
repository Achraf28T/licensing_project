<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ActivityReportRepository;

#[ORM\Entity(repositoryClass: ActivityReportRepository::class)]
#[ORM\Table(name: 'activity_reports')]
class ActivityReport
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 36)]
    private string $licenseId;

    #[ORM\Column(type: 'string', length: 64)]
    private string $machineFingerprint;

    #[ORM\Column(type: 'string', length: 32)]
    private string $activityType;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $details;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $reportedAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    // Getters and setters...
    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getLicenseId(): string { return $this->licenseId; }
    public function setLicenseId(string $licenseId): void { $this->licenseId = $licenseId; }

    public function getMachineFingerprint(): string { return $this->machineFingerprint; }
    public function setMachineFingerprint(string $machineFingerprint): void { $this->machineFingerprint = $machineFingerprint; }

    public function getActivityType(): string { return $this->activityType; }
    public function setActivityType(string $activityType): void { $this->activityType = $activityType; }

    public function getDetails(): ?string { return $this->details; }
    public function setDetails(?string $details): void { $this->details = $details; }

    public function getReportedAt(): \DateTime { return $this->reportedAt; }
    public function setReportedAt(\DateTime $reportedAt): void { $this->reportedAt = $reportedAt; }

    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function setCreatedAt(\DateTime $createdAt): void { $this->createdAt = $createdAt; }
}