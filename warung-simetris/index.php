<?php
require 'koneksi.php';

$pesan_sukses = '';
$pesan_error = '';

// 1. Proses Simpan Pesanan Pembeli (Dienkripsi di Belakang Layar dengan AES-256)
if (isset($_POST['kirim_pesanan'])) {
    $nama   = trim($_POST['nama'] ?? '');
    $menu   = trim($_POST['menu'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $nohp   = trim($_POST['no_hp'] ?? '');

    if (!empty($nama) && !empty($menu) && !empty($alamat) && !empty($nohp)) {
        $cipher = "aes-256-cbc";
        $iv_len = openssl_cipher_iv_length($cipher);
        $iv_raw = openssl_random_pseudo_bytes($iv_len);
        $iv_base64 = base64_encode($iv_raw);

        // Enkripsi seluruh data di belakang layar (Symmetric AES-256)
        $nama_enc   = mysqli_real_escape_string($conn, enkripsiSimetris($nama, SECRET_KEY_MASTER, $iv_raw));
        $menu_enc   = mysqli_real_escape_string($conn, enkripsiSimetris($menu, SECRET_KEY_MASTER, $iv_raw));
        $alamat_enc = mysqli_real_escape_string($conn, enkripsiSimetris($alamat, SECRET_KEY_MASTER, $iv_raw));
        $nohp_enc   = mysqli_real_escape_string($conn, enkripsiSimetris($nohp, SECRET_KEY_MASTER, $iv_raw));

        $query = "INSERT INTO pesanan_simetris (nama_pembeli, menu_pesanan, alamat_pengiriman, no_hp, iv) 
                  VALUES ('$nama_enc', '$menu_enc', '$alamat_enc', '$nohp_enc', '$iv_base64')";
        
        if (mysqli_query($conn, $query)) {
            $pesan_sukses = "Terima kasih Kak <strong>" . htmlspecialchars($nama) . "</strong>! Pesanan Anda telah berhasil diterima dan sedang disiapkan oleh dapur warung.";
        } else {
            $pesan_error = "Maaf, terjadi kendala saat mengirim pesanan. Silakan coba lagi.";
        }
    } else {
        $pesan_error = "Mohon lengkapi seluruh formulir pemesanan.";
    }
}

// 2. Proses Login Admin (Kunci salah tetap masuk, namun data tetap terenkripsi)
if (isset($_POST['masuk_admin'])) {
    $input_kunci = trim($_POST['kunci_simetris'] ?? '');
    if ($input_kunci !== '') {
        $_SESSION['login_simetris'] = true;
        $_SESSION['kunci_aktif'] = $input_kunci;
        header("Location: admin.php");
        exit;
    } else {
        $pesan_error = "Kunci otorisasi tidak boleh kosong.";
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warung Selera Nusantara - Makanan Tradisional Enak & Segar</title>
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
                    },
                    colors: {
                        brand: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-amber-50/30 text-slate-800 min-h-screen flex flex-col font-sans selection:bg-amber-500 selection:text-white">

    <!-- Top Announcement Bar -->
    <div class="bg-slate-900 text-amber-200/90 text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-2">
                <span>📍 Buka Setiap Hari: 09:00 - 21:00 WIB</span>
                <span class="text-slate-600 hidden sm:inline">&bull;</span>
                <span class="hidden sm:inline">Siap Antar Area Kota & Sekitarnya</span>
            </div>
            <div class="flex items-center gap-3 text-[11px]">
                <a href="../warung-asimetris/" class="text-amber-400 hover:text-white underline transition">
                    Lihat Warung Asimetris &rarr;
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
                    🍛
                </div>
                <div>
                    <h1 class="font-extrabold text-slate-900 text-lg tracking-tight group-hover:text-amber-700 transition">Warung Selera Nusantara</h1>
                    <p class="text-xs text-amber-700 font-medium">Cita Rasa Asli Masakan Rumah</p>
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
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-900 via-stone-900 to-amber-950 text-white p-8 sm:p-14 shadow-xl">
                <!-- Decorative Glow -->
                <div class="absolute -right-10 -bottom-10 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-2xl space-y-4">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-500/20 border border-amber-400/30 text-amber-300 text-xs font-bold uppercase tracking-wider">
                        ✨ Rasa Rempah Asli & Olahan Harian
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight font-serif">
                        Makan Enak, Hangat, & Bikin Rindu Masakan Rumah.
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                        Pilihan menu tradisional favorit dengan bumbu racikan khas yang meresap sempurna. Cukup pilih menu, isi alamat, dan kami antarkan langsung ke meja atau pintu rumah Anda!
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
                        <img src="https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=600&auto=format&fit=crop&q=80" 
                             alt="Nasi Ayam Bakar Madu" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-5xl bg-amber-100\'>🍗</div>'">
                        <span class="absolute top-3 left-3 bg-amber-500 text-slate-950 font-extrabold text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full shadow">
                            ⭐ Terlaris
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex justify-between items-baseline">
                                <h4 class="font-bold text-slate-900 text-base group-hover:text-amber-700 transition">Nasi Ayam Bakar Madu</h4>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Potongan ayam bakar legit manis gurih dengan lalapan segar, tahu, tempe, dan sambal terasi pedas nendang.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-base font-extrabold text-amber-700 font-mono">Rp 25.000</span>
                            <button type="button" onclick="tambahKePesanan('1 Nasi Ayam Bakar Madu')"
                                class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-800 hover:text-white font-bold text-xs transition border border-amber-200 hover:border-amber-600 flex items-center gap-1">
                                <span>+</span><span>Pesan</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Menu Item 2 -->
                <div class="bg-white rounded-3xl border border-amber-100 shadow-sm hover:shadow-xl hover:border-amber-300 transition-all duration-300 overflow-hidden flex flex-col group">
                    <div class="relative h-44 overflow-hidden bg-amber-100">
                        <img src="https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=600&auto=format&fit=crop&q=80" 
                             alt="Nasi Goreng Spesial Warung" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-5xl bg-amber-100\'>🍳</div>'">
                        <span class="absolute top-3 left-3 bg-red-600 text-white font-extrabold text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full shadow">
                            🔥 Favorit
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex justify-between items-baseline">
                                <h4 class="font-bold text-slate-900 text-base group-hover:text-amber-700 transition">Nasi Goreng Spesial</h4>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Nasi goreng wangi bumbu dapur tradisional, suwiran ayam gurih, telur ceplok mata sapi, kerupuk dan acar.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-base font-extrabold text-amber-700 font-mono">Rp 22.000</span>
                            <button type="button" onclick="tambahKePesanan('1 Nasi Goreng Spesial')"
                                class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-600 text-amber-800 hover:text-white font-bold text-xs transition border border-amber-200 hover:border-amber-600 flex items-center gap-1">
                                <span>+</span><span>Pesan</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Menu Item 3 -->
                <div class="bg-white rounded-3xl border border-amber-100 shadow-sm hover:shadow-xl hover:border-amber-300 transition-all duration-300 overflow-hidden flex flex-col group">
                    <div class="relative h-44 overflow-hidden bg-amber-100">
                        <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=600&auto=format&fit=crop&q=80" 
                             alt="Sate Ayam Madura" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-5xl bg-amber-100\'>🍢</div>'">
                        <span class="absolute top-3 left-3 bg-emerald-600 text-white font-extrabold text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full shadow">
                            👍 Mantap
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex justify-between items-baseline">
                                <h4 class="font-bold text-slate-900 text-base group-hover:text-amber-700 transition">Sate Ayam Madura</h4>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                10 tusuk sate ayam empuk dipanggang arang, disiram bumbu kacang kental gurih manis dengan irisan bawang merah.
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

                <!-- Menu Item 4 -->
                <div class="bg-white rounded-3xl border border-amber-100 shadow-sm hover:shadow-xl hover:border-amber-300 transition-all duration-300 overflow-hidden flex flex-col group">
                    <div class="relative h-44 overflow-hidden bg-amber-100">
                        <img src="https://images.unsplash.com/photo-1517256064527-09c73fc73e38?w=600&auto=format&fit=crop&q=80" 
                             alt="Es Kopi Susu Gula Aren" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-5xl bg-amber-100\'>🥤</div>'">
                        <span class="absolute top-3 left-3 bg-sky-600 text-white font-extrabold text-[10px] uppercase tracking-wider px-2.5 py-1 rounded-full shadow">
                            🧊 Minuman
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex justify-between items-baseline">
                                <h4 class="font-bold text-slate-900 text-base group-hover:text-amber-700 transition">Es Kopi Susu Aren</h4>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Paduan espresso kopi lokal, susu segar gurih, dan legitnya gula aren asli. Segar dan melegakan dahaga.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-base font-extrabold text-amber-700 font-mono">Rp 12.000</span>
                            <button type="button" onclick="tambahKePesanan('1 Es Kopi Susu Aren')"
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
                        <input type="text" name="nama" placeholder="Contoh: Budi Santoso" required
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm font-medium transition placeholder:text-slate-400">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Menu yang Dipesan & Jumlah <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="kolom_pesanan" name="menu" placeholder="Contoh: 1 Ayam Bakar, 1 Es Kopi" required
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm font-medium transition placeholder:text-slate-400">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Nomor WhatsApp / HP <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="no_hp" placeholder="Contoh: 081234567890" required
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm font-medium transition placeholder:text-slate-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alamat Pengantaran / Nomor Meja <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alamat" rows="2" placeholder="Contoh: Jl. R. Syamsudin No. 50 (Samping Apotek Sehat)" required
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
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 sm:p-8 space-y-5 relative">
            <button type="button" onclick="tutupModalAdmin()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xl font-bold p-1">
                &times;
            </button>

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl font-bold">
                    🔐
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-lg">Login Staff / Admin Warung</h3>
                    <p class="text-xs text-slate-500">Masukkan kunci otorisasi untuk membuka dashboard pesanan</p>
                </div>
            </div>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kunci Rahasia Admin (Secret Key)</label>
                    <div class="relative">
                        <input type="password" id="input_kunci_admin" name="kunci_simetris" placeholder="Masukkan Secret Key..." required
                            class="w-full pl-4 pr-10 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm font-mono transition">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-sm p-1" title="Lihat/Sembunyikan Kunci">
                            👁️
                        </button>
                    </div>
                </div>

                <button type="submit" name="masuk_admin"
                    class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-6 rounded-2xl shadow-md transition-all text-sm">
                    Masuk ke Dashboard Pesanan &rarr;
                </button>
            </form>
        </div>
    </div>

    <!-- Footer Warung -->
    <footer class="bg-white border-t border-amber-100 py-8 text-center text-xs text-slate-500 mt-12">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <p class="font-bold text-slate-700">Warung Selera Nusantara &bull; Kuliner Tradisional Asli</p>
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

        function togglePasswordVisibility() {
            const field = document.getElementById('input_kunci_admin');
            field.type = field.type === 'password' ? 'text' : 'password';
        }

        // Close modal on escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') tutupModalAdmin();
        });
    </script>
</body>
</html>
