<?php

namespace App\Controller;

use App\Entity\License;
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
use Symfony\Component\HttpFoundation\Response;

class AdminController extends AbstractController
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

    #[Route('/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return new Response(file_get_contents(__DIR__ . '/../../templates/dashboard.html'));
    }

    #[Route('/admin/licenses', name: 'admin_create_license', methods: ['POST'])]
    public function createLicense(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $license = new License();
        $license->setId((string)Uuid::v4());
        $license->setProductId($data['productId']);
        $license->setCustomerId($data['customerId']);
        $license->setLicenseKeyHash(hash('sha256', $data['licenseKey']));
        $license->setPlan($data['plan'] ?? 'standard');
        $license->setStatus('active');
        $license->setMaxActivations($data['maxActivations'] ?? 1);
        $license->setStartsAt(new \DateTime($data['startsAt'] ?? 'now'));
        $license->setExpiresAt(isset($data['expiresAt']) ? new \DateTime($data['expiresAt']) : null);
        $license->setGracePeriodHours($data['gracePeriodHours'] ?? 24);
        $license->setCreatedAt(new \DateTime());
        $license->setUpdatedAt(new \DateTime());

        $this->licenseRepository->save($license, true);

        return new JsonResponse(['licenseId' => $license->getId()], 201);
    }

    #[Route('/admin/licenses/{id}', name: 'admin_revoke_license', methods: ['DELETE'])]
    public function revokeLicense(string $id): JsonResponse
    {
        $license = $this->licenseRepository->find($id);
        if (!$license) {
            return new JsonResponse(['error' => 'License not found'], 404);
        }

        $license->setStatus('revoked');
        $license->setUpdatedAt(new \DateTime());
        $this->licenseRepository->save($license, true);

        return new JsonResponse(['message' => 'License revoked']);
    }

    #[Route('/admin/analytics/overview', name: 'admin_analytics_overview', methods: ['GET'])]
    public function getAnalyticsOverview(): JsonResponse
    {
        $allLicenses = $this->licenseRepository->findAll();
        $now = new \DateTime();
        $thirtyDaysLater = (new \DateTime())->modify('+30 days');

        $activeLicenses = 0;
        $revokedLicenses = 0;
        $expiredLicenses = 0;
        $expiringLicenses = 0;
        $licenseActivationCounts = [];

        foreach ($allLicenses as $license) {
            if ($license->getStatus() === 'revoked') {
                $revokedLicenses++;
            } elseif ($license->getExpiresAt() && $license->getExpiresAt() < $now) {
                $expiredLicenses++;
            } elseif ($license->getExpiresAt() && $license->getExpiresAt() <= $thirtyDaysLater) {
                $expiringLicenses++;
                $activeLicenses++;
            } elseif ($license->getStatus() === 'active') {
                $activeLicenses++;
            }

            $activeCount = $this->activationRepository->countActivationsForLicense($license->getId());
            $licenseActivationCounts[$license->getId()] = $activeCount;
        }

        $allActivations = $this->activationRepository->findAll();
        $totalActivations = count($allActivations);
        
        // Check for suspicious activations (same license on too many machines)
        $suspiciousCount = 0;
        foreach ($allLicenses as $license) {
            if ($licenseActivationCounts[$license->getId()] > $license->getMaxActivations()) {
                $suspiciousCount++;
            }
        }

        return new JsonResponse([
            'totalLicenses' => count($allLicenses),
            'activeLicenses' => $activeLicenses,
            'revokedLicenses' => $revokedLicenses,
            'expiredLicenses' => $expiredLicenses,
            'expiringLicenses' => $expiringLicenses,
            'totalActivations' => $totalActivations,
            'suspiciousActivations' => $suspiciousCount,
            'licenseActivationCounts' => $licenseActivationCounts,
        ]);
    }

    #[Route('/admin/analytics/licenses', name: 'admin_analytics_licenses', methods: ['GET'])]
    public function getAnalyticsLicenses(): JsonResponse
    {
        $licenses = $this->licenseRepository->findAll();

        $data = array_map(function ($license) {
            return [
                'id' => $license->getId(),
                'productId' => $license->getProductId(),
                'customerId' => $license->getCustomerId(),
                'plan' => $license->getPlan(),
                'status' => $license->getStatus(),
                'maxActivations' => $license->getMaxActivations(),
                'startsAt' => $license->getStartsAt()?->format('Y-m-d H:i:s'),
                'expiresAt' => $license->getExpiresAt()?->format('Y-m-d H:i:s'),
                'createdAt' => $license->getCreatedAt()?->format('Y-m-d H:i:s'),
            ];
        }, $licenses);

        return new JsonResponse($data);
    }

    #[Route('/admin/analytics/activations', name: 'admin_analytics_activations', methods: ['GET'])]
    public function getAnalyticsActivations(): JsonResponse
    {
        $activations = $this->activationRepository->findAll();

        // Group by machine fingerprint
        $machineStats = [];
        foreach ($activations as $activation) {
            $fingerprint = $activation->getMachineFingerprint();
            if (!isset($machineStats[$fingerprint])) {
                $machineStats[$fingerprint] = [
                    'machineFingerprint' => $fingerprint,
                    'activationCount' => 0,
                    'status' => $activation->getStatus(),
                    'lastValidatedAt' => null,
                ];
            }
            $machineStats[$fingerprint]['activationCount']++;
            if ($activation->getLastValidatedAt() && 
                (!$machineStats[$fingerprint]['lastValidatedAt'] || 
                 $activation->getLastValidatedAt() > new \DateTime($machineStats[$fingerprint]['lastValidatedAt']))) {
                $machineStats[$fingerprint]['lastValidatedAt'] = $activation->getLastValidatedAt()?->format('Y-m-d H:i:s');
            }
        }

        return new JsonResponse(array_values($machineStats));
    }

    #[Route('/admin/analytics/activities', name: 'admin_analytics_activities', methods: ['GET'])]
    public function getAnalyticsActivities(): JsonResponse
    {
        $activities = array_reverse($this->activityRepository->findAll());

        $data = array_map(function ($activity) {
            return [
                'id' => $activity->getId(),
                'licenseId' => $activity->getLicenseId(),
                'machineFingerprint' => $activity->getMachineFingerprint(),
                'activityType' => $activity->getActivityType(),
                'details' => $activity->getDetails(),
                'reportedAt' => $activity->getReportedAt()?->format('Y-m-d H:i:s'),
            ];
        }, $activities);

        return new JsonResponse($data);
    }

    #[Route('/admin/simulate-test-data', name: 'admin_simulate_test_data', methods: ['POST'])]
    public function simulateTestData(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $machineCount = $data['machineCount'] ?? 5;

        // Create a test license
        $license = new License();
        $license->setId((string)Uuid::v4());
        $license->setProductId('test-product');
        $license->setCustomerId('test-customer-' . rand(1000, 9999));
        $clearKey = 'DEV-KEY-' . rand(100, 999);
        $license->setLicenseKeyHash(hash('sha256', $clearKey));
        // We'll return the clear key in the response so the user knows what to use
        $license->setPlan('premium');
        $license->setStatus('active');
        $license->setMaxActivations($machineCount + 5);
        $license->setStartsAt(new \DateTime());
        $license->setExpiresAt((new \DateTime())->modify('+90 days'));
        $license->setGracePeriodHours(24);
        $license->setCreatedAt(new \DateTime());
        $license->setUpdatedAt(new \DateTime());

        $this->licenseRepository->save($license, true);

        // Create machine activations and activities
        for ($i = 0; $i < $machineCount; $i++) {
            $fingerprint = hash('sha256', 'machine-' . $i . '-' . uniqid());

            // Create activation
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

            // Create activity reports
            $activityTypes = ['app_started', 'validation_check', 'app_closed'];
            foreach ($activityTypes as $type) {
                $activity = new ActivityReport();
                $activity->setId((string)Uuid::v4());
                $activity->setLicenseId($license->getId());
                $activity->setMachineFingerprint($fingerprint);
                $activity->setActivityType($type);
                $activity->setDetails('Simulated activity for testing');
                $activity->setReportedAt((new \DateTime())->modify('-' . rand(0, 60) . ' minutes'));
                $activity->setCreatedAt(new \DateTime());

                $this->activityRepository->save($activity, true);
            }
        }

        return new JsonResponse([
            'message' => "Simulation complete! Created 1 license, $machineCount machines, and " . ($machineCount * 3) . " activities",
            'licenseKey' => $clearKey,
            'licenseId' => $license->getId(),
            'machinesCreated' => $machineCount,
        ]);
    }
}