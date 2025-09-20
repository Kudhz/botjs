<?php
session_start();
// Hapus semua data session (termasuk allCookies)
session_unset();
session_destroy();
header("Location: loginAPI.php");
exit;
$cookieFile = __DIR__ . "/cookie.txt";
$logoutUrl  = "https://e-kinerja.kemenhub.go.id/logout";

if (isset($_GET['logout'])) {
    // Request logout
    $ch = curl_init($logoutUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR  => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    // Hapus cookie file biar logout bersih
    if (file_exists($cookieFile)) {
        unlink($cookieFile);
    }

    echo "<h3>Anda sudah logout</h3>";
    echo '<a href="loginAPI.php">Login kembali</a>';
    exit;
}
?>

<!-- Tombol logout -->
<a href="?logout=1" 
   style="display:inline-block;padding:8px 16px;background:#d9534f;color:white;text-decoration:none;border-radius:4px;">
   Logout
</a>
