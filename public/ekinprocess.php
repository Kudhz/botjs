<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
$url = "https://e-kinerja.kemenhub.go.id/skp/renaksi/editdetailitem";

// Get data from the HTML form (assuming you've already processed it)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cookie = urldecode($_POST['allcookies']);
    $csrf = $_POST["csrf_token"];
    $key = "renaksi";
    $realisasi = "realisasi";
    $bukti = "bukti_dukung";
    $values = isset($_POST["rhk"]) ? json_decode($_POST["rhk"], true) : [];
    $totalValues = count($values);
    $jumlahLRhk1 = $_POST["jumlahUnikIndikatorKinerja"];
    $loopId = $jumlahLRhk1;
    $realValue = array ("1 Document", "100 Persen", "1 Bulan");
    $realIndex = 0;
    $drive = $_POST["bukti_dukung"];

    // Timeouts (seconds) - adjust these values to extend/reduce how long we wait
    $connectTimeout = 30; // seconds to wait for connection
    $requestTimeout = 300; // seconds maximum for each request

    // Build all POST requests first, then execute in parallel with curl_multi
    $mh = curl_multi_init();
    $handles = [];
    $handleMap = []; // map (int)$ch => postString for retries
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
        "Referer: https://e-kinerja.kemenhub.go.id/skp/renaksi/262030",
        "Connection: keep-alive",
    ];

    // Parse id_indikator outside the loop
    $id = [];
    if (isset($_POST['id_indikator'])) {
        $rawIdIndikator = $_POST['id_indikator'];
        error_log("📥 Raw id_indikator: " . $rawIdIndikator);
        
        // Coba decode sebagai JSON array dulu
        $decodedIds = json_decode($rawIdIndikator, true);
        if (is_array($decodedIds)) {
            $id = $decodedIds;
            error_log("📥 Decoded as JSON array: " . implode(', ', $id));
        } else {
            // Fallback: explode dengan koma jika bukan JSON
            $id = explode(',', $rawIdIndikator);
            error_log("📥 Exploded by comma: " . implode(', ', $id));
        }
    }
    
    // Debug: Log all values
    error_log("📥 Received Values: " . json_encode($values));
    error_log("📥 Total IDs: " . count($id) . ", Total Values: " . count($values));
    
    for ($i = 0; $i < $loopId; $i++) {
        // Ensure we have the id for current index
        if (!isset($id[$i])) {
            error_log("❌ Missing ID for index $i");
            continue;
        }
        
        $currentId = trim(strval($id[$i])); // Pastikan ID adalah string bersih
        
        // Get value from rhk array - each index corresponds to one renaksi
        $value = isset($values[$i]) ? $values[$i] : '';
        
        // Debug log untuk melihat mapping - termasuk index 0
        $logPrefix = ($i === 0) ? "🔥 INDEX 0" : "Index $i";
        error_log("$logPrefix: ID = '$currentId' (clean), Value = '$value'");
        
        // Validasi ID tidak boleh kosong
        if (empty($currentId)) {
            error_log("❌ Empty ID at index $i, skipping...");
            continue;
        }

        $renaksi = [ 'id' => $currentId, 'key' => $key, 'value' => $value ];
        $realisasi2 = [ 'id' => $currentId, 'key' => $realisasi, 'value' => $realValue[$realIndex] ];
        $realIndex = ($realIndex + 1) % 3;
        $buktiDukung = [ 'id' => $currentId, 'key' => $bukti, 'value' => $drive ];

        $postStrings = [
            http_build_query($renaksi),
            http_build_query($realisasi2),
            http_build_query($buktiDukung),
        ];

    foreach ($postStrings as $ps) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $ps);
            // timeouts (configurable above)
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $requestTimeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_multi_add_handle($mh, $ch);
            $handles[] = $ch;
            $handleMap[(int)$ch] = $ps;
        }
    }

    // Execute all handles in parallel
    $running = null;
    do {
        $mrc = curl_multi_exec($mh, $running);
        // wait for activity on any curl-connection
        if ($running) curl_multi_select($mh, 1.0);
    } while ($running && $mrc == CURLM_OK);

    // Collect responses and errors, and collect payloads that need retry
    $errors = [];
    $responses = [];
    $retryPayloads = [];
    foreach ($handles as $ch) {
        $content = curl_multi_getcontent($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ps = isset($handleMap[(int)$ch]) ? $handleMap[(int)$ch] : null;
        $responses[] = ['http_code' => $httpCode, 'body' => $content, 'payload' => $ps];
        // consider retry on curl error or HTTP 5xx or empty/invalid body
        if ($err) {
            $errors[] = $err;
            if ($ps !== null) $retryPayloads[] = $ps;
        } elseif ($httpCode >= 500 || $httpCode === 0) {
            if ($ps !== null) $retryPayloads[] = $ps;
        }
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    curl_multi_close($mh);
    // Retry failed payloads with exponential backoff
    $maxRetries = 2; // additional attempts
    $retryResults = [];
    $attempt = 0;
    while (!empty($retryPayloads) && $attempt < $maxRetries) {
        $attempt++;
        // small backoff (ms)
        $waitMs = (int)((pow(2, $attempt) * 100) + rand(0, 200));
        usleep($waitMs * 1000);

        $mh2 = curl_multi_init();
        $handles2 = [];
        $handleMap2 = [];
        foreach ($retryPayloads as $ps) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $ps);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $requestTimeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_multi_add_handle($mh2, $ch);
            $handles2[] = $ch;
            $handleMap2[(int)$ch] = $ps;
        }

        // exec
        $running2 = null;
        do {
            $mrc2 = curl_multi_exec($mh2, $running2);
            if ($running2) curl_multi_select($mh2, 1.0);
        } while ($running2 && $mrc2 == CURLM_OK);

        // collect which still fail
        $newRetry = [];
        foreach ($handles2 as $ch) {
            $content = curl_multi_getcontent($ch);
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $ps = isset($handleMap2[(int)$ch]) ? $handleMap2[(int)$ch] : null;
            $retryResults[] = ['attempt' => $attempt, 'http_code' => $httpCode, 'err' => $err, 'payload' => $ps, 'body' => $content];
            if ($err || $httpCode >= 500 || $httpCode === 0) {
                if ($ps !== null) $newRetry[] = $ps;
            }
            curl_multi_remove_handle($mh2, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh2);
        $retryPayloads = $newRetry;
    }

    $suksesCount = 0;
foreach ($responses as $resp) {
    // Cek jika body response adalah JSON dan ada status true
    $body = $resp['body'];
    $json = json_decode($body, true);
    if (is_array($json) && isset($json['status']) && $json['status'] === true) {
        $suksesCount++;
    }
}

if (!empty($errors) || !empty($retryPayloads)) {
    http_response_code(207); // multi-status-ish
    echo json_encode([
        'status' => 'partial',
        'initial_responses' => $responses,
        'retry_summary' => $retryResults,
        'final_failures' => $retryPayloads,
        'success_count' => $suksesCount // <-- jumlah sukses
        
    ]);
} else {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'count' => count($responses),
        'success_count' => $suksesCount, // <-- jumlah sukses
        //'responses' => $responses,
        'retries' => count($retryResults),
        'final_success' => (count($retryResults) + $suksesCount)
    ]);
}
    
}
