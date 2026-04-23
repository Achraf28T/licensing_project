#!/usr/bin/env php
<?php

/**
 * Machine Simulator - Simulates multiple real machines with different hardware
 * Makes actual HTTP requests to test API communication and fingerprinting
 */

class MachineSimulator
{
    private $serverUrl = 'http://localhost:8000';
    private $machineProfiles = [];

    public function __construct()
    {
        $this->generateMachineProfiles();
    }

    /**
     * Generate diverse machine profiles with different hardware characteristics
     */
    private function generateMachineProfiles()
    {
        $this->machineProfiles = [
            [
                'name' => 'Windows Development Machine',
                'os' => 'Windows',
                'osVersion' => '10.0.19045',
                'architecture' => 'x86_64',
                'hostname' => 'DEVELOPER-PC',
                'cpu' => 'Intel Core i9-11900K',
                'cpuCount' => 8,
                'totalMemory' => '32GB',
                'diskSerial' => 'WD5000AAKX-123456',
                'ipAddress' => '192.168.1.100',
                'macAddress' => '00:1A:2B:3C:4D:5E',
            ],
            [
                'name' => 'Linux Server (Ubuntu)',
                'os' => 'Linux',
                'osVersion' => '5.15.0-86-generic',
                'architecture' => 'x86_64',
                'hostname' => 'ubuntu-server-01',
                'cpu' => 'AMD Ryzen 9 5900X',
                'cpuCount' => 12,
                'totalMemory' => '64GB',
                'diskSerial' => 'QEMU_HARDDISK_12345678',
                'ipAddress' => '192.168.1.101',
                'macAddress' => '52:54:00:12:34:56',
            ],
            [
                'name' => 'MacBook Pro',
                'os' => 'Darwin',
                'osVersion' => '14.2.1',
                'architecture' => 'arm64',
                'hostname' => 'MacBook-Pro-M1',
                'cpu' => 'Apple M1 Pro',
                'cpuCount' => 10,
                'totalMemory' => '16GB',
                'diskSerial' => 'S7S3NK0R408963',
                'ipAddress' => '192.168.1.102',
                'macAddress' => 'A0:36:BC:F3:4A:5B',
            ],
            [
                'name' => 'Windows Laptop (Consumer)',
                'os' => 'Windows',
                'osVersion' => '11.0.22621',
                'architecture' => 'x86_64',
                'hostname' => 'LAPTOP-USER123',
                'cpu' => 'Intel Core i7-12700H',
                'cpuCount' => 14,
                'totalMemory' => '16GB',
                'diskSerial' => 'SKHYNx3SM256GCS7K0D5',
                'ipAddress' => '192.168.1.103',
                'macAddress' => '00:50:F2:44:5B:6C',
            ],
            [
                'name' => 'Linux Desktop (Fedora)',
                'os' => 'Linux',
                'osVersion' => '6.6.9-200.fc39.x86_64',
                'architecture' => 'x86_64',
                'hostname' => 'fedora-workstation',
                'cpu' => 'AMD Ryzen 5 5500',
                'cpuCount' => 6,
                'totalMemory' => '32GB',
                'diskSerial' => 'ST1000DM010-2EP102',
                'ipAddress' => '192.168.1.104',
                'macAddress' => '08:00:27:4E:66:77',
            ],
            [
                'name' => 'Windows Server',
                'os' => 'Windows',
                'osVersion' => '10.0.20348',
                'architecture' => 'x86_64',
                'hostname' => 'SERVER-PROD-01',
                'cpu' => 'Intel Xeon Gold 6248',
                'cpuCount' => 20,
                'totalMemory' => '256GB',
                'diskSerial' => 'DELL_S4610_D5E4F5G6H7I8',
                'ipAddress' => '192.168.1.105',
                'macAddress' => 'E8:6A:64:3D:5A:6B',
            ],
        ];
    }

    /**
     * Generate a realistic hardware fingerprint for a machine
     */
    private function generateFingerprint($profile)
    {
        $fingerprintData = sprintf(
            '%s|%s|%s|%s|%s|%s|%s',
            $profile['os'],
            $profile['osVersion'],
            $profile['architecture'],
            $profile['hostname'],
            $profile['cpu'],
            $profile['macAddress'],
            $profile['diskSerial']
        );

        return hash('sha256', $fingerprintData);
    }

    /**
     * Test a single machine against the API
     */
    public function testMachine($profile, $licenseKey = null)
    {
        echo "\n" . str_repeat('=', 80) . "\n";
        echo "Testing Machine: " . $profile['name'] . "\n";
        echo str_repeat('=', 80) . "\n";

        $fingerprint = $this->generateFingerprint($profile);
        echo "\n[Hardware Profile]\n";
        echo "  OS: " . $profile['os'] . " " . $profile['osVersion'] . "\n";
        echo "  Architecture: " . $profile['architecture'] . "\n";
        echo "  Hostname: " . $profile['hostname'] . "\n";
        echo "  CPU: " . $profile['cpu'] . " (" . $profile['cpuCount'] . " cores)\n";
        echo "  Memory: " . $profile['totalMemory'] . "\n";
        echo "  IP Address: " . $profile['ipAddress'] . "\n";
        echo "  MAC Address: " . $profile['macAddress'] . "\n";
        echo "  Disk Serial: " . $profile['diskSerial'] . "\n";

        echo "\n[Generated Fingerprint]\n";
        echo "  SHA-256: " . substr($fingerprint, 0, 32) . "...\n";

        if (!$licenseKey) {
            echo "\n⚠️  No license key provided - skipping activation test.\n";
            return true;
        }

        // Test activation
        echo "\n[Testing Activation]\n";
        $activationId = $this->testActivation($licenseKey, $fingerprint);

        if (!$activationId) {
            echo "  ❌ Activation failed\n";
            return false;
        }

        echo "  ✅ Activation successful: " . substr($activationId, 0, 8) . "...\n";

        // Test validation
        echo "\n[Testing Validation]\n";
        $isValid = $this->testValidation($activationId, $fingerprint);

        if ($isValid) {
            echo "  ✅ License validation successful\n";
        } else {
            echo "  ❌ License validation failed\n";
            return false;
        }

        // Test activity reporting
        echo "\n[Testing Activity Reporting]\n";
        $reported = $this->testActivityReport($licenseKey, $fingerprint, $profile['name']);

        if ($reported) {
            echo "  ✅ Activity reported successfully\n";
        } else {
            echo "  ❌ Activity reporting failed\n";
            return false;
        }

        echo "\n✅ All tests passed for " . $profile['name'] . "\n";
        return true;
    }

    /**
     * Test license activation via API
     */
    private function testActivation($licenseKey, $fingerprint)
    {
        $url = $this->serverUrl . '/sdk/activate';
        $payload = [
            'licenseKey' => $licenseKey,
            'machineFingerprint' => $fingerprint,
        ];

        $response = $this->makeRequest('POST', $url, $payload);

        if ($response && isset($response['activationId'])) {
            return $response['activationId'];
        }

        if ($response && isset($response['error'])) {
            echo "  Error: " . $response['error'] . "\n";
        }

        return null;
    }

    /**
     * Test license validation via API
     */
    private function testValidation($activationId, $fingerprint)
    {
        $url = $this->serverUrl . '/sdk/validate';
        $payload = [
            'activationId' => $activationId,
            'machineFingerprint' => $fingerprint,
        ];

        $response = $this->makeRequest('POST', $url, $payload);

        return $response && isset($response['valid']) && $response['valid'] === true;
    }

    /**
     * Test activity reporting via API
     */
    private function testActivityReport($licenseId, $fingerprint, $machineName)
    {
        $url = $this->serverUrl . '/sdk/report';
        $payload = [
            'licenseId' => $licenseId,
            'machineFingerprint' => $fingerprint,
            'activityType' => 'app_started',
            'details' => 'Simulated activity from ' . $machineName,
            'reportedAt' => date('Y-m-d H:i:s'),
        ];

        $response = $this->makeRequest('POST', $url, $payload);

        return $response && isset($response['message']);
    }

    /**
     * Make HTTP request to API
     */
    private function makeRequest($method, $url, $data = [])
    {
        try {
            $options = [
                'http' => [
                    'method' => $method,
                    'header' => "Content-Type: application/json\r\n",
                    'content' => json_encode($data),
                    'timeout' => 10,
                ]
            ];

            $context = stream_context_create($options);
            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                echo "  ❌ Failed to connect to server at $url\n";
                return null;
            }

            $decoded = json_decode($response, true);
            return $decoded;
        } catch (Exception $e) {
            echo "  ❌ Error: " . $e->getMessage() . "\n";
            return null;
        }
    }

    /**
     * Run all tests
     */
    public function runAllTests($licenseKey = null)
    {
        echo "\n" . str_repeat('#', 80) . "\n";
        echo "# MACHINE SIMULATION TEST SUITE\n";
        echo "# Testing " . count($this->machineProfiles) . " diverse hardware profiles\n";
        echo str_repeat('#', 80) . "\n";

        echo "\nServer URL: " . $this->serverUrl . "\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";

        $results = [];
        foreach ($this->machineProfiles as $profile) {
            $passed = $this->testMachine($profile, $licenseKey);
            $results[$profile['name']] = $passed;
            sleep(1); // Add delay between requests
        }

        $this->printSummary($results);
    }

    /**
     * Print test summary
     */
    private function printSummary($results)
    {
        echo "\n" . str_repeat('#', 80) . "\n";
        echo "# TEST SUMMARY\n";
        echo str_repeat('#', 80) . "\n";

        $passed = array_sum($results);
        $total = count($results);

        foreach ($results as $name => $success) {
            $status = $success ? '✅ PASS' : '❌ FAIL';
            echo $status . " - " . $name . "\n";
        }

        echo "\nTotal: $passed / $total tests passed\n";

        if ($passed === $total) {
            echo "\n🎉 ALL TESTS PASSED! System is working correctly.\n";
        } else {
            echo "\n⚠️  Some tests failed. Check the output above for details.\n";
        }

        echo str_repeat('#', 80) . "\n\n";
    }
}

// Main execution
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

$licenseKey = isset($argv[1]) ? $argv[1] : null;

if (!$licenseKey) {
    echo "Usage: php simulate-machines.php <license-key>\n\n";
    echo "This script simulates multiple machines with different hardware profiles\n";
    echo "and tests API communication.\n\n";
    echo "Example:\n";
    echo "  php simulate-machines.php test-key-abc123\n\n";
    echo "To get a license key:\n";
    echo "  1. Run: php bin/console doctrine:database:create\n";
    echo "  2. Run: php bin/console doctrine:schema:create\n";
    echo "  3. Start server: php -S localhost:8000 -t public\n";
    echo "  4. Visit: http://localhost:8000/admin/dashboard\n";
    echo "  5. Click 'Simulate 5 Machines' to generate test data\n";
    echo "  6. Copy a license key from the Recent Licenses table\n";
    echo "  7. Run this script with that key\n";
    exit(0);
}

$simulator = new MachineSimulator();
$simulator->runAllTests($licenseKey);
