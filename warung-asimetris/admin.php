<?php
require 'koneksi.php';

if (!isset($_SESSION['login_asimetris'])) {
    header("Location: index.php");
    exit;
}

$privKey_aktif = $_SESSION['private_key_aktif'] ?? '';
$mode_dekripsi = false;
$error_dekripsi = '';
$sukses_dekripsi = '';

// Verifikasi jika admin ingin mendekripsi data
if (isset($_POST['aksi_dekripsi'])) {
    $input_priv = trim($_POST['private_key_dekripsi'] ?? $privKey_aktif);
    $pubKey = @file_get_contents(__DIR__ . '/public.pem');
    $uji = "TEST_RSA_CHALLENGE_" . bin2hex(random_bytes(6));
    $resPriv = @openssl_pkey_get_private($input_priv);

    if ($resPriv && @openssl_sign($uji, $sig, $resPriv, OPENSSL_ALGO_SHA256) && @openssl_verify($uji, $sig, $pubKey, OPENSSL_ALGO_SHA256) === 1) {
        $mode_dekripsi = true;
        $privKey_aktif = $input_priv;
        $sukses_dekripsi = "Private Key RSA terverifikasi cocok! Seluruh data ciphertext berhasil didekripsi.";
    } else {
        $error_dekripsi = "Private Key tidak cocok atau bukan pasangan dari Public Key server! Data tidak dapat didekripsi.";
    }
} elseif (isset($_GET['mode']) && $_GET['mode'] === 'decrypt') {
    $mode_dekripsi = true;
}

$result = mysqli_query($conn, "SELECT * FROM pesanan_asimetris ORDER BY id DESC");
$total_pesanan = $result ? mysqli_num_rows($result) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Warung Asimetris (RSA-2048)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        indigo: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-50 via-indigo-50/10 to-slate-100 text-slate-800 min-h-screen flex flex-col font-sans">

    <!-- Top Admin Bar -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3.5 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-800 flex items-center justify-center text-white font-black text-lg shadow-md shadow-indigo-600/20">
                    A2
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="font-extrabold text-slate-900 text-base sm:text-lg leading-tight">Dashboard Admin Warung</h1>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-indigo-100 text-indigo-800 border border-indigo-200">
                            RSA-2048
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium">Asymmetric Cryptography &bull; Notasi: <code class="font-mono text-indigo-700">X = D(PR, Y)</code></p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="index.php" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3.5 py-2 rounded-xl transition flex items-center gap-1.5">
                    <span>&larr;</span>
                    <span class="hidden sm:inline">Halaman Pembeli</span>
                </a>
                <a href="logout.php" class="text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3.5 py-2 rounded-xl transition flex items-center gap-1.5">
                    <span>🚪</span>
                    <span>Keluar Sesi</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 flex-1 w-full space-y-6">

        <!-- Status Alerts -->
        <?php if ($sukses_dekripsi): ?>
            <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-900 text-xs sm:text-sm shadow-sm flex items-center gap-3">
                <span class="text-lg">🔓</span>
                <span><?= $sukses_dekripsi ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error_dekripsi): ?>
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm shadow-sm flex items-center gap-3">
                <span class="text-lg">⚠️</span>
                <span class="font-medium"><?= $error_dekripsi ?></span>
            </div>
        <?php endif; ?>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pesanan</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1"><?= $total_pesanan ?></h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Tersimpan di database</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                    📦
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Kunci RSA</span>
                    <h3 class="text-sm font-extrabold text-indigo-700 mt-1 font-mono">RSA-2048 PKCS#1</h3>
                    <p class="text-[11px] text-emerald-600 mt-0.5 flex items-center gap-1 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Private Key Verified
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl font-bold">
                    🗝️
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Mode Tampilan</span>
                    <h3 class="text-base font-extrabold <?= $mode_dekripsi ? 'text-indigo-700' : 'text-amber-700' ?> mt-1">
                        <?= $mode_dekripsi ? 'Plaintext (Didekripsi PR)' : 'Ciphertext (Terkunci PU)' ?>
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        <?= $mode_dekripsi ? 'Didekripsi dengan Private Key' : 'Blok Base64 RSA 256-byte' ?>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl <?= $mode_dekripsi ? 'bg-indigo-50 text-indigo-600' : 'bg-amber-50 text-amber-600' ?> flex items-center justify-center text-xl font-bold">
                    <?= $mode_dekripsi ? '🔓' : '🔒' ?>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Validasi Signature</span>
                    <h3 class="text-base font-extrabold text-slate-900 mt-1">SHA-256 Digest</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5 font-mono">Tanda Tangan Asimetris</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl font-bold">
                    ✍️
                </div>
            </div>

        </div>

        <!-- Control Bar: Mode Toggle & Search -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Left Info -->
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $mode_dekripsi ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800' ?>">
                        <?= $mode_dekripsi ? 'STATUS: PLAINTEXT TERDEKRIPSI DENGAN PRIVATE KEY' : 'STATUS: CIPHERTEXT RSA-2048 DARI DATABASE' ?>
                    </span>
                </div>
                <p class="text-xs text-slate-500">
                    <?= $mode_dekripsi 
                        ? 'Seluruh data di bawah ini berhasil dibuka menggunakan Private Key (PR) admin.' 
                        : 'Data dikunci oleh Public Key (PU) saat transaksi. Klik tombol di kanan untuk mendekripsi data.' ?>
                </p>
            </div>

            <!-- Right Controls -->
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <?php if (!$mode_dekripsi): ?>
                    <form method="POST" class="flex items-center gap-2">
                        <textarea name="private_key_dekripsi" class="hidden"><?= htmlspecialchars($privKey_aktif) ?></textarea>
                        <button type="submit" name="aksi_dekripsi"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-5 py-2.5 rounded-xl text-xs sm:text-sm shadow-sm transition flex items-center gap-2">
                            <span>🔓</span>
                            <span>Dekripsi Pesanan dengan Private Key</span>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="admin.php"
                        class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-5 py-2.5 rounded-xl text-xs sm:text-sm shadow-sm transition flex items-center gap-2">
                        <span>🔒</span>
                        <span>Kunci Kembali (Mode Ciphertext)</span>
                    </a>
                <?php endif; ?>

                <!-- Live Search Box -->
                <div class="relative">
                    <input type="text" id="tabelSearch" placeholder="Cari pesanan..." onkeyup="filterTable()"
                        class="pl-8 pr-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 w-44 sm:w-56 font-medium">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs">🔍</span>
                </div>
            </div>

        </div>

        <!-- Orders Table Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table id="pesananTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                            <th class="py-4 px-4 sm:px-6">ID</th>
                            <th class="py-4 px-4">Nama Pembeli</th>
                            <th class="py-4 px-4">Menu Pesanan</th>
                            <th class="py-4 px-4">Alamat Pengiriman</th>
                            <th class="py-4 px-4">No. HP</th>
                            <th class="py-4 px-4 sm:px-6 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if ($total_pesanan === 0): ?>
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <div class="text-3xl mb-2">📭</div>
                                    <div class="font-medium">Belum ada data pesanan di database.</div>
                                    <div class="text-[11px] text-slate-400 mt-1">Silakan coba lakukan pemesanan dari Halaman Pembeli.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php
                                $waktu = !empty($row['waktu_pesan']) ? date('d M Y, H:i', strtotime($row['waktu_pesan'])) : '-';
                                ?>
                                <tr class="hover:bg-indigo-50/30 transition-colors">
                                    <td class="py-4 px-4 sm:px-6 font-bold text-slate-400 font-mono">
                                        #<?= $row['id'] ?>
                                    </td>

                                    <?php if ($mode_dekripsi): ?>
                                        <!-- Plaintext Decrypted View -->
                                        <td class="py-4 px-4 font-bold text-slate-900">
                                            <?= htmlspecialchars(dekripsiAsimetris($row['nama_pembeli'], $privKey_aktif)) ?>
                                        </td>
                                        <td class="py-4 px-4 text-indigo-800 font-medium">
                                            <span class="inline-block px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-200">
                                                <?= htmlspecialchars(dekripsiAsimetris($row['menu_pesanan'], $privKey_aktif)) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-slate-700 max-w-xs leading-relaxed">
                                            <?= htmlspecialchars(dekripsiAsimetris($row['alamat_pengiriman'], $privKey_aktif)) ?>
                                        </td>
                                        <td class="py-4 px-4 font-mono text-slate-800">
                                            <?= htmlspecialchars(dekripsiAsimetris($row['no_hp'], $privKey_aktif)) ?>
                                        </td>
                                    <?php else: ?>
                                        <!-- Ciphertext Encrypted View -->
                                        <td class="py-4 px-4 font-mono text-[11px] text-rose-600 break-all max-w-[160px]">
                                            <span class="bg-rose-50 px-2 py-1 rounded border border-rose-200 block truncate" title="<?= htmlspecialchars($row['nama_pembeli']) ?>">
                                                <?= htmlspecialchars(substr($row['nama_pembeli'], 0, 24)) ?>...
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 font-mono text-[11px] text-rose-600 break-all max-w-[180px]">
                                            <span class="bg-rose-50 px-2 py-1 rounded border border-rose-200 block truncate" title="<?= htmlspecialchars($row['menu_pesanan']) ?>">
                                                <?= htmlspecialchars(substr($row['menu_pesanan'], 0, 24)) ?>...
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 font-mono text-[11px] text-rose-600 break-all max-w-[200px]">
                                            <span class="bg-rose-50 px-2 py-1 rounded border border-rose-200 block truncate" title="<?= htmlspecialchars($row['alamat_pengiriman']) ?>">
                                                <?= htmlspecialchars(substr($row['alamat_pengiriman'], 0, 24)) ?>...
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 font-mono text-[11px] text-rose-600 break-all max-w-[140px]">
                                            <span class="bg-rose-50 px-2 py-1 rounded border border-rose-200 block truncate" title="<?= htmlspecialchars($row['no_hp']) ?>">
                                                <?= htmlspecialchars(substr($row['no_hp'], 0, 20)) ?>...
                                            </span>
                                        </td>
                                    <?php endif; ?>

                                    <!-- Time Column -->
                                    <td class="py-4 px-4 sm:px-6 text-right text-slate-400 text-[11px] whitespace-nowrap">
                                        <?= $waktu ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-5 mt-8 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-3">
            <p>&copy; 2026 Dashboard Warung Asimetris &bull; Sistem Pengamanan Data RSA-2048</p>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <span class="font-medium text-slate-600">Sesi Admin Terverifikasi (Private Key)</span>
            </div>
        </div>
    </footer>

    <!-- Filter Script -->
    <script>
        function filterTable() {
            const input = document.getElementById("tabelSearch");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("pesananTable");
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName("td");
                let match = false;
                for (let j = 0; j < td.length; j++) {
                    if (td[j] && td[j].textContent.toLowerCase().indexOf(filter) > -1) {
                        match = true;
                        break;
                    }
                }
                tr[i].style.display = match ? "" : "none";
            }
        }
    </script>
</body>
</html>
