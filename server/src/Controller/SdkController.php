<?php

namespace App\Controller;

use App\Entity\MachineActivation;
use App\Entity\ActivityReport;
use App\Repository\LicenseRepository;
use App\Repository\MachineActivationRepository;
use App\Repository\ActivityReportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class SdkController extends AbstractController
{
    private LicenseRepository $licenseRepository;
    private MachineActivationRepository $activationRepository;
    private ActivityReportRepository $activityRepository;

    public function __construct(
        LicenseRepository $licenseRepository,
        MachineActivationRepository $activationRepository,
        ActivityReportRepository $activityRepository
    ) {
        $this->licenseRepository = $licenseRepository;
        $this->activationRepository = $activationRepository;
        $this->activityRepository = $activityRepository;
    }

    #[Route('/sdk/activate', name: 'sdk_activate', methods: ['POST'])]
    public function activate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $licenseKeyHash = hash('sha256', $data['licenseKey']);
        $fingerprint = $data['machineFingerprint'];

        $license = $this->licenseRepository->findByLicenseKeyHash($licenseKeyHash);
        if (!$license || $license->getStatus() !== 'active') {
            return new JsonResponse(['error' => 'Invalid license'], 400);
        }

        // Check expiration
        if ($license->getExpiresAt() && $license->getExpiresAt() < new \DateTime()) {
            return new JsonResponse(['error' => 'License expired'], 400);
        }

        // Check activation count
        $activeCount = $this->activationRepository->countActivationsForLicense($license->getId());
        if ($activeCount >= $license->getMaxActivations()) {
            return new JsonResponse(['error' => 'Maximum activations reached'], 400);
        }

        // Check if already activated on this machine
        $existing = $this->activationRepository->findByLicenseAndFingerprint($license->getId(), $fingerprint);
        if ($existing && $existing->getStatus() === 'active') {
            return new JsonResponse(['activationId' => $existing->getId()]);
        }

        $activation = new MachineActivation();
        $activation->setId((string)Uuid::v4());
        $activation->setLicenseId($license->getId());
        $activation->setMachineFingerprint($fingerprint);
        $activation->setStatus('active');
        $activation->setActivatedAt(new \DateTime());
        $activation->setLastValidatedAt(new \DateTime());
        $activation->setExpiresAt($license->getExpiresAt());
        $activation->setCreatedAt(new \DateTime());
        $activation->setUpdatedAt(new \DateTime());

        $this->activationRepository->save($activation, true);

        return new JsonResponse(['activationId' => $activation->getId()], 201);
    }

    #[Route('/sdk/validate', name: 'sdk_validate', methods: ['POST'])]
    public function validate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $activationId = $data['activationId'];
        $fingerprint = $data['machineFingerprint'];

        $activation = $this->activationRepository->find($activationId);
        if (!$activation || $activation->getMachineFingerprint() !== $fingerprint || $activation->getStatus() !== 'active') {
            return new JsonResponse(['valid' => false], 200);
        }

        $license = $this->licenseRepository->find($activation->getLicenseId());
        if (!$license || $license->getStatus() !== 'active') {
            return new JsonResponse(['valid' => false], 200);
        }

        // Check expiration with grace period
        $now = new \DateTime();
        $graceEnd = $license->getExpiresAt() ? clone $license->getExpiresAt() : null;
        if ($graceEnd) {
            $graceEnd->modify("+{$license->getGracePeriodHours()} hours");
        }

        if ($license->getExpiresAt() && $now > $graceEnd) {
            return new JsonResponse(['valid' => false], 200);
        }

        $activation->setLastValidatedAt($now);
        $activation->setUpdatedAt($now);
        $this->activationRepository->save($activation, true);

        return new JsonResponse(['valid' => true], 200);
    }

    #[Route('/sdk/report', name: 'sdk_report_activity', methods: ['POST'])]
    public function reportActivity(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $report = new ActivityReport();
        $report->setId((string)Uuid::v4());
        $report->setLicenseId($data['licenseId']);
        $report->setMachineFingerprint($data['machineFingerprint']);
        $report->setActivityType($data['activityType']);
        $report->setDetails($data['details'] ?? null);
        $report->setReportedAt(new \DateTime($data['reportedAt'] ?? 'now'));
        $report->setCreatedAt(new \DateTime());

        $this->activityRepository->save($report, true);

        return new JsonResponse(['message' => 'Activity reported'], 201);
    }
}