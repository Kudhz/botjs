<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$connectTimeout = 30; // seconds to wait for connection
$requestTimeout = 300; // seconds maximum for each request

// Logout logic
if (isset($_GET['logout'])) {
    $logoutUrl = "https://e-kinerja.kemenhub.go.id/logout";

    // Ambil cookies dari session dan pastikan bentuknya array
    $allCookies = isset($_SESSION['allCookies']) ? $_SESSION['allCookies'] : [];
    if (!is_array($allCookies)) {
        $allCookies = strlen($allCookies) ? [$allCookies] : [];
    }

    // Ekstrak XSRF-TOKEN jika ada
    $xsrfToken = null;
    foreach ($allCookies as $cookie) {
        if (strpos($cookie, 'XSRF-TOKEN=') === 0) {
            $xsrfToken = substr($cookie, strlen('XSRF-TOKEN='));
            break;
        }
    }

    $extraCookies = !empty($allCookies) ? implode('; ', $allCookies) : '';

    $dummy = [];
    curl_request($logoutUrl, $extraCookies, $xsrfToken, $dummy, false, null);
    unset($_SESSION['allCookies']);
    echo "Logged out.";
    exit;
}

$cookieDir = __DIR__ . "/cookie";
if (!is_dir($cookieDir)) @mkdir($cookieDir, 0755, true);
// Lokasi file cookie default (legacy single-file)
$cookieFile = $cookieDir . "/cookie.txt";
$loginPageUrl = "https://e-kinerja.kemenhub.go.id/auth/login";
$loginPostUrl = "https://e-kinerja.kemenhub.go.id/auth";

/**
 * Fungsi download halaman dengan cURL
 */
function curlGet($url, $cookieFile) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/**
 * Fungsi download captcha dengan cookie session
 */
function downloadCaptcha($url, $cookieFile) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    $imageData = curl_exec($ch);
    curl_close($ch);
    return $imageData;
}

function curlRequest($url, $cookieFile) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    $imageData = curl_exec($ch);
    curl_close($ch);
    return $imageData;
}

function curl_request($url, $extraCookies = "", $csrf_token = null, &$setCookies = array(), $isPost = false, $postData = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    // Auto-decode gzip/deflate/br if supported by libcurl
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $headers = ["User-Agent: Mozilla/5.0"];
    if ($csrf_token) {
        $headers[] = "X-CSRF-TOKEN: $csrf_token";
    }
    if ($extraCookies) {
        $headers[] = "Cookie: $extraCookies";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($isPost) {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($postData) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
    }

    $res = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "<h3>cURL Error:</h3><p>" . curl_error($ch) . "</p>";
        return null;
    }

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($res, 0, $header_size);
    $body   = substr($res, $header_size);

    preg_match_all('/Set-Cookie:\s*([^;]*)/mi', $header, $matches);
    foreach($matches[1] as $cookie) {
        $setCookies[] = $cookie;
    }

    curl_close($ch);
    return $body;
}

/**
 * Fungsi OCR captcha dengan tesseract
 */
function readCaptchaOCR($filePath) {
    $outputFile = tempnam(sys_get_temp_dir(), 'ocr');
    $command = "tesseract " 
             . escapeshellarg($filePath) . " " 
             . escapeshellarg($outputFile) 
             . " -l eng --oem 1 --psm 7 2>&1";

    exec($command, $output, $return_var);

    if ($return_var !== 0) {
        return ''; // gagal OCR
    }

    $text = file_get_contents($outputFile . '.txt');
    unlink($outputFile);
    unlink($outputFile . '.txt');

    return trim($text);
}
$id_skp = null;
$id_skp1 = null;

/**
 * Fungsi autologin dengan username dan password
 */
function autoLogin($username, $password) {
    global $cookieFile, $loginPageUrl, $loginPostUrl;

    // === 1. Ambil halaman login ===
    $response = curlGet($loginPageUrl, $cookieFile);
    if (empty($response)) {
        return ["success" => false, "message" => "Gagal mengambil halaman login"];
    }

    // === 2. Ambil csrf token ===
    $dom = new DOMDocument();
    @$dom->loadHTML($response);
    $xpath = new DOMXPath($dom);
    $csrf_token = "";
    $tokenNodes = $xpath->query("//input[@name='_token']");
    if ($tokenNodes->length > 0) {
        $node = $tokenNodes->item(0);
        if ($node instanceof DOMElement) {
            $csrf_token = $node->getAttribute("value");
        }
    }

    // Fallback: jika tidak ditemukan via DOM, coba regex pada HTML mentah
    if (empty($csrf_token)) {
        // coba input hidden sebagai fallback
        if (preg_match('/<input[^>]*name=[\'\"]?_token[\'\"]?[^>]*value=[\'\"]([^\'\"]+)[\'\"]/i', $response, $m)) {
            $csrf_token = $m[1];
        }
    }

    // Fallback kedua: meta tag csrf (beberapa aplikasi menyimpan token di meta)
    if (empty($csrf_token)) {
        if (preg_match('/<meta[^>]*name=[\'\"]csrf-token[\'\"]\s+content=[\'\"]([^\'\"]+)[\'\"]/i', $response, $m2)) {
            $csrf_token = $m2[1];
        }
    }

    // === 3. Ambil url captcha ===
    $captchaUrl = "";
    $imgNodes = $xpath->query("//div[@class='input-group div-img-captcha mt-2']//img");
    if ($imgNodes->length > 0) {
        $node = $imgNodes->item(0);
        if ($node instanceof DOMElement) {
            $captchaUrl = $node->getAttribute("src");
            if (strpos($captchaUrl, "http") === false) {
                $captchaUrl = "https://e-kinerja.kemenhub.go.id" . $captchaUrl;
            }
        }
    }

    // === 4. Download captcha ===
    $captchaData = downloadCaptcha($captchaUrl, $cookieFile);
    $captchaFile = __DIR__ . "/captcha.png";
    file_put_contents($captchaFile, $captchaData);

    // === 5. OCR captcha ===
    $captchaInput = readCaptchaOCR($captchaFile);
    $captchaInput = strtoupper(trim($captchaInput));
    $captchaInput = str_replace(' ', '', $captchaInput);
    $captchaInput = preg_replace('/[^A-Z0-9]/', '', $captchaInput);

    // === 6. Login ===
    $postData = [
        "_token" => $csrf_token,
        "act" => "login",
        "v_username" => $username,
        "v_password" => $password,
        "v_captcha" => $captchaInput,
    ];

    $headers = [
        "Host: e-kinerja.kemenhub.go.id",
        "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0",
        "Accept: */*",
        "Accept-Language: en-US,en;q=0.5",
        "Accept-Encoding: gzip, deflate, br",
        "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
        "X-Csrf-Token: $csrf_token",
        "X-Requested-With: XMLHttpRequest",
        "Origin: https://e-kinerja.kemenhub.go.id",
        "Referer: https://e-kinerja.kemenhub.go.id/auth/login",
        "Sec-Fetch-Dest: empty",
        "Sec-Fetch-Mode: cors",
        "Sec-Fetch-Site: same-origin",
        "Priority: u=0",
        "Te: trailers",
        "Connection: keep-alive",
    ];
$connectTimeout = 30; // seconds to wait for connection
$requestTimeout = 300; // seconds maximum for each request
    $ch = curl_init($loginPostUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_CONNECTTIMEOUT => $connectTimeout,
        CURLOPT_TIMEOUT => $requestTimeout,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    curl_close($ch);

    // Parse cookies
    preg_match_all('/Set-Cookie: ([^\r\n]+)/', $header, $cookieMatches);
    $setCookies = [];
    if (!empty($cookieMatches[1])) {
        foreach ($cookieMatches[1] as $rawCookieLine) {
            if (preg_match('/^([^=]+=[^;]+)/', $rawCookieLine, $m)) {
                $setCookies[] = $m[1];
            }
        }
    }

    if (empty($setCookies)) {
        return ["success" => false, "message" => "Login gagal, tidak ada cookie"];
    }

    $extraCookies = implode('; ', $setCookies);
    $xsrfToken = null;
    foreach ($setCookies as $cookie) {
        if (stripos($cookie, 'XSRF-TOKEN=') !== false) {
            if (preg_match('/XSRF-TOKEN=([^;]+)/i', $cookie, $mc)) {
                $xsrfToken = urldecode($mc[1]);
                break;
            }
        }
    }

    // === 7. Ambil halaman utama ===
    $mainPageUrl = "https://e-kinerja.kemenhub.go.id/skp";
    $mainPageUrl1 = "https://e-kinerja.kemenhub.go.id/skp";
    $dummy = [];
    // Prefer passing csrf_token as header if available, otherwise use xsrfToken from cookie
    $tokenForHeader = !empty($csrf_token) ? $csrf_token : $xsrfToken;
    $mainResponse = curl_request($mainPageUrl, $extraCookies, $tokenForHeader, $dummy, false, null);
   // $mainResponse1 = curl_request($mainPageUrl1, $extraCookies, $tokenForHeader, $dummy, false, null);

    // Debug logging: record limited mainResponse and any csrf-like matches
    $logFile = __DIR__ . '/logs/get_mapping.log';
    $snippet = substr($mainResponse, 0, 3000);
    @file_put_contents($logFile, date('[Y-m-d H:i:s] mainResponse snippet: ') . preg_replace('/\s+/', ' ', $snippet) . PHP_EOL, FILE_APPEND);
    // search patterns
    $found = [];
    if (preg_match('/<input[^>]*name=[\'\"]?_token[\'\"]?[^>]*value=[\'\"]([^\'\"]+)[\'\"]/i', $mainResponse, $mm)) $found['input_token'] = $mm[1];
    if (preg_match('/<meta[^>]*name=[\'\"]csrf-token[\'\"]\s+content=[\'\"]([^\'\"]+)[\'\"]/i', $mainResponse, $mm2)) $found['meta_csrf'] = $mm2[1];
    if (preg_match('/var\s+csrf_token\s*=\s*[\'\"]([^\'\"]+)[\'\"]/i', $mainResponse, $mjs)) $found['js_var_csrf'] = $mjs[1];
    if (preg_match('/XSRF-TOKEN=([^;]+)/i', implode('; ', $setCookies), $mc2)) $found['cookie_xsrf'] = urldecode($mc2[1]);
    if (!empty($found)) @file_put_contents($logFile, date('[Y-m-d H:i:s] csrf-like found: ') . json_encode($found) . PHP_EOL, FILE_APPEND);

    // Try to extract csrf token from main page if not present from login page
    if (empty($csrf_token) && !empty($mainResponse)) {
        // input hidden fallback
        if (preg_match('/<input[^>]*name=[\'\"]?_token[\'\"]?[^>]*value=[\'\"]([^\'\"]+)[\'\"]/i', $mainResponse, $mm)) {
            $csrf_token = $mm[1];
        }
    }
    // meta tag fallback
    if (empty($csrf_token) && !empty($mainResponse)) {
        if (preg_match('/<meta[^>]*name=[\'\"]csrf-token[\'\"]\s+content=[\'\"]([^\'\"]+)[\'\"]/i', $mainResponse, $mm2)) {
            $csrf_token = $mm2[1];
        }
    }
    // js var fallback
    if (empty($csrf_token) && !empty($mainResponse)) {
        if (preg_match('/var\s+csrf_token\s*=\s*[\'\"]([^\'\"]+)[\'\"]/i', $mainResponse, $mjs)) {
            $csrf_token = $mjs[1];
        }
    }
    // cookie fallback: check setCookies aggregated
    if (empty($csrf_token) && !empty($setCookies)) {
        $joined = implode('; ', $setCookies);
        if (preg_match('/XSRF-TOKEN=([^;]+)/i', $joined, $mc2)) {
            $csrf_token = urldecode($mc2[1]);
        }
    }

    // Parse id_skp
    
    // if (preg_match('/var\s+skp\s*=\s*({.*?});/s', $mainResponse, $m)) {
    //     $skpData = json_decode($m[1], true);
    //     if (isset($skpData['id'])) {
    //         $id_skp = $skpData['id'];
    //     }
    // }

    if (preg_match('/var\s+dataList\s*=\s*(\[[\s\S]*?\]);/i', $mainResponse, $m)) {
        $dataList = json_decode($m[1], true);

        // Ambil id berdasarkan "tanggal_awal":"2025-01-01"
        if (is_array($dataList)) {
            foreach ($dataList as $item) {
                if (isset($item['tanggal_awal']) && $item['tanggal_awal'] === '2025-06-01' && isset($item['id'])) {
                    $id_skp = $item['id'];
                     break;
                }
               
                   
                
            }
        }
    }
    // debug output removed to keep responses clean
    if (!$id_skp) {
        return ["success" => false, "message" => "ID SKP tidak ditemukan"];
    }

    return [
        "success" => true,
        "extraCookies" => $extraCookies,
        "xsrfToken" => $xsrfToken,
        "csrf_token" => $csrf_token,
        "id_skp" => $id_skp,
        "setCookies" => $setCookies
    ];
}

// === 1. Ambil halaman login ===
$response = curlGet($loginPageUrl, $cookieFile);

if (empty($response)) {
    echo "Error: Gagal mengambil halaman login. Respons kosong.";
    exit;
}

// === 2. Ambil csrf token dari input hidden ===
$dom = new DOMDocument();
@$dom->loadHTML($response);
$xpath = new DOMXPath($dom);


$tokenNodes = $xpath->query("//input[@name='_token']");
if ($tokenNodes->length > 0) {
    $node = $tokenNodes->item(0);
    if ($node instanceof DOMElement) {
        $csrf_token = $node->getAttribute("value");
    }
}

// === 3. Ambil url captcha ===
$captchaUrl = "";
$imgNodes = $xpath->query("//div[@class='input-group div-img-captcha mt-2']//img");
if ($imgNodes->length > 0) {
    $node = $imgNodes->item(0);
    if ($node instanceof DOMElement) {
        $captchaUrl = $node->getAttribute("src");
    }
    if (strpos($captchaUrl, "http") === false) {
        $captchaUrl = "https://e-kinerja.kemenhub.go.id" . $captchaUrl;
    }
}

// === 4. Download captcha ===
// === STEP 4: Download captcha ===
// === 4. Download captcha ===
$captchaData = downloadCaptcha($captchaUrl, $cookieFile);
$captchaFile = __DIR__ . "/captcha.png";
file_put_contents($captchaFile, $captchaData);

 $indikator_kinerja1 = null;

/**
 * Fungsi untuk mendapatkan data mapping dari halaman renaksi
 */
function getMappingData($extraCookies, $xsrfToken, $csrf_token, $id_skp) {
    $renaksiUrl = "https://e-kinerja.kemenhub.go.id/skp/renaksi/" . urlencode($id_skp);
    $dummy2 = [];
    $renaksiResponse = curl_request($renaksiUrl, $extraCookies, $xsrfToken, $dummy2, false, $csrf_token);


    return [
       
        'csrf_token' => $csrf_token,
        'allcookies' => $extraCookies,
        'id_skp' => $id_skp,
        'raw_response' => $renaksiResponse // Tambahkan raw response
    ];
}

// === Setelah login berhasil ===
// if ($id_skp) {
//     // Panggil fungsi untuk mendapatkan data mapping
//     $mappingData = getMappingData($extraCookies, $xsrfToken, $csrf_token, $id_skp);

//     // Simpan data ke session
//     $_SESSION['bulan'] = $mappingData['mapping'];
//     $_SESSION['mapping_id_penilaiaan'] = $mappingData['mapping_id_penilaiaan'];
//     $_SESSION['mapping_id_penilaiaan1'] = $mappingData['mapping_id_penilaiaan1'];
//     $_SESSION['mapping_tupoksi'] = $mappingData['mapping_tupoksi'];
//     $_SESSION['mapping_rhk'] = $mappingData['mapping_rhk'];
//     $_SESSION['mapping_bukti_dukung'] = $mappingData['mapping_bukti_dukung'];
//     $_SESSION['csrf_token'] = $csrf_token;
//     $_SESSION['jumlahUnikIndikatorKinerja'] = $mappingData['jumlahUnikIndikatorKinerja'];
//     $_SESSION['id_skp'] = $id_skp;
//     // debug output removed to keep responses clean
//     exit;
// }



// Mapping angka ke nama bulan Indonesia

    // Mapping id_penilaiaan berdasarkan bulan di $penilaiaanArr


