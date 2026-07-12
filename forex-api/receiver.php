<?php
//+------------------------------------------------------------------+
//|                                                   receiver.php   |
//|                          Forex Data Receiver - Failover System   |
//|                                                                  |
//|  FOLDER STRUCTURE (create this on Namecheap):                   |
//|  public_html/                                                    |
//|  └── forex-api/                                                  |
//|      ├── receiver.php         <- this file                       |
//|      ├── forex_data.json      <- auto created                    |
//|      ├── health_status.json   <- auto created                    |
//|      ├── receiver_log.txt     <- auto created (current)          |
//|      └── receiver_log.old.txt <- auto created (rotated backup)   |
//+------------------------------------------------------------------+

// ============================================================
//  CONFIGURATION - Edit these values
// ============================================================

// Primary VPS (MT4 EA #1) secret key
define('SECRET_KEY_PRIMARY', 'QM*lHyp6C*k*jqF$DY');

// Backup VPS (MT4 EA #2) secret key
define('SECRET_KEY_BACKUP',  'xEH#RP3l4@eb8VdLik');

// If primary is silent for this many seconds, switch to backup
define('FAILOVER_TIMEOUT',   90);

// Maximum allowed data age (seconds) - reject older data
define('MAX_DATA_AGE',       300);

// Enable logging
// true  = log errors and failover events only (recommended)
// false = no logging at all
define('ENABLE_LOG',         true);

// Log file max size in bytes before rotation (1MB default)
define('MAX_LOG_SIZE',       1048576);

// ============================================================
//  FILE PATHS
// ============================================================
define('DATA_FILE',          __DIR__ . '/forex_data.json');
define('HEALTH_FILE',        __DIR__ . '/health_status.json');
define('LOG_FILE',           __DIR__ . '/receiver_log.txt');
define('LOG_FILE_OLD',       __DIR__ . '/receiver_log.old.txt');

// ============================================================
//  CORS HEADERS
// ============================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');

// ============================================================
//  NO-CACHE HEADERS
//  Prevents Cloudflare, CDN, proxies, and browsers
//  from caching responses. Users always get live data.
// ============================================================
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Surrogate-Control: no-store');
header('CDN-Cache-Control: no-store');

// ============================================================
//  ROUTE REQUEST
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    handleGetRequest();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handlePostRequest();
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request method']);


// ============================================================
//  FUNCTION: Handle GET (health check)
// ============================================================
function handleGetRequest() {
    $health = readJsonFile(HEALTH_FILE);

    if (!$health) {
        echo json_encode([
            'success'       => false,
            'active_source' => 'none',
            'message'       => 'No data received yet'
        ]);
        return;
    }

    $now         = time();
    $primaryAge  = isset($health['primary']['last_ping'])
                     ? ($now - $health['primary']['last_ping']) : 999;
    $backupAge   = isset($health['backup']['last_ping'])
                     ? ($now - $health['backup']['last_ping'])  : 999;
    $activeSource = determineActiveSource($health);

    echo json_encode([
        'success'        => true,
        'active_source'  => $activeSource,
        'primary' => [
            'status'      => $primaryAge < FAILOVER_TIMEOUT ? 'online' : 'offline',
            'last_ping'   => $health['primary']['last_ping']  ?? null,
            'age_seconds' => $primaryAge,
            'send_count'  => $health['primary']['send_count'] ?? 0,
        ],
        'backup' => [
            'status'      => $backupAge < FAILOVER_TIMEOUT ? 'online' : 'offline',
            'last_ping'   => $health['backup']['last_ping']   ?? null,
            'age_seconds' => $backupAge,
            'send_count'  => $health['backup']['send_count']  ?? 0,
        ],
        'data_file_age'    => file_exists(DATA_FILE)
                                ? ($now - filemtime(DATA_FILE)) : null,
        'last_failover'    => $health['last_failover']  ?? null,
        'failover_timeout' => FAILOVER_TIMEOUT,
        'server_time'      => date('Y-m-d H:i:s'),
    ]);
}


// ============================================================
//  FUNCTION: Handle POST (receive MT4 data)
// ============================================================
function handlePostRequest() {

    // --- Step 1: Get raw POST data
    $rawData = isset($_POST['data']) ? $_POST['data'] : '';

    if (empty($rawData)) {
        writeLog('ERROR: Empty POST body received');
        echo json_encode(['success' => false, 'error' => 'No data received']);
        return;
    }

    // --- Step 2: Decode JSON
    $data = json_decode($rawData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        writeLog('ERROR: Invalid JSON — ' . json_last_error_msg());
        echo json_encode(['success' => false, 'error' => 'Invalid JSON format']);
        return;
    }

    // --- Step 3: Identify source
    $receivedKey = isset($data['secret_key']) ? $data['secret_key'] : '';
    $source      = identifySource($receivedKey);

    if ($source === 'unknown') {
        writeLog('ERROR: Invalid secret key received — possible unauthorized access');
        echo json_encode(['success' => false, 'error' => 'Authentication failed']);
        return;
    }

    // --- Step 4: Check data age
    $dataTimestamp = isset($data['timestamp']) ? (int)$data['timestamp'] : 0;
    $dataAge       = time() - $dataTimestamp;

    if ($dataAge > MAX_DATA_AGE) {
        writeLog("ERROR: Stale data from [{$source}] rejected — age: {$dataAge}s (max: " . MAX_DATA_AGE . "s)");
        echo json_encode([
            'success' => false,
            'error'   => "Data too old ({$dataAge}s)"
        ]);
        return;
    }

    // --- Step 5: Update health status
    $health = readJsonFile(HEALTH_FILE) ?: [
        'primary' => ['last_ping' => 0, 'send_count' => 0],
        'backup'  => ['last_ping' => 0, 'send_count' => 0],
    ];

    $health[$source]['last_ping']  = time();
    $health[$source]['send_count'] = ($health[$source]['send_count'] ?? 0) + 1;
    $health[$source]['pairs_sent'] = $data['summary']['pairs_sent'] ?? 0;
    $health[$source]['ea_version'] = $data['v'] ?? 'unknown';

    // --- Step 6: Determine active source + detect failover
    $activeSource   = determineActiveSource($health);
    $previousSource = isset($health['active_source']) ? $health['active_source'] : 'none';

    // Failover event — log always regardless of ENABLE_LOG setting
    if ($previousSource !== 'none' && $previousSource !== $activeSource) {
        $failoverMsg = "FAILOVER: Switched [{$previousSource}] → [{$activeSource}]";

        // Force-write failover to log even if ENABLE_LOG is false
        writeLog($failoverMsg, true);

        $health['last_failover'] = [
            'time'      => time(),
            'from'      => $previousSource,
            'to'        => $activeSource,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    $health['active_source'] = $activeSource;
    writeJsonFile(HEALTH_FILE, $health);

    // --- Step 7: Save data only from active source
    if ($source === $activeSource) {

        $data['active_source'] = $activeSource;
        $data['received_at']   = time();
        $data['received_time'] = date('Y-m-d H:i:s');
        unset($data['secret_key']);

        if (writeJsonFile(DATA_FILE, $data)) {
            // Normal OK — NOT logged (saves disk I/O)
            echo json_encode([
                'success'       => true,
                'source'        => $source,
                'active_source' => $activeSource,
                'pairs_saved'   => $data['summary']['pairs_sent'] ?? 0,
            ]);
        } else {
            writeLog("ERROR: Failed to write forex_data.json — check folder permissions (must be 755)");
            echo json_encode(['success' => false, 'error' => 'File write failed']);
        }

    } else {
        // Backup sending while primary is active — NOT logged (normal state)
        echo json_encode([
            'success'       => true,
            'source'        => $source,
            'active_source' => $activeSource,
            'note'          => 'standby — primary is active',
        ]);
    }
}


// ============================================================
//  FUNCTION: Identify source by secret key
// ============================================================
function identifySource($key) {
    if ($key === SECRET_KEY_PRIMARY) return 'primary';
    if ($key === SECRET_KEY_BACKUP)  return 'backup';
    return 'unknown';
}


// ============================================================
//  FUNCTION: Determine active source
// ============================================================
function determineActiveSource($health) {
    $now        = time();
    $primaryAge = isset($health['primary']['last_ping'])
                    ? ($now - $health['primary']['last_ping']) : 999;

    if ($primaryAge < FAILOVER_TIMEOUT) return 'primary';

    $backupAge = isset($health['backup']['last_ping'])
                   ? ($now - $health['backup']['last_ping']) : 999;

    if ($backupAge < FAILOVER_TIMEOUT) return 'backup';

    return 'none';
}


// ============================================================
//  FUNCTION: Read JSON file safely
// ============================================================
function readJsonFile($path) {
    if (!file_exists($path)) return null;
    $content = file_get_contents($path);
    if ($content === false) return null;
    $decoded = json_decode($content, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
}


// ============================================================
//  FUNCTION: Write JSON file atomically
// ============================================================
function writeJsonFile($path, $data) {
    $json    = json_encode($data);
    $tmpFile = $path . '.tmp';
    if (file_put_contents($tmpFile, $json, LOCK_EX) === false) return false;
    return rename($tmpFile, $path);
}


// ============================================================
//  FUNCTION: Write log with rotation
//
//  What gets logged (ENABLE_LOG = true):
//    - ERROR: any failure (JSON, auth, file write, stale data)
//    - FAILOVER: when active source switches
//
//  What does NOT get logged:
//    - Normal OK sends (every 30s = too much disk I/O)
//    - Standby backup pings (normal behavior)
//
//  Rotation behavior:
//    - When receiver_log.txt hits MAX_LOG_SIZE (1MB):
//      1. receiver_log.txt  → renamed to receiver_log.old.txt
//      2. receiver_log.old.txt (previous) → overwritten
//      3. New empty receiver_log.txt starts fresh
//    - You always have up to 2MB of history total
//
//  $force = true bypasses ENABLE_LOG check (used for FAILOVER)
// ============================================================
function writeLog($message, $force = false) {
    if (!ENABLE_LOG && !$force) return;

    // Rotate if current log exceeds size limit
    if (file_exists(LOG_FILE) && filesize(LOG_FILE) >= MAX_LOG_SIZE) {
        // Move current → old (overwrites previous .old if exists)
        rename(LOG_FILE, LOG_FILE_OLD);
        // Write rotation notice to fresh log
        file_put_contents(
            LOG_FILE,
            '[' . date('Y-m-d H:i:s') . '] LOG ROTATED — previous entries in receiver_log.old.txt' . PHP_EOL,
            LOCK_EX
        );
    }

    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents(LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

