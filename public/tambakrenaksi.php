<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

$url = "https://e-kinerja.kemenhub.go.id/skp/renaksi/add";

// Get data from the HTML form (assuming you've already processed it)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Read inputs sent from botekin "Tambah"
    $cookie = isset($_POST['allcookies']) ? urldecode($_POST['allcookies']) : '';
    $csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    // tupoksi is expected as JSON string (e.g. "[1,2,3]") or as already an array
    $tupoksi_raw = isset($_POST['tupoksi']) ? $_POST['tupoksi'] : '[]';
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
        'id_penilaiaan' => '',
        'id_indikator_penilaiaan' => array_values($tupoksi),
        'bulan' => $bulan,
        'tahun' => $tahun,
        'id_skp' => $id_skp
    ];

    $headers = [
        "Host: e-kinerja.kemenhub.go.id",
        "Cookie: $cookie",
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

    $postString7 = http_build_query($renaksi);

    $ch7 = curl_init($url);
    curl_setopt_array($ch7, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $postString7,
        CURLOPT_SSL_VERIFYPEER => false, // keep as-is for test environment
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $response7 = curl_exec($ch7);

    if (curl_errno($ch7)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => curl_error($ch7)]);
    } else {
        http_response_code(200);
        // return remote response plus the payload we sent for debugging
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'sent_payload' => $renaksi,
            'remote_response' => $response7
        ]);
    }

    curl_close($ch7);

}
