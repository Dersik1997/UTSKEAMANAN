<?php
require 'koneksi.php';

if (!isset($_SESSION['login_simetris'])) {
    header("Location: index.php");
    exit;
}

$kunci_aktif = $_SESSION['kunci_aktif'] ?? '';
$kunci_cocok = (hash('sha256', $kunci_aktif) === VALID_KEY_HASH);

function encUI($teks, $kunci_cocok, $seed = 'simetris') {
    if ($kunci_cocok) {
        return htmlspecialchars((string)$teks);
    }
    $b64 = base64_encode(hash('sha256', (string)$teks . $seed, true));
    $len = max(6, min(26, strlen((string)$teks) + 2));
    return htmlspecialchars(substr($b64, 0, $len));
}

// Ambil data pesanan
$result = mysqli_query($conn, "SELECT * FROM pesanan_simetris ORDER BY id DESC");
$total_pesanan = $result ? mysqli_num_rows($result) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $kunci_cocok ? 'Dashboard Admin - Warung Simetris (AES-256)' : encUI('Dashboard Admin Warung', false) ?></title>
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
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-50 via-emerald-50/10 to-slate-100 text-slate-800 min-h-screen flex flex-col font-sans">

    <!-- Top Admin Bar -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3.5 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br <?= $kunci_cocok ? 'from-emerald-600 to-teal-700 shadow-emerald-600/20' : 'from-rose-600 to-rose-800 shadow-rose-600/20' ?> flex items-center justify-center text-white font-black text-lg shadow-md">
                    <?= $kunci_cocok ? 'A1' : '🔒' ?>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="font-extrabold <?= $kunci_cocok ? 'text-slate-900' : 'text-rose-700 font-mono' ?> text-base sm:text-lg leading-tight">
                            <?= encUI('Dashboard Admin Warung', $kunci_cocok) ?>
                        </h1>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full <?= $kunci_cocok ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200 font-mono' ?> border">
                            <?= encUI('AES-256-CBC', $kunci_cocok) ?>
                        </span>
                    </div>
                    <p class="text-xs <?= $kunci_cocok ? 'text-slate-500' : 'text-rose-500 font-mono' ?> font-medium">
                        <?= encUI('Single-Key Cryptography • X = D(K, Y)', $kunci_cocok) ?>
                    </p>
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

        <!-- Status Kunci Info -->
        <?php if ($kunci_cocok): ?>
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm shadow-sm flex items-center gap-3">
                <span class="text-lg">🔓</span>
                <span>Kunci Simetris cocok! Seluruh data pesanan otomatis didekripsi dan ditampilkan secara normal.</span>
            </div>
        <?php else: ?>
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs sm:text-sm shadow-sm flex items-center gap-3 font-mono">
                <span class="text-lg">🔒</span>
                <span class="font-medium break-all"><?= encUI('Kunci tidak cocok! Seluruh halaman dan data tetap dalam kondisi terenkripsi.', false) ?></span>
            </div>
        <?php endif; ?>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider <?= $kunci_cocok ? 'text-slate-400' : 'text-rose-500 font-mono' ?>">
                        <?= encUI('Total Pesanan', $kunci_cocok) ?>
                    </span>
                    <h3 class="text-2xl font-black <?= $kunci_cocok ? 'text-slate-900' : 'text-rose-700 font-mono text-lg' ?> mt-1">
                        <?= encUI((string)$total_pesanan, $kunci_cocok) ?>
                    </h3>
                    <p class="text-[11px] <?= $kunci_cocok ? 'text-slate-500' : 'text-rose-400 font-mono' ?> mt-0.5">
                        <?= encUI('Tersimpan di database', $kunci_cocok) ?>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl <?= $kunci_cocok ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' ?> flex items-center justify-center text-xl font-bold">
                    <?= $kunci_cocok ? '📦' : '🔒' ?>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider <?= $kunci_cocok ? 'text-slate-400' : 'text-rose-500 font-mono' ?>">
                        <?= encUI('Status Kunci', $kunci_cocok) ?>
                    </span>
                    <h3 class="text-sm font-extrabold <?= $kunci_cocok ? 'text-emerald-700' : 'text-rose-700' ?> mt-1 font-mono truncate max-w-[140px] sm:max-w-[160px]">
                        <?= encUI($kunci_aktif, $kunci_cocok) ?>
                    </h3>
                    <p class="text-[11px] <?= $kunci_cocok ? 'text-emerald-600' : 'text-rose-600 font-mono' ?> mt-0.5 flex items-center gap-1 font-medium">
                        <span class="w-2 h-2 rounded-full <?= $kunci_cocok ? 'bg-emerald-500' : 'bg-rose-500' ?>"></span>
                        <?= encUI($kunci_cocok ? 'Kunci Cocok' : 'Kunci Salah', $kunci_cocok) ?>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl <?= $kunci_cocok ? 'bg-teal-50 text-teal-600' : 'bg-rose-50 text-rose-600' ?> flex items-center justify-center text-xl font-bold">
                    🔑
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider <?= $kunci_cocok ? 'text-slate-400' : 'text-rose-500 font-mono' ?>">
                        <?= encUI('Status Data', $kunci_cocok) ?>
                    </span>
                    <h3 class="text-base font-extrabold <?= $kunci_cocok ? 'text-emerald-700' : 'text-rose-700 font-mono text-sm' ?> mt-1">
                        <?= encUI($kunci_cocok ? 'Normal (Plaintext)' : 'Terenkripsi (Ciphertext)', $kunci_cocok) ?>
                    </h3>
                    <p class="text-[11px] <?= $kunci_cocok ? 'text-slate-500' : 'text-rose-400 font-mono' ?> mt-0.5">
                        <?= encUI($kunci_cocok ? 'Terbaca dengan Secret Key' : 'Gagal dekripsi, tetap teracak', $kunci_cocok) ?>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl <?= $kunci_cocok ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' ?> flex items-center justify-center text-xl font-bold">
                    <?= $kunci_cocok ? '🔓' : '🔒' ?>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider <?= $kunci_cocok ? 'text-slate-400' : 'text-rose-500 font-mono' ?>">
                        <?= encUI('Algoritma Sandi', $kunci_cocok) ?>
                    </span>
                    <h3 class="text-base font-extrabold <?= $kunci_cocok ? 'text-slate-900' : 'text-rose-700 font-mono text-sm' ?> mt-1">
                        <?= encUI('AES-256-CBC', $kunci_cocok) ?>
                    </h3>
                    <p class="text-[11px] <?= $kunci_cocok ? 'text-slate-500' : 'text-rose-400' ?> mt-0.5 font-mono">
                        <?= encUI('IV: 16 Byte Base64', $kunci_cocok) ?>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl <?= $kunci_cocok ? 'bg-indigo-50 text-indigo-600' : 'bg-rose-50 text-rose-600' ?> flex items-center justify-center text-xl font-bold">
                    🛡️
                </div>
            </div>

        </div>

        <!-- Control Bar: Status & Search -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Left Info -->
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $kunci_cocok ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800 font-mono' ?>">
                        <?= encUI($kunci_cocok ? 'STATUS: DATA NORMAL (TERDEKRIPSI)' : 'STATUS: TERENKRIPSI (KUNCI SALAH)', $kunci_cocok) ?>
                    </span>
                </div>
                <p class="text-xs <?= $kunci_cocok ? 'text-slate-500' : 'text-rose-500 font-mono' ?>">
                    <?= encUI($kunci_cocok ? 'Data pesanan didekripsi otomatis menggunakan Kunci Simetris yang Anda masukkan saat login.' : 'Karena kunci yang dimasukkan salah, seluruh tampilan dan data terenkripsi.', $kunci_cocok) ?>
                </p>
            </div>

            <!-- Right Controls -->
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <!-- Live Search Box -->
                <div class="relative">
                    <input type="text" id="tabelSearch" placeholder="<?= encUI('Cari pesanan...', $kunci_cocok) ?>" onkeyup="filterTable()"
                        class="pl-8 pr-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 w-44 sm:w-64 font-medium">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs">🔍</span>
                </div>
            </div>

        </div>

        <!-- Orders Table Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table id="pesananTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider <?= $kunci_cocok ? 'text-slate-600' : 'text-rose-600 font-mono' ?>">
                            <th class="py-4 px-4 sm:px-6"><?= encUI('ID', $kunci_cocok) ?></th>
                            <th class="py-4 px-4"><?= encUI('Nama Pembeli', $kunci_cocok) ?></th>
                            <th class="py-4 px-4"><?= encUI('Menu Pesanan', $kunci_cocok) ?></th>
                            <th class="py-4 px-4"><?= encUI('Alamat Pengiriman', $kunci_cocok) ?></th>
                            <th class="py-4 px-4"><?= encUI('No. HP', $kunci_cocok) ?></th>
                            <th class="py-4 px-4"><?= encUI('IV (Base64)', $kunci_cocok) ?></th>
                            <th class="py-4 px-4 sm:px-6 text-right"><?= encUI('Waktu', $kunci_cocok) ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if ($total_pesanan === 0): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center <?= $kunci_cocok ? 'text-slate-400' : 'text-rose-400 font-mono' ?>">
                                    <div class="text-3xl mb-2"><?= $kunci_cocok ? '📭' : '🔒' ?></div>
                                    <div class="font-medium"><?= encUI('Belum ada data pesanan di database.', $kunci_cocok) ?></div>
                                    <div class="text-[11px] mt-1"><?= encUI('Silakan coba lakukan pemesanan dari Halaman Pembeli.', $kunci_cocok) ?></div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php
                                $waktu = !empty($row['waktu_pesan']) ? date('d M Y, H:i', strtotime($row['waktu_pesan'])) : '-';
                                ?>
                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                    <td class="py-4 px-4 sm:px-6 font-bold <?= $kunci_cocok ? 'text-slate-400' : 'text-rose-500' ?> font-mono">
                                        <?= encUI('#' . $row['id'], $kunci_cocok) ?>
                                    </td>

                                    <?php if ($kunci_cocok): ?>
                                        <!-- Plaintext Decrypted View -->
                                        <td class="py-4 px-4 font-bold text-slate-900">
                                            <?= htmlspecialchars(dekripsiSimetris($row['nama_pembeli'], $kunci_aktif, $row['iv'])) ?>
                                        </td>
                                        <td class="py-4 px-4 text-emerald-800 font-medium">
                                            <span class="inline-block px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200">
                                                <?= htmlspecialchars(dekripsiSimetris($row['menu_pesanan'], $kunci_aktif, $row['iv'])) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-slate-700 max-w-xs leading-relaxed">
                                            <?= htmlspecialchars(dekripsiSimetris($row['alamat_pengiriman'], $kunci_aktif, $row['iv'])) ?>
                                        </td>
                                        <td class="py-4 px-4 font-mono text-slate-800">
                                            <?= htmlspecialchars(dekripsiSimetris($row['no_hp'], $kunci_aktif, $row['iv'])) ?>
                                        </td>
                                    <?php else: ?>
                                        <!-- Ciphertext Encrypted View (Kunci Salah) -->
                                        <?php
                                        $nama_out   = dekripsiSimetris($row['nama_pembeli'], $kunci_aktif, $row['iv']);
                                        $menu_out   = dekripsiSimetris($row['menu_pesanan'], $kunci_aktif, $row['iv']);
                                        $alamat_out = dekripsiSimetris($row['alamat_pengiriman'], $kunci_aktif, $row['iv']);
                                        $nohp_out   = dekripsiSimetris($row['no_hp'], $kunci_aktif, $row['iv']);
                                        ?>
                                        <td class="py-4 px-4 font-mono text-[11px] text-rose-600 break-all max-w-[160px]">
                                            <span class="bg-rose-50 px-2 py-1 rounded border border-rose-200 block truncate" title="<?= htmlspecialchars($nama_out) ?>">
                                                <?= htmlspecialchars($nama_out) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 font-mono text-[11px] text-rose-600 break-all max-w-[180px]">
                                            <span class="bg-rose-50 px-2 py-1 rounded border border-rose-200 block truncate" title="<?= htmlspecialchars($menu_out) ?>">
                                                <?= htmlspecialchars($menu_out) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 font-mono text-[11px] text-rose-600 break-all max-w-[200px]">
                                            <span class="bg-rose-50 px-2 py-1 rounded border border-rose-200 block truncate" title="<?= htmlspecialchars($alamat_out) ?>">
                                                <?= htmlspecialchars($alamat_out) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 font-mono text-[11px] text-rose-600 break-all max-w-[140px]">
                                            <span class="bg-rose-50 px-2 py-1 rounded border border-rose-200 block truncate" title="<?= htmlspecialchars($nohp_out) ?>">
                                                <?= htmlspecialchars($nohp_out) ?>
                                            </span>
                                        </td>
                                    <?php endif; ?>

                                    <!-- IV Column -->
                                    <td class="py-4 px-4 font-mono text-[11px] <?= $kunci_cocok ? 'text-slate-500' : 'text-rose-500' ?> break-all max-w-[130px]">
                                        <span class="<?= $kunci_cocok ? 'bg-slate-100 border-slate-200 text-slate-600' : 'bg-rose-50 border-rose-200 text-rose-600' ?> px-2 py-1 rounded border block truncate" title="<?= htmlspecialchars($row['iv']) ?>">
                                            <?= htmlspecialchars($row['iv']) ?>
                                        </span>
                                    </td>

                                    <!-- Time Column -->
                                    <td class="py-4 px-4 sm:px-6 text-right <?= $kunci_cocok ? 'text-slate-400' : 'text-rose-500 font-mono' ?> text-[11px] whitespace-nowrap">
                                        <?= encUI($waktu, $kunci_cocok) ?>
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
            <p class="<?= $kunci_cocok ? '' : 'text-rose-500 font-mono' ?>">
                <?= encUI('© 2026 Dashboard Warung Simetris • Sistem Pengamanan Data AES-256-CBC', $kunci_cocok) ?>
            </p>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full <?= $kunci_cocok ? 'bg-emerald-500' : 'bg-rose-500' ?>"></span>
                <span class="font-medium <?= $kunci_cocok ? 'text-slate-600' : 'text-rose-600 font-mono' ?>">
                    <?= encUI($kunci_cocok ? 'Sesi Admin Terverifikasi' : 'Sesi Terenkripsi', $kunci_cocok) ?>
                </span>
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
