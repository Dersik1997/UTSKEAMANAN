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

// Pastikan tabel pesanan_asimetris sudah ada dan tipe kolom TEXT
$sql_table = "CREATE TABLE IF NOT EXISTS `pesanan_asimetris` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_pembeli` TEXT NOT NULL,
    `menu_pesanan` TEXT NOT NULL,
    `alamat_pengiriman` TEXT NOT NULL,
    `no_hp` TEXT NOT NULL,
    `waktu_pesan` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
@mysqli_query($conn, $sql_table);

// Pastikan tipe kolom TEXT agar data RSA yang panjang (344+ karakter) tidak terpotong
@mysqli_query($conn, "ALTER TABLE `pesanan_asimetris` MODIFY `nama_pembeli` TEXT NOT NULL, MODIFY `menu_pesanan` TEXT NOT NULL, MODIFY `alamat_pengiriman` TEXT NOT NULL, MODIFY `no_hp` TEXT NOT NULL");

// Fungsi deteksi otomatis letak openssl.cnf di Laragon / Windows
function cariOpenSSLCnf() {
    $env = getenv('OPENSSL_CONF');
    if ($env && file_exists($env)) return $env;

    $candidates = [
        'C:/laragon/bin/apache/httpd-2.4.62-240904-win64-VS17/conf/openssl.cnf',
        'C:/laragon/bin/php/php-8.4.27-1-nts-Win32-vs17-x64/extras/ssl/openssl.cnf',
        'C:/laragon/bin/git/mingw64/etc/ssl/openssl.cnf',
        'C:/laragon/bin/git/usr/ssl/openssl.cnf'
    ];
    foreach ($candidates as $c) {
        if (file_exists($c)) return $c;
    }

    $apacheGlob = glob('C:/laragon/bin/apache/*/conf/openssl.cnf');
    if (!empty($apacheGlob) && file_exists($apacheGlob[0])) return $apacheGlob[0];

    $phpGlob = glob('C:/laragon/bin/php/*/extras/ssl/openssl.cnf');
    if (!empty($phpGlob) && file_exists($phpGlob[0])) return $phpGlob[0];

    return null;
}

// Pastikan pasangan Public Key & Private Key RSA tersedia dan valid
function pastikanKunciRSA($forceNew = false) {
    $privPath = __DIR__ . '/private.pem';
    $pubPath  = __DIR__ . '/public.pem';

    if ($forceNew || !file_exists($privPath) || !file_exists($pubPath) || filesize($privPath) < 100) {
        $cnf = cariOpenSSLCnf();
        $config = [
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        if ($cnf) {
            $config['config'] = $cnf;
        }

        $res = openssl_pkey_new($config);
        if ($res) {
            openssl_pkey_export($res, $privKey, null, $config);
            $details = openssl_pkey_get_details($res);
            if (!empty($privKey) && !empty($details['key'])) {
                file_put_contents($privPath, $privKey);
                file_put_contents($pubPath, $details['key']);
                return true;
            }
        }
        return false;
    }
    return true;
}
pastikanKunciRSA();

// Fungsi Enkripsi Asimetris menggunakan Public Key: Y = E(PU, X)
function enkripsiAsimetris($plaintext) {
    if ($plaintext === null || $plaintext === '') return '';
    pastikanKunciRSA();
    $pubKey = @file_get_contents(__DIR__ . '/public.pem');
    if (!$pubKey) return '';

    // Chunking 200 bytes untuk mendukung teks panjang tanpa batas RSA 2048 bit
    $chunks = str_split($plaintext, 200);
    $encryptedAll = '';
    foreach ($chunks as $chunk) {
        $enc = '';
        if (openssl_public_encrypt($chunk, $enc, $pubKey, OPENSSL_PKCS1_PADDING)) {
            $encryptedAll .= $enc;
        } else {
            return '';
        }
    }
    return base64_encode($encryptedAll);
}

// Fungsi Dekripsi Asimetris menggunakan Private Key: X = D(PR, Y)
function dekripsiAsimetris($ciphertext_base64, $privKey) {
    if (empty($ciphertext_base64)) return '-';
    $raw = base64_decode($ciphertext_base64);
    if (!$raw) return '[Ciphertext Rusak/Bukan Base64]';

    // Setiap blok RSA-2048 hasil enkripsi adalah 256 bytes
    $chunks = str_split($raw, 256);
    $plaintext = '';
    foreach ($chunks as $chunk) {
        $dec = '';
        if (@openssl_private_decrypt($chunk, $dec, $privKey, OPENSSL_PKCS1_PADDING)) {
            $plaintext .= $dec;
        } else {
            return '[Private Key Tidak Cocok]';
        }
    }
    return $plaintext;
}
?>
