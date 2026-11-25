<?php
require 'koneksi.php';

$pesan_sukses = '';
$pesan_error = '';

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'key_regenerated') {
        $pesan_sukses = "Pasangan Kunci RSA-2048 baru berhasil diperbarui secara aman!";
    } elseif ($_GET['status'] === 'key_failed') {
        $pesan_error = "Gagal memperbarui pasangan kunci RSA. Periksa OpenSSL server.";
    }
}

// 1. Proses Simpan Pesanan Pembeli (Dienkripsi di Belakang Layar dengan Public Key RSA-2048)
if (isset($_POST['kirim_pesanan'])) {
    $nama   = trim($_POST['nama'] ?? '');
    $menu   = trim($_POST['menu'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $nohp   = trim($_POST['no_hp'] ?? '');

    if (!empty($nama) && !empty($menu) && !empty($alamat) && !empty($nohp)) {
        // Enkripsi seluruh data di belakang layar memakai Public Key Warung
        $nama_enc   = mysqli_real_escape_string($conn, enkripsiAsimetris($nama));
        $menu_enc   = mysqli_real_escape_string($conn, enkripsiAsimetris($menu));
        $alamat_enc = mysqli_real_escape_string($conn, enkripsiAsimetris($alamat));
        $nohp_enc   = mysqli_real_escape_string($conn, enkripsiAsimetris($nohp));

        if (!empty($nama_enc) && !empty($menu_enc) && !empty($alamat_enc) && !empty($nohp_enc)) {
            $query = "INSERT INTO pesanan_asimetris (nama_pembeli, menu_pesanan, alamat_pengiriman, no_hp) 
                      VALUES ('$nama_enc', '$menu_enc', '$alamat_enc', '$nohp_enc')";
            
            if (mysqli_query($conn, $query)) {
                $pesan_sukses = "Terima kasih Kak <strong>" . htmlspecialchars($nama) . "</strong>! Pesanan Anda telah berhasil diterima dan sedang disiapkan oleh dapur warung.";
            } else {
                $pesan_error = "Maaf, terjadi kendala saat menyimpan pesanan. Silakan coba lagi.";
            }
        } else {
            $pesan_error = "Gagal mengamankan data pesanan. Pastikan Public Key server tersedia.";
        }
    } else {
        $pesan_error = "Mohon lengkapi seluruh formulir pemesanan.";
    }
}

// 2. Proses Login Admin (Private Key salah tetap masuk, namun data tetap terenkripsi)
if (isset($_POST['masuk_admin'])) {
    $input_privKey = trim($_POST['private_key'] ?? '');
    if ($input_privKey !== '') {
        $_SESSION['login_asimetris'] = true;
        $_SESSION['private_key_aktif'] = $input_privKey;
        header("Location: admin.php");
        exit;
    } else {
        $pesan_error = "Kunci privat tidak boleh kosong.";
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warung Rasa Nusantara - Sajian Tradisional Khas & Hangat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-amber-50/30 text-slate-800 min-h-screen flex flex-col font-sans selection:bg-amber-600 selection:text-white">

    <!-- Top Announcement Bar -->
    <div class="bg-slate-900 text-amber-200/90 text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-2">
                <span>📍 Buka Setiap Hari: 10:00 - 22:00 WIB</span>
                <span class="text-slate-600 hidden sm:inline">&bull;</span>
                <span class="hidden sm:inline">Pesan Antar Cepat & Segar</span>
            </div>
            <div class="flex items-center gap-3 text-[11px]">
                <a href="../warung-simetris/" class="text-amber-400 hover:text-white underline transition">
                    &larr; Lihat Warung Simetris
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <header class="bg-white/95 backdrop-blur-md border-b border-amber-100 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3.5 flex justify-between items-center">
            
            <!-- Brand Logo -->
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-600 to-amber-500 flex items-center justify-center text-white text-2xl shadow-md shadow-amber-500/20 group-hover:scale-105 transition-transform">
                    🍲
                </div>
                <div>
                    <h1 class="font-extrabold text-slate-900 text-lg tracking-tight group-hover:text-amber-700 transition">Warung Rasa Nusantara</h1>
                    <p class="text-xs text-amber-700 font-medium">Spesialis Masakan Nusantara Warisan</p>
                </div>
            </a>

            <!-- Navigation Links -->
            <div class="flex items-center gap-2 sm:gap-4">
                <a href="#menu-warung" class="hidden sm:inline-flex text-xs font-semibold text-slate-600 hover:text-amber-700 px-3 py-2 rounded-xl hover:bg-amber-50 transition">
                    Daftar Menu
                </a>
                <a href="#cara-pesan" class="hidden md:inline-flex text-xs font-semibold text-slate-600 hover:text-amber-700 px-3 py-2 rounded-xl hover:bg-amber-50 transition">
                    Cara Pesan
                </a>
                <a href="#form-pesanan" class="text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 px-4 py-2.5 rounded-xl shadow-sm shadow-amber-600/20 transition">
                    🛒 Pesan Sekarang
                </a>
                <button type="button" onclick="bukaModalAdmin()" title="Area Khusus Pengelola Warung"
                    class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition border border-slate-200 text-xs flex items-center gap-1.5 font-medium">
                    <span>🔐</span>
                    <span class="hidden lg:inline">Staff</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">

        <!-- Flash Messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4">
            <?php if ($pesan_sukses): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm shadow-sm flex items-start gap-3">
                    <span class="text-2xl">🎉</span>
                    <div class="flex-1 leading-relaxed"><?= $pesan_sukses ?></div>
                </div>
            <?php endif; ?>

            <?php if ($pesan_error): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-sm shadow-sm flex items-start gap-3">
                    <span class="text-2xl">⚠️</span>
                    <div class="flex-1 leading-relaxed font-medium"><?= $pesan_error ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Hero Section Warung -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-stone-900 via-amber-950 to-stone-950 text-white p-8 sm:p-14 shadow-xl">
                <!-- Decorative Glow -->
                <div class="absolute -right-10 -bottom-10 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-2xl space-y-4">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-500/20 border border-amber-400/30 text-amber-300 text-xs font-bold uppercase tracking-wider">
                        🍲 Masakan Hangat & Otentik
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight font-serif">
                        Kenikmatan Kuliner Nusantara di Setiap Suapan.
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                        Diracik dari bahan-bahan segar pilihan dan bumbu rempah tradisional warisan. Kami sajikan hidangan penuh kehangatan yang siap diantar langsung ke tempat Anda.
                    </p>
                    
                    <div class="flex flex-wrap items-center gap-3 pt-3">
                        <a href="#menu-warung" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-6 py-3 rounded-2xl text-sm shadow-lg shadow-amber-500/20 transition flex items-center gap-2">
                            <span>Lihat Pilihan Menu</span>
                            <span>&darr;</span>
                        </a>
                        <div class="flex items-center gap-2 text-xs text-amber-200/80 bg-white/10 backdrop-blur px-3.5 py-2.5 rounded-2xl border border-white/10">
                            <span>🔒</span>
                            <span>Pemesanan Nyaman & Terlindungi</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Etalase Menu Makanan & Minuman -->
        <section id="menu-warung" class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-2 border-b border-amber-200/80 pb-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700">Daftar Hidangan Spesial</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1 font-serif">Pilihan Menu Warung</h3>
                </div>
                <p class="text-xs text-slate-500">Klik tombol <strong>+ Pesan Menu</strong> untuk memasukkannya ke formulir pesanan.</p>
            </div>

            <!-- Menu Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Menu Item 1 -->
                <div class="bg-white rounded-3xl border border-amber-100 shadow-sm hover:shadow-xl hover:border-amber-300 transition-all duration-300 overflow-hidden flex flex-col group">
                    <div class="relative h-44 overflow-hidden bg-amber-100">
                        <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=600&auto=format&fit=crop&q=80" 
                             alt="Sate Ayam Madura" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-5xl bg-amber-100\'>🍢</div>'">
                        <span class="absolute top-3 left-3 bg-amber-500 text-slate-950 font-extrabold text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full shadow">
                            ⭐ Pilihan Utama
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex justify-between items-baseline">
                                <h4 class="font-bold text-slate-900 text-base group-hover:text-amber-700 transition">Sate Ayam Madura</h4>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Daging ayam pilihan dipanggang arang batok kelapa, disiram bumbu kacang gurih legit dengan taburan bawang goreng renyah.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-base font-extrabold text-amber-700 font-mono">Rp 25.000</span>
                            <button type="button" onclick="tambahKePesanan('1 Porsi Sate Ayam Madura')"
                                class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-800 hover:text-white font-bold text-xs transition border border-amber-200 hover:border-amber-600 flex items-center gap-1">
                                <span>+</span><span>Pesan</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Menu Item 2 -->
                <div class="bg-white rounded-3xl border border-amber-100 shadow-sm hover:shadow-xl hover:border-amber-300 transition-all duration-300 overflow-hidden flex flex-col group">
                    <div class="relative h-44 overflow-hidden bg-amber-100">
                        <img src="https://images.unsplash.com/photo-1544025162-d76694265947?w=600&auto=format&fit=crop&q=80" 
                             alt="Nasi Rawon Daging Sapi" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-5xl bg-amber-100\'>🍲</div>'">
                        <span class="absolute top-3 left-3 bg-red-600 text-white font-extrabold text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full shadow">
                            🔥 Khas Jawa Timur
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex justify-between items-baseline">
                                <h4 class="font-bold text-slate-900 text-base group-hover:text-amber-700 transition">Nasi Rawon Daging</h4>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Kuah kluwek hitam pekat harum kaya rempah, irisan daging sapi empuk, tauge pendek segar & sambal terasi.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-base font-extrabold text-amber-700 font-mono">Rp 28.000</span>
                            <button type="button" onclick="tambahKePesanan('1 Nasi Rawon Daging Sapi')"
                                class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-800 hover:text-white font-bold text-xs transition border border-amber-200 hover:border-amber-600 flex items-center gap-1">
                                <span>+</span><span>Pesan</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Menu Item 3 -->
                <div class="bg-white rounded-3xl border border-amber-100 shadow-sm hover:shadow-xl hover:border-amber-300 transition-all duration-300 overflow-hidden flex flex-col group">
                    <div class="relative h-44 overflow-hidden bg-amber-100">
                        <img src="https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=600&auto=format&fit=crop&q=80" 
                             alt="Soto Ayam Lamongan" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-5xl bg-amber-100\'>🍜</div>'">
                        <span class="absolute top-3 left-3 bg-emerald-600 text-white font-extrabold text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full shadow">
                            🍲 Kuah Gurih
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex justify-between items-baseline">
                                <h4 class="font-bold text-slate-900 text-base group-hover:text-amber-700 transition">Soto Ayam Lamongan</h4>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Kuah kuning harum kaldu ayam kampung, suwiran ayam, sohun kenyal, dan taburan bubuk koya gurih melimpah.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-base font-extrabold text-amber-700 font-mono">Rp 22.000</span>
                            <button type="button" onclick="tambahKePesanan('1 Soto Ayam Lamongan')"
                                class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-800 hover:text-white font-bold text-xs transition border border-amber-200 hover:border-amber-600 flex items-center gap-1">
                                <span>+</span><span>Pesan</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Menu Item 4 -->
                <div class="bg-white rounded-3xl border border-amber-100 shadow-sm hover:shadow-xl hover:border-amber-300 transition-all duration-300 overflow-hidden flex flex-col group">
                    <div class="relative h-44 overflow-hidden bg-amber-100">
                        <img src="https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=600&auto=format&fit=crop&q=80" 
                             alt="Es Teh Lemon Madu Segar" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-5xl bg-amber-100\'>🍋</div>'">
                        <span class="absolute top-3 left-3 bg-teal-600 text-white font-extrabold text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full shadow">
                            🧊 Segar Alami
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex justify-between items-baseline">
                                <h4 class="font-bold text-slate-900 text-base group-hover:text-amber-700 transition">Es Teh Lemon Madu</h4>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Seduhan daun teh wangi melati dipadu perasan lemon segar asli dan manisnya madu hutan. Sangat melegakan.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-base font-extrabold text-amber-700 font-mono">Rp 10.000</span>
                            <button type="button" onclick="tambahKePesanan('1 Es Teh Lemon Madu')"
                                class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-800 hover:text-white font-bold text-xs transition border border-amber-200 hover:border-amber-600 flex items-center gap-1">
                                <span>+</span><span>Pesan</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Formulir Pemesanan Makanan -->
        <section id="form-pesanan" class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
            <div class="bg-white rounded-3xl border border-amber-200 shadow-xl p-6 sm:p-10 space-y-6">
                
                <div class="text-center space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-3 py-1 rounded-full border border-amber-200">
                        Formulir Pemesanan Cepat
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-serif">Kirim Pesanan Anda</h3>
                    <p class="text-xs text-slate-500">Silakan isi data pengantaran di bawah ini. Makanan akan segera diolah dan dikirim.</p>
                </div>

                <form method="POST" class="space-y-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nama Pemesan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama" maxlength="100" placeholder="Contoh: Muhammad Firaj" required
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm font-medium transition placeholder:text-slate-400">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Menu yang Dipesan & Jumlah <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="kolom_pesanan" name="menu" maxlength="180" placeholder="Contoh: 1 Sate Ayam, 1 Es Teh Lemon" required
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm font-medium transition placeholder:text-slate-400">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Nomor WhatsApp / HP <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="no_hp" maxlength="30" placeholder="Contoh: 085712345678" required
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm font-medium transition placeholder:text-slate-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alamat Pengantaran / Nomor Meja <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alamat" rows="2" maxlength="250" placeholder="Contoh: Jl. Cikole Dalam No. 12 (Depan Kantor Pos)" required
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm font-medium transition placeholder:text-slate-400"></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" name="kirim_pesanan"
                            class="w-full bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white font-bold py-4 px-6 rounded-2xl shadow-lg shadow-amber-600/30 hover:shadow-amber-600/40 transition-all flex items-center justify-center gap-2 text-base">
                            <span>Kirim Pesanan Sekarang</span>
                            <span>🛵</span>
                        </button>
                    </div>

                    <div class="text-center pt-2">
                        <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-400 font-medium">
                            <span>🔒</span>
                            <span>Privasi terjamin. Data pemesanan Anda disimpan secara aman & terenkripsi.</span>
                        </span>
                    </div>
                </form>

            </div>
        </section>

        <!-- Cara Pesan & Info Warung -->
        <section id="cara-pesan" class="max-w-7xl mx-auto px-4 sm:px-6 py-10 border-t border-amber-200/60">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                <div class="p-6 rounded-3xl bg-white border border-amber-100 shadow-sm space-y-2">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl mx-auto font-bold">1</div>
                    <h4 class="font-bold text-slate-900 text-base">Pilih Menu Favorit</h4>
                    <p class="text-xs text-slate-500">Pilih dari etalase menu masakan tradisional khas nusantara yang tersedia.</p>
                </div>
                <div class="p-6 rounded-3xl bg-white border border-amber-100 shadow-sm space-y-2">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl mx-auto font-bold">2</div>
                    <h4 class="font-bold text-slate-900 text-base">Isi Alamat & Kontak</h4>
                    <p class="text-xs text-slate-500">Tuliskan nama, nomor WhatsApp aktif, dan alamat pengantaran lengkap Anda.</p>
                </div>
                <div class="p-6 rounded-3xl bg-white border border-amber-100 shadow-sm space-y-2">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl mx-auto font-bold">3</div>
                    <h4 class="font-bold text-slate-900 text-base">Makanan Siap Diantar</h4>
                    <p class="text-xs text-slate-500">Dapur kami segera memasak hidangan hangat dan kurir meluncur ke tempat Anda!</p>
                </div>
            </div>
        </section>

    </main>

    <!-- Modal Login Admin / Staff Warung (Rapi & Tersembunyi) -->
    <div id="modalAdmin" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 sm:p-8 space-y-5 relative">
            <button type="button" onclick="tutupModalAdmin()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xl font-bold p-1">
                &times;
            </button>

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl font-bold">
                    🔐
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-lg">Login Staff / Admin Warung</h3>
                    <p class="text-xs text-slate-500">Gunakan Private Key untuk membuka dashboard pesanan</p>
                </div>
            </div>

            <form method="POST" class="space-y-4">
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700">RSA Private Key (.pem)</label>
                        <label for="upload_priv_file" class="cursor-pointer text-xs font-bold text-amber-700 hover:text-amber-800 flex items-center gap-1">
                            📁 Unggah File .pem
                        </label>
                        <input type="file" id="upload_priv_file" accept=".pem,.key,.txt" onchange="unggahFilePem(this)" class="hidden">
                    </div>
                    <textarea id="input_priv" name="private_key" rows="5" placeholder="Tempel isi private.pem di sini (-----BEGIN PRIVATE KEY-----...)" required
                        class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500 text-[11px] font-mono leading-relaxed transition"></textarea>
                </div>

                <button type="submit" name="masuk_admin"
                    class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-6 rounded-2xl shadow-md transition-all text-sm">
                    Verifikasi Kunci & Buka Dashboard &rarr;
                </button>
            </form>
        </div>
    </div>

    <!-- Footer Warung -->
    <footer class="bg-white border-t border-amber-100 py-8 text-center text-xs text-slate-500 mt-12">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <p class="font-bold text-slate-700">Warung Rasa Nusantara &bull; Kuliner Tradisional Asli</p>
                <p class="text-[11px] text-slate-400 mt-0.5">&copy; 2026 Seluruh Hak Cipta Dilindungi</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="bukaModalAdmin()" class="text-amber-700 hover:underline font-semibold text-xs">
                    🔐 Area Khusus Pengelola
                </button>
            </div>
        </div>
    </footer>

    <!-- Interactive Script -->
    <script>
        function tambahKePesanan(item) {
            const field = document.getElementById('kolom_pesanan');
            if (field.value.trim() === '') {
                field.value = item;
            } else {
                field.value += ', ' + item;
            }
            // Smooth scroll to form
            document.getElementById('form-pesanan').scrollIntoView({ behavior: 'smooth' });
            field.focus();
        }

        function bukaModalAdmin() {
            document.getElementById('modalAdmin').classList.remove('hidden');
        }

        function tutupModalAdmin() {
            document.getElementById('modalAdmin').classList.add('hidden');
        }

        function unggahFilePem(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('input_priv').value = e.target.result;
                };
                reader.readAsText(input.files[0]);
            }
        }

        // Close modal on escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') tutupModalAdmin();
        });
    </script>
</body>
</html>
