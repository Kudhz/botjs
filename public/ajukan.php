<?php

$url = "https://e-kinerja.kemenhub.go.id/skp/renaksi/pengajuan";

// Get data from the HTML form (assuming you've already processed it)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Read inputs sent from botekin "Tambah"
    $cookie = isset($_POST['allcookies']) ? urldecode($_POST['allcookies']) : '';
    $csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    // tupoksi is expected as JSON string (e.g. "[1,2,3]") or as already an array
    $tupoksi_raw =  $_POST['tupoksi'];
    $tupoksi = is_array($tupoksi_raw) ? $tupoksi_raw : json_decode($tupoksi_raw, true);
    if (!is_array($tupoksi)) {
        // fallback: try to parse comma separated
        $tupoksi = array_filter(array_map('trim', explode(',', (string)$tupoksi_raw)), 'strlen');
    }

    // bulan expected as numeric value from botekin (e.g. 9 for September)
    $bulan = isset($_POST['bulan']) ? intval($_POST['bulan']) : 10;
    $tahun = 2025; // fixed as requested
    $id_skp = isset($_POST['id_skp']) ? intval($_POST['id_skp']) : null;

    // Build payload: use tupoksi array as id_indikator_penilaiaan values
    $renaksi = [
        'id_penilaiaan' => $tupoksi
    ];

    $headers_base = [
        "Host: e-kinerja.kemenhub.go.id",
        "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0",
        "Accept: */*",
        "Accept-Language: en-US,en;q=0.5",
        "Accept-Encoding: gzip, deflate, br",
        "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
        "X-Csrf-Token: $csrf",
        "X-Requested-With: XMLHttpRequest",
        "Origin: https://e-kinerja.kemenhub.go.id",
        // Referer may need to be adjusted depending on the target renaksi id
        "Referer: https://e-kinerja.kemenhub.go.id/skp/renaksi/{$id_skp}",
        "Connection: keep-alive",
    ];

    // optional per-user cookiefile: prefer server-side cookie file named cookie_<username>.txt
    $username_raw = isset($_POST['username']) ? (string)$_POST['username'] : '';
    $cookiefile_info = null;
    $cookiefile_user = '';
    if ($username_raw !== '') {
        $san = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $username_raw);
        $cookiefile_user = __DIR__ . "/cookie_{$san}.txt";
        if (file_exists($cookiefile_user)) {
            $cookiefile_info = $cookiefile_user;
        }
    }

    // final headers: if no cookiefile, include Cookie header built from posted allcookies
    $headers = $headers_base;
    if (empty($cookiefile_info) && $cookie !== '') {
        $headers[] = "Cookie: $cookie";
    }

    $postString7 = http_build_query($renaksi);

    $ch7 = curl_init($url);

    $opts = [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $postString7,
        CURLOPT_SSL_VERIFYPEER => false, // keep as-is for test environment
        CURLOPT_SSL_VERIFYHOST => false,
    ];

    // if we have a server-side cookie file for this user, instruct cURL to use it
    if (!empty($cookiefile_info)) {
        $opts[CURLOPT_COOKIEFILE] = $cookiefile_info;
        $opts[CURLOPT_COOKIEJAR] = $cookiefile_info;
    }

    curl_setopt_array($ch7, $opts);

    $response7 = curl_exec($ch7);

    if (curl_errno($ch7)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => curl_error($ch7)]);
    } else {
        // On success: attempt logout on remote site using stored cookies
        $logout_response = null;
        $logout_error = null;
        $logout_http_code = null;
        $logout_url = 'https://e-kinerja.kemenhub.go.id/logout';
        // prefer using a local cookie file for logout so cookies/sessions match what was used during login
    $cookieDir = __DIR__ . '/cookie';
    $cookiefile = $cookieDir . '/cookie.txt';
        if (file_exists($cookiefile)) {
            $chLogout = curl_init($logout_url);
            $logoutHeaders = [
                "Host: e-kinerja.kemenhub.go.id",
                "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0",
                "Accept: */*",
                "X-Requested-With: XMLHttpRequest",
                "Referer: https://e-kinerja.kemenhub.go.id/",
            ];
            $opts = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $logoutHeaders,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 15,
                // use the cookie file created during login
                CURLOPT_COOKIEFILE => $cookiefile,
                CURLOPT_COOKIEJAR => $cookiefile,
            ];
            curl_setopt_array($chLogout, $opts);
            $logout_response = curl_exec($chLogout);
            if (curl_errno($chLogout)) {
                $logout_error = curl_error($chLogout);
            }
            $logout_http_code = curl_getinfo($chLogout, CURLINFO_HTTP_CODE);
            curl_close($chLogout);
        } elseif (!empty($cookie)) {
            // fallback: send Cookie header if no cookie file is present
            $chLogout = curl_init($logout_url);
            $logoutHeaders = [
                "Host: e-kinerja.kemenhub.go.id",
                "Cookie: $cookie",
                "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0",
                "Accept: */*",
                "X-Requested-With: XMLHttpRequest",
                "Referer: https://e-kinerja.kemenhub.go.id/",
            ];
            curl_setopt_array($chLogout, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $logoutHeaders,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 15,
            ]);
            $logout_response = curl_exec($chLogout);
            if (curl_errno($chLogout)) {
                $logout_error = curl_error($chLogout);
            }
            $logout_http_code = curl_getinfo($chLogout, CURLINFO_HTTP_CODE);
            curl_close($chLogout);
        }

        // (ensure session is started before destroying)
        if (session_status() === PHP_SESSION_NONE) session_start();
        // clear session data
        $_SESSION = [];
        // remove session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        // delete cookie files inside cookie/ directory
        $deleted = [];
        $cookieDir = __DIR__ . '/cookie';
        $candidates = [];
        if (is_dir($cookieDir)) {
            $files = glob($cookieDir . '/*.txt');
            if ($files) $candidates = $files;
        }
        foreach ($candidates as $cf) {
            if (file_exists($cf)) {
                $ok = @unlink($cf);
                $deleted[$cf] = $ok ? 'deleted' : 'failed';
            } else {
                $deleted[$cf] = 'not_found';
            }
        }

        http_response_code(200);
        // return remote responses plus the payload we sent for debugging
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'sent_payload' => $renaksi,
            'remote_response' => $response7,
            'logout_response' => $logout_response,
            'logout_error' => $logout_error,
            'logout_http_code' => $logout_http_code,
            'session' => 'destroyed',
            'cookie_files' => $deleted
        ]);
    }

    curl_close($ch7);

}
