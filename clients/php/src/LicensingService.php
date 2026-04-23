<?php

namespace App;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class LicensingService
{
    private HttpClientInterface $httpClient;
    private string $serverUrl;
    private string $licenseKey;
    private string $activationId;
    private string $fingerprint;

    public function __construct(HttpClientInterface $httpClient, string $serverUrl, string $licenseKey)
    {
        $this->httpClient = $httpClient;
        $this->serverUrl = rtrim($serverUrl, '/');
        $this->licenseKey = $licenseKey;
        $this->fingerprint = $this->generateFingerprint();
    }

    public function activate(): bool
    {
        try {
            $response = $this->httpClient->request('POST', $this->serverUrl . '/sdk/activate', [
                'json' => [
                    'licenseKey' => $this->licenseKey,
                    'machineFingerprint' => $this->fingerprint,
                ],
            ]);

            $data = $response->toArray();
            if ($response->getStatusCode() === 201 && isset($data['activationId'])) {
                $this->activationId = $data['activationId'];
                $this->reportActivity('activated');
                return true;
            }
        } catch (TransportExceptionInterface $e) {
            // Handle network errors
        }

        return false;
    }

    public function validate(): bool
    {
        if (!$this->activationId) {
            return false;
        }

        try {
            $response = $this->httpClient->request('POST', $this->serverUrl . '/sdk/validate', [
                'json' => [
                    'activationId' => $this->activationId,
                    'machineFingerprint' => $this->fingerprint,
                ],
            ]);

            $data = $response->toArray();
            return $data['valid'] ?? false;
        } catch (TransportExceptionInterface $e) {
            // Handle network errors - allow offline grace period
            return true; // Assume valid during network issues
        }
    }

    public function reportActivity(string $type, ?string $details = null): void
    {
        if (!$this->activationId) {
            return;
        }

        try {
            $this->httpClient->request('POST', $this->serverUrl . '/sdk/report', [
                'json' => [
                    'licenseId' => $this->getLicenseIdFromActivation(),
                    'machineFingerprint' => $this->fingerprint,
                    'activityType' => $type,
                    'details' => $details,
                    'reportedAt' => (new \DateTime())->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (TransportExceptionInterface $e) {
            // Activity reporting failures shouldn't break the app
        }
    }

    private function generateFingerprint(): string
    {
        $components = [
            php_uname('s'),      // OS
            php_uname('r'),      // Release
            php_uname('m'),      // Machine type
        ];

        // Add CPU info if available
        if (function_exists('shell_exec')) {
            // Windows CPU info
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $output = shell_exec('wmic cpu get ProcessorId /format:csv 2>nul');
                if ($output) {
                    $lines = array_filter(explode("\n", trim($output)));
                    if (count($lines) > 1) {
                        $processorId = trim($lines[1]);
                        if ($processorId && $processorId !== 'ProcessorId') {
                            $components[] = 'CPU:' . $processorId;
                        }
                    }
                }
            } else {
                // Linux CPU info
                $output = shell_exec('lscpu | grep -i "Serial" 2>/dev/null');
                if ($output) {
                    $components[] = 'CPU:' . trim($output);
                }
            }
        }

        // Add MAC address if available
        if (function_exists('shell_exec')) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $output = shell_exec('getmac /fo csv /nh 2>nul');
                if ($output) {
                    $lines = explode("\n", trim($output));
                    foreach ($lines as $line) {
                        $parts = str_getcsv($line);
                        if (count($parts) >= 3 && $parts[2] !== 'Disabled') {
                            $components[] = 'MAC:' . $parts[0];
                            break;
                        }
                    }
                }
            } else {
                // Linux MAC address
                $output = shell_exec('ip link show | grep "link/ether" 2>/dev/null');
                if ($output) {
                    preg_match('/link\/ether\s+([0-9a-f:]+)/i', $output, $matches);
                    if (!empty($matches[1])) {
                        $components[] = 'MAC:' . $matches[1];
                    }
                }
            }
        }

        // Add disk serial if available
        if (function_exists('shell_exec')) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $output = shell_exec('wmic logicaldisk get volumeserialnumber 2>nul');
                if ($output) {
                    $lines = array_filter(explode("\n", trim($output)));
                    if (count($lines) > 1) {
                        $volumeSerial = trim($lines[1]);
                        if ($volumeSerial && is_numeric($volumeSerial)) {
                            $components[] = 'DISK:' . $volumeSerial;
                        }
                    }
                }
            } else {
                // Linux disk serial
                $output = shell_exec('lsblk -d -o name,serial 2>/dev/null | tail -1');
                if ($output) {
                    $parts = preg_split('/\s+/', trim($output));
                    if (count($parts) >= 2) {
                        $components[] = 'DISK:' . $parts[1];
                    }
                }
            }
        }

        return hash('sha256', implode('|', $components));
    }

    /**
     * Get detailed fingerprint info (for debugging)
     */
    public function getFingerprintDebug(): string
    {
        return $this->fingerprint;
    }

    /**
     * Get detailed system info (for debugging)
     */
    public static function getSystemInfo(): array
    {
        $info = [
            'os' => php_uname('s'),
            'kernel' => php_uname('r'),
            'arch' => php_uname('m'),
            'hostname' => php_uname('n'),
            'php_version' => PHP_VERSION,
            'system_info' => php_uname('a'),
        ];

        if (function_exists('shell_exec')) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows
                $output = shell_exec('wmic os get caption /format:csv 2>nul');
                if ($output) {
                    $lines = array_filter(explode("\n", trim($output)));
                    if (count($lines) > 1) {
                        $info['windows_version'] = trim($lines[1]);
                    }
                }
            }
        }

        return $info;
    }

    private function getLicenseIdFromActivation(): string
    {
        // In a real implementation, you'd store and retrieve this
        // For demo purposes, we'll use a placeholder
        return 'placeholder-license-id';
    }
}