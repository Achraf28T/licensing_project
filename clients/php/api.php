<?php
/**
 * VM Test App - Backend API
 * Handles licensing activation and system info
 */

header('Content-Type: application/json');

try {
    // Get the action from request
    $input = json_decode(file_get_contents('php://input'), true) ?? $_GET;
    $action = $input['action'] ?? $_GET['action'] ?? 'system_info';

    switch ($action) {
        case 'system_info':
            handleSystemInfo();
            break;

        case 'test_connection':
            handleTestConnection($input['serverUrl'] ?? null);
            break;

        case 'activate':
            handleActivate(
                $input['serverUrl'] ?? null,
                $input['licenseKey'] ?? null
            );
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action: ' . $action]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Get system information and fingerprint
 */
function handleSystemInfo() {
    $info = [
        'os' => php_uname('s'),
        'kernel' => php_uname('r'),
        'architecture' => php_uname('m'),
        'hostname' => php_uname('n'),
        'php_version' => PHP_VERSION,
        'fingerprint' => generateFingerprint(),
        'timestamp' => date('Y-m-d H:i:s')
    ];

    echo json_encode($info, JSON_PRETTY_PRINT);
}

/**
 * Test connection to the API server
 */
function handleTestConnection($serverUrl) {
    if (empty($serverUrl)) {
        http_response_code(400);
        echo json_encode(['error' => 'Server URL required', 'success' => false]);
        return;
    }

    // Normalize URL
    $serverUrl = rtrim($serverUrl, '/');

    // Try to connect
    try {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
            ]
        ]);

        // Try to access the dashboard
        $response = @file_get_contents($serverUrl . '/admin/dashboard', false, $context);

        if ($response !== false) {
            echo json_encode([
                'success' => true,
                'message' => 'Connected successfully',
                'server_url' => $serverUrl,
                'endpoint' => $serverUrl . '/admin/dashboard'
            ]);
        } else {
            http_response_code(503);
            echo json_encode([
                'error' => 'Server is not responding',
                'success' => false
            ]);
        }
    } catch (Exception $e) {
        http_response_code(503);
        echo json_encode([
            'error' => 'Connection failed: ' . $e->getMessage(),
            'success' => false
        ]);
    }
}

/**
 * Activate a license
 */
function handleActivate($serverUrl, $licenseKey) {
    if (empty($serverUrl) || empty($licenseKey)) {
        http_response_code(400);
        echo json_encode(['error' => 'Server URL and License Key required', 'success' => false]);
        return;
    }

    $serverUrl = rtrim($serverUrl, '/');
    $fingerprint = generateFingerprint();

    try {
        // Prepare the request
        $activateUrl = $serverUrl . '/sdk/activate';
        
        $postData = json_encode([
            'licenseKey' => $licenseKey,
            'machineFingerprint' => $fingerprint,
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nContent-Length: " . strlen($postData) . "\r\n",
                'content' => $postData,
                'timeout' => 10,
            ]
        ]);

        $response = @file_get_contents($activateUrl, false, $context);
        
        if ($response === false) {
            http_response_code(503);
            echo json_encode([
                'error' => 'Server is not accessible. Check the server URL and network connectivity.',
                'success' => false,
                'tried_url' => $activateUrl
            ]);
            return;
        }

        $result = json_decode($response, true);

        if (isset($result['activationId'])) {
            // Success
            echo json_encode([
                'success' => true,
                'message' => 'License activated successfully',
                'activationId' => $result['activationId'],
                'fingerprint' => $fingerprint,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            // API returned an error
            http_response_code(400);
            echo json_encode([
                'error' => $result['error'] ?? 'Activation failed',
                'success' => false,
                'response' => $result
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Activation error: ' . $e->getMessage(),
            'success' => false
        ]);
    }
}

/**
 * Generate comprehensive device fingerprint
 */
function generateFingerprint() {
    $components = [
        php_uname('s'),      // OS
        php_uname('r'),      // Release
        php_uname('m'),      // Machine type
    ];

    // Add CPU info if available
    if (function_exists('shell_exec')) {
        // Windows CPU info
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = @shell_exec('wmic cpu get ProcessorId /format:csv 2>nul');
            if ($output) {
                $lines = array_filter(explode("\n", trim($output)));
                if (count($lines) > 1) {
                    $processorId = trim($lines[1]);
                    if ($processorId && $processorId !== 'ProcessorId') {
                        $components[] = 'CPU:' . substr($processorId, 0, 20); // Limit length
                    }
                }
            }
        } else {
            // Linux CPU info
            $output = @shell_exec('lscpu | grep -i "Serial" 2>/dev/null');
            if ($output) {
                $components[] = 'CPU:' . trim($output);
            }
        }
    }

    // Add MAC address if available
    if (function_exists('shell_exec')) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = @shell_exec('getmac /fo csv /nh 2>nul');
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
            $output = @shell_exec('ip link show | grep "link/ether" 2>/dev/null');
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
            $output = @shell_exec('wmic logicaldisk get volumeserialnumber 2>nul');
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
            $output = @shell_exec('lsblk -d -o name,serial 2>/dev/null | tail -1');
            if ($output) {
                $parts = preg_split('/\s+/', trim($output));
                if (count($parts) >= 2 && !empty($parts[1])) {
                    $components[] = 'DISK:' . substr($parts[1], 0, 20);
                }
            }
        }
    }

    // Generate SHA256 hash of all components
    return hash('sha256', implode('|', $components));
}
