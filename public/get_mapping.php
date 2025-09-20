<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
session_start();
require_once "loginAPI.php"; // Include untuk mengakses fungsi autoLogin dan getMappingData

// logging untuk debug (file sederhana)
define('GM_LOG_DIR', __DIR__ . '/logs');
define('GM_LOG_FILE', GM_LOG_DIR . '/get_mapping.log');
if (!is_dir(GM_LOG_DIR)) {
    @mkdir(GM_LOG_DIR, 0755, true);
}

// Jika ada POST dengan body plain text "username password"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $body = file_get_contents('php://input'); // Ambil body sebagai string
    // log incoming body
    @file_put_contents(GM_LOG_FILE, date('[Y-m-d H:i:s] Incoming body: ') . $body . PHP_EOL, FILE_APPEND);
    $parts = explode(' ', trim($body)); // Pisah berdasarkan spasi
    if (count($parts) == 2) {
        $username = $parts[0];
        $password = $parts[1];

    // prefer per-user cookie file in cookie/ directory so login writes directly to cookie/<username>.txt
    $cookieDir = __DIR__ . '/cookie';
    if (!is_dir($cookieDir)) @mkdir($cookieDir, 0755, true);
    $san = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $username);
    $cookieFile = $cookieDir . "/cookie_{$san}.txt";
    // Panggil fungsi autologin
    $loginResult = autoLogin($username, $password);

        // log autoLogin result (mask password-like fields if present)
        $lr = $loginResult;
        if (is_array($lr)) {
            if (isset($lr['setCookies'])) $lr['setCookies'] = '[COOKIES]';
        }
        @file_put_contents(GM_LOG_FILE, date('[Y-m-d H:i:s] autoLogin result: ') . json_encode($lr) . PHP_EOL, FILE_APPEND);

        if (!$loginResult['success']) {
            echo json_encode(["success" => false, "message" => $loginResult['message']]);
            exit;
        }

        // Jika login berhasil, panggil getMappingData
        $mappingData = getMappingData($loginResult['extraCookies'], $loginResult['xsrfToken'], $loginResult['csrf_token'], $loginResult['id_skp'] , isset($loginResult['id_skp1']) ? $loginResult['id_skp1'] : null);

        // Simpan data ke session
        $_SESSION['csrf_token'] = $loginResult['csrf_token'];
        $_SESSION['allCookies'] = implode('; ', $loginResult['setCookies']); // Simpan sebagai string
        $_SESSION['id_skp'] = $loginResult['id_skp'];

    // user cookie file used (login writes directly into it)
    $userCookieFile = isset($cookieFile) ? $cookieFile : '';

        // Return data minimal + raw response untuk JavaScript processing
        echo json_encode([
            "success" => true,
            "id_skp" => $loginResult['id_skp'],
            "csrf_token" => $loginResult['csrf_token'],
            "allCookies" => $_SESSION['allCookies'],
            "raw_response" => $mappingData['raw_response'] // Raw response untuk JavaScript processing
        ]);
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "Format body tidak valid"]);
        exit;
    }
}
