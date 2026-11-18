<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "123";
$port = 3306;
$db   = "db_keamanan_warung";

// Koneksi resilien dengan pembuatan database otomatis jika belum ada
$conn = @mysqli_connect($host, $user, $pass, $db, $port);
if (!$conn) {
    $rootConn = @mysqli_connect($host, $user, $pass, "", $port);
    if ($rootConn) {
        @mysqli_query($rootConn, "CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        @mysqli_close($rootConn);
        $conn = @mysqli_connect($host, $user, $pass, $db, $port);
    }
}

if (!$conn) {
    die("<div style='font-family:sans-serif;padding:24px;background:#fef2f2;color:#991b1b;border-radius:16px;margin:24px;border:1px solid #fecaca;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);'>
        <h3 style='margin-top:0;font-size:18px;font-weight:700;'>⚠️ Koneksi Database Gagal</h3>
        <p style='margin:8px 0;font-size:14px;'>Pastikan MySQL di Laragon sudah aktif dan password root sesuai (default: <code>123</code>).</p>
        <p style='margin:8px 0;font-size:12px;background:#fee2e2;padding:8px 12px;border-radius:8px;font-family:monospace;'>Detail: " . htmlspecialchars(mysqli_connect_error()) . "</p>
    </div>");
}

// Pastikan tabel pesanan_simetris sudah ada
$sql_table = "CREATE TABLE IF NOT EXISTS `pesanan_simetris` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_pembeli` TEXT NOT NULL,
    `menu_pesanan` TEXT NOT NULL,
    `alamat_pengiriman` TEXT NOT NULL,
    `no_hp` TEXT NOT NULL,
    `iv` VARCHAR(255) NOT NULL,
    `waktu_pesan` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
@mysqli_query($conn, $sql_table);

// Pastikan tipe kolom TEXT
@mysqli_query($conn, "ALTER TABLE `pesanan_simetris` MODIFY `nama_pembeli` TEXT NOT NULL, MODIFY `menu_pesanan` TEXT NOT NULL, MODIFY `alamat_pengiriman` TEXT NOT NULL, MODIFY `no_hp` TEXT NOT NULL");

// Kunci Rahasia Simetris (Secret Key K)
if (!defined('SECRET_KEY_MASTER')) {
    define('SECRET_KEY_MASTER', 'KunciWarungSimetris2026');
}
if (!defined('VALID_KEY_HASH')) {
    define('VALID_KEY_HASH', hash('sha256', SECRET_KEY_MASTER));
}

// Fungsi Enkripsi Simetris: Y = E(K, X)
function enkripsiSimetris($plaintext, $key, $iv_raw) {
    if ($plaintext === null || $plaintext === '') return '';
    $cipher = "aes-256-cbc";
    $key_hash = hash('sha256', $key, true);
    return openssl_encrypt($plaintext, $cipher, $key_hash, 0, $iv_raw);
}

// Fungsi Dekripsi Simetris: X = D(K, Y)
function dekripsiSimetris($ciphertext, $key, $iv_base64) {
    if (empty($ciphertext)) return '-';
    $cipher = "aes-256-cbc";
    $key_hash = hash('sha256', $key, true);
    $iv_raw = base64_decode($iv_base64);
    $decrypted = openssl_decrypt($ciphertext, $cipher, $key_hash, 0, $iv_raw);
    return $decrypted !== false ? $decrypted : "[Kunci Salah / Gagal Dekripsi]";
}
?>
