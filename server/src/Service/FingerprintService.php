<?php

namespace App\Service;

class FingerprintService
{
    public function generateFingerprint(): string
    {
        $components = [];

        // OS information
        $components[] = php_uname('s'); // OS name
        $components[] = php_uname('r'); // OS release
        $components[] = php_uname('m'); // Machine type

        // CPU information (if available)
        if (function_exists('shell_exec')) {
            $cpuInfo = shell_exec('wmic cpu get name /value 2>nul');
            if ($cpuInfo) {
                preg_match('/Name=(.+)/', $cpuInfo, $matches);
                $components[] = trim($matches[1] ?? '');
            }
        }

        // MAC address (primary network interface)
        $mac = $this->getMacAddress();
        if ($mac) {
            $components[] = $mac;
        }

        // Disk serial number (if available)
        if (function_exists('shell_exec')) {
            $diskInfo = shell_exec('wmic diskdrive get serialnumber /value 2>nul');
            if ($diskInfo) {
                preg_match('/SerialNumber=(.+)/', $diskInfo, $matches);
                $components[] = trim($matches[1] ?? '');
            }
        }

        // Combine and hash
        $fingerprint = implode('|', $components);
        return hash('sha256', $fingerprint);
    }

    private function getMacAddress(): ?string
    {
        if (function_exists('shell_exec')) {
            $output = shell_exec('getmac /fo csv /nh 2>nul');
            if ($output) {
                $lines = explode("\n", trim($output));
                foreach ($lines as $line) {
                    $parts = str_getcsv($line);
                    if (count($parts) >= 3 && $parts[2] !== 'Disabled') {
                        return $parts[0]; // MAC address
                    }
                }
            }
        }
        return null;
    }
}