<?php
require 'koneksi.php';

if (!isset($_SESSION['login_simetris'])) {
    header("Location: index.php");
    exit;
}

$kunci_aktif = $_SESSION['kunci_aktif'] ?? SECRET_KEY_MASTER;
$mode_dekripsi = false;
$error_dekripsi = '';
$sukses_dekripsi = '';

// Cek apakah admin mengubah mode dekripsi
if (isset($_POST['aksi_dekripsi'])) {
    $kunci_konfirmasi = trim($_POST['kunci_dekripsi'] ?? '');
    if (hash('sha256', $kunci_konfirmasi) === VALID_KEY_HASH) {
        $mode_dekripsi = true;
        $kunci_aktif = $kunci_konfirmasi;
        $sukses_dekripsi = "Kunci Simetris terverifikasi! Data berhasil didekripsi menjadi Plaintext.";
    } else {
        $error_dekripsi = "Kunci Dekripsi salah! Sistem menolak membuka data demi menjaga kerahasiaan.";
    }
} elseif (isset($_GET['mode']) && $_GET['mode'] === 'decrypt') {
    $mode_dekripsi = true;
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
    <title>Dashboard Admin - Warung Simetris (AES-256)</title>
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
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 flex items-center justify-center text-white font-black text-lg shadow-md shadow-emerald-600/20">
                    A1
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="font-extrabold text-slate-900 text-base sm:text-lg leading-tight">Dashboard Admin Warung</h1>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                            AES-256-CBC
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium">Single-Key Cryptography &bull; Notasi: <code class="font-mono text-emerald-700">X = D(K, Y)</code></p>
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
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm shadow-sm flex items-center gap-3">
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
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                    📦
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Kunci</span>
                    <h3 class="text-sm font-extrabold text-emerald-700 mt-1 font-mono truncate max-w-[140px] sm:max-w-[160px]"><?= htmlspecialchars($kunci_aktif) ?></h3>
                    <p class="text-[11px] text-emerald-600 mt-0.5 flex items-center gap-1 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Kunci Terotentikasi
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl font-bold">
                    🔑
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Mode Tampilan</span>
                    <h3 class="text-base font-extrabold <?= $mode_dekripsi ? 'text-emerald-700' : 'text-amber-700' ?> mt-1">
                        <?= $mode_dekripsi ? 'Plaintext (Terbuka)' : 'Ciphertext (Terkunci)' ?>
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        <?= $mode_dekripsi ? 'Didekripsi dengan Secret Key' : 'Sesuai data mentah MySQL' ?>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl <?= $mode_dekripsi ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' ?> flex items-center justify-center text-xl font-bold">
                    <?= $mode_dekripsi ? '🔓' : '🔒' ?>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Algoritma Sandi</span>
                    <h3 class="text-base font-extrabold text-slate-900 mt-1">AES-256-CBC</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5 font-mono">IV: 16 Byte Base64</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                    🛡️
                </div>
            </div>

        </div>

        <!-- Control Bar: Mode Toggle & Search -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Left Info -->
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $mode_dekripsi ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                        <?= $mode_dekripsi ? 'STATUS: PLAINTEXT (TERDEKRIPSI)' : 'STATUS: CIPHERTEXT (TERENKRIPSI DI DB)' ?>
                    </span>
                </div>
                <p class="text-xs text-slate-500">
                    <?= $mode_dekripsi 
                        ? 'Data pesanan telah berhasil didekripsi menggunakan Kunci Simetris aktif.' 
                        : 'Menampilkan data terenkripsi asli dari database. Klik tombol dekripsi untuk membedah data.' ?>
                </p>
            </div>

            <!-- Right Controls -->
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <?php if (!$mode_dekripsi): ?>
                    <form method="POST" class="flex items-center gap-2">
                        <input type="hidden" name="kunci_dekripsi" value="<?= htmlspecialchars($kunci_aktif) ?>">
                        <button type="submit" name="aksi_dekripsi"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 rounded-xl text-xs sm:text-sm shadow-sm transition flex items-center gap-2">
                            <span>🔓</span>
                            <span>Dekripsi Pesanan Sekarang</span>
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
                        class="pl-8 pr-3 py-2 rounded-xl border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 w-44 sm:w-56 font-medium">
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
                            <th class="py-4 px-4">IV (Base64)</th>
                            <th class="py-4 px-4 sm:px-6 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if ($total_pesanan === 0): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
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
                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                    <td class="py-4 px-4 sm:px-6 font-bold text-slate-400 font-mono">
                                        #<?= $row['id'] ?>
                                    </td>

                                    <?php if ($mode_dekripsi): ?>
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

                                    <!-- IV Column -->
                                    <td class="py-4 px-4 font-mono text-[11px] text-slate-500 break-all max-w-[130px]">
                                        <span class="bg-slate-100 px-2 py-1 rounded border border-slate-200 text-slate-600 block truncate" title="<?= htmlspecialchars($row['iv']) ?>">
                                            <?= htmlspecialchars($row['iv']) ?>
                                        </span>
                                    </td>

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
            <p>&copy; 2026 Dashboard Warung Simetris &bull; Sistem Pengamanan Data AES-256-CBC</p>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="font-medium text-slate-600">Sesi Admin Terverifikasi</span>
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
