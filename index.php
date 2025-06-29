<?php
require_once 'config/config.php';
require_once 'psnetbot.php';
require_once 'header.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';

try {
    $stmt = $conn->query("SELECT * FROM pesantren LIMIT 3"); // Maksimal 3 pesantren
    $pesantrens = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Maaf, gagal mengambil data: " . htmlspecialchars($e->getMessage()) . "</div>";
    $pesantrens = [];
}

$pengguna_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn && $userRole === 'pengelola') {
    $nama = $_POST['name'] ?? '';
    $lokasi = $_POST['location'] ?? '';
    $kategori = $_POST['category'] ?? '';

    try {
        $conn->beginTransaction();
        $stmt = $conn->prepare("INSERT INTO pesantren (nama, kategori, lokasi) VALUES (?, ?, ?)");
        $stmt->execute([$nama, $kategori, $lokasi]);
        $pesantren_id = $conn->lastInsertId();

        $stmt = $conn->prepare("INSERT INTO pengelola_pesantren (pengguna_id, pesantren_id) VALUES (?, ?)");
        $stmt->execute([$pengguna_id, $pesantren_id]);

        $conn->commit();
        header("Location: index.php?success=" . urlencode($lang['registration_success']));
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "<div class='alert alert-danger'>Pendaftaran pesantren gagal: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IBSflow - Jaringan Pesantren Indonesia</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Tambahan CSS untuk scroll di contentbox */
        .contentbox {
            max-height: 200px; /* Tinggi maksimum sebelum scroll muncul */
            overflow-y: auto; /* Mengaktifkan scroll vertikal */
            padding: 10px; /* Padding untuk kenyamanan membaca */
            border: none; /* Opsional: Batas untuk memperjelas area scroll */
            border-radius: 5px; /* Opsional: Membuat sudut melengkung */
        }
        /* Styling scroll bar untuk tampilan yang lebih baik (opsional) */
        .contentbox::-webkit-scrollbar {
            width: 8px;
            display: none;
        }
        .contentbox::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 5px;
        }
        .contentbox::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 5px;
        }
        .contentbox::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        .btn-outline-primary {
        color: #ffffff !important; /* Mengubah teks menjadi putih */
        border-color: #ffffff; /* Opsional: Mengubah border menjadi putih agar konsisten */
        }
        
    </style>
</head>
<body>
<!-- Hero Section -->
<section class="hero">
    <h1>Menghubungkan Pesantren Indonesia</h1>
    <h1 id="ht1">Membangun Masa Depan Pendidikan Islam</h1>
    <p>Temukan pesantren terbaik dan bergabunglah dengan komunitas pendidikan Islam digital</p>
    <div class="d-flex gap-3">
        <a href="direktori.php" class="btn btn-primary">Jelajahi Direktori</a>
        <a href="#kontak" class="btn btn-outline-primary">Daftarkan Pesantren</a>
    </div>
</section>

<!-- Directory Section (Featured Pesantrens) -->
<section class="directory py-5">
    <div class="container">
        <h2 class="section-title" style="text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);">Pesantren Unggulan</h2>
        <?php if (empty($pesantrens)): ?>
            <p class="text-center">Belum ada pesantren yang tersedia.</p>
        <?php else: ?>
            <div class="pesantren-carousel position-relative">
                <div class="carousel-container">
                    <div class="img-box">
                        <div class="img-list">
                            <div class="img-slider">
                                <?php foreach ($pesantrens as $index => $pesantren): ?>
                                    <div class="img-item <?php echo $index === 0 ? 'active' : ''; ?>" style="--i: <?php echo $index; ?>;">
                                        <div class="item">
                                            <img src="<?php echo $pesantren['gambar'] ? 'img/' . htmlspecialchars($pesantren['gambar']) : 'img/ibsdefault.jpg'; ?>" alt="<?php echo htmlspecialchars($pesantren['nama']); ?>" loading="lazy">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="navigation">
                            <span class="prev-btn" aria-label="Pesantren Sebelumnya"><i class="fas fa-chevron-left"></i></span>
                            <span class="next-btn" aria-label="Pesantren Berikutnya"><i class="fas fa-chevron-right"></i></span>
                        </div>
                    </div>
                    <div class="info-box">
                        <?php foreach ($pesantrens as $index => $pesantren): ?>
                            <div class="info-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <h2><?php echo htmlspecialchars($pesantren['nama']); ?></h2>
                                <h2><?php echo htmlspecialchars($pesantren['kategori']); ?> - <?php echo htmlspecialchars($pesantren['lokasi']); ?></h2>
                                <p><?php echo htmlspecialchars($pesantren['deskripsi'] ?? 'Pesantren unggulan dengan pendidikan berkualitas tinggi dan nilai-nilai Islam moderat.'); ?></p>
                                <a href="detailpondok.php?id=<?php echo $pesantren['id']; ?>" class="btn">Lihat Detail</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- About Section -->
<section class="about py-5">
    <div class="container">
        <h2 class="section-title">Tentang Kami</h2>
        <div class="container">
            <div class="row g-4 justify-content-center">
                <!-- Kartu Visi -->
                <div class="col-md-4">
                    <div class="about-card">
                        <div class="imgbox">
                            <i class="fas fa-eye fa-5x" style="color: #fff; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></i>
                        </div>
                        <div class="contentbox">
                            <h2>Visi</h2>
                            <p>Menjadi jembatan digital yang menghubungkan pesantren di seluruh Indonesia untuk kolaborasi dan modernisasi pendidikan Islam</p>
                        </div>
                    </div>
                </div>
                <!-- Kartu Misi -->
                <div class="col-md-4">
                    <div class="about-card">
                        <div class="imgbox">
                            <i class="fas fa-bullseye fa-5x" style="color: #fff; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></i>
                        </div>
                        <div class="contentbox">
                            <h2>Misi</h2>
                            <p>1. Memfasilitasi jaringan antar-pesantren.<br>2. Mempromosikan keunggulan pesantren.<br>3. Mendorong keterbukaan dan pengembangan pendidikan</p>
                        </div>
                    </div>
                </div>
                <!-- Kartu Tentang Kami -->
                <div class="col-md-4">
                    <div class="about-card">
                        <div class="imgbox">
                            <i class="fas fa-info-circle fa-5x" style="color: #fff; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></i>
                        </div>
                        <div class="contentbox">
                            <h2>Tentang</h2>
                            <p>IBSflow adalah platform yang diluncurkan pada tahun 2025 untuk mendukung transformasi digital pesantren di Indonesia</p>
                            <p>IBSflow yang merupakan singkatan dari "Islamic Boarding Schools Facilitating Links, Outreach, and Wisdom", mencerminkan jembatan antara tradisi pesantren dan era digital dengan dua makna utama yang saling berkaitan. Pertama, sebagai pusat pendidikan Islam berbasis asrama, IBS menegaskan identitas kuat yang berakar pada kebijaksanaan agama, sementara Facilitating Links dan Outreach menunjukkan komitmen untuk menghubungkan pesantren secara inklusif dan memperluas akses melalui teknologi, menciptakan jaringan yang dinamis dan relevan. Kedua, makna aliran (flow) melambangkan pergerakan mulus informasi dan transformasi, di mana Wisdom tetap menjadi inti yang terjaga, mengalir ke masa depan dengan inovasi yang mendukung pendidikan dan kolaborasi antar-pesantren.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <h3 class="section-title mt-3">Tim Kami</h3>
            <div class="team-container">
                <div class="box">
                    <div class="login">
                        <div class="loginBx">
                            <h2 style="text-align: center;">Bima Pranawira<i class="fas fa-user"></i></h2>
                            <div class="group">
                                <p>Ketua Tim</p>
                            </div>
                            <img src="img/bima.jpeg" class="card-img-top mx-auto" alt="Bima Pranawira" loading="lazy">
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="login">
                        <div class="loginBx">
                            <h2 style="text-align: center;">Ronando Musyafiri<i class="fas fa-user"></i></h2>
                            <div class="group">
                                <p>Pengembang Front-End</p>
                            </div>
                            <img src="img/nando1.jpg" class="card-img-top mx-auto" alt="Ronando Musyafiri" loading="lazy">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact py-5" id="kontak">
    <div class="container">
        <h2 class="section-title">Kontak & Pendaftaran</h2>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card contact-card p-4">
                    <h3 class="fs-4">Daftarkan Pesantren Anda</h3>
                    <?php if ($isLoggedIn && $userRole === 'pengelola'): ?>
                        <form method="POST" action="index.php">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Pesantren</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Nama Pesantren" required>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Lokasi</label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="Lokasi" required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Kategori</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="Tahfidz">Tahfidz</option>
                                    <option value="Riset">Riset</option>
                                    <option value="Salafi">Salafi</option>
                                    <option value="Modern">Modern</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Kirim Pendaftaran</button>
                        </form>
                    <?php else: ?>
                        <div class="alert d-flex align-items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            <span>Silakan <a href="login-register.php">masuk</a> sebagai pengelola untuk mendaftarkan pesantren</span>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert success">Pesantren berhasil didaftarkan</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card contact-card p-4">
                    <h3 class="fs-4">Hubungi Kami</h3>
                    <p>
                        <i class="fas fa-envelope" aria-label="Email: ibsflow@gmail.com"></i>
                        <a href="mailto:ibsflow@gmail.com">ibsflow@gmail.com</a>
                    </p>
                    <p>
                        <i class="fas fa-phone" aria-label="Telepon: +62 831-2864-5918"></i>
                        +62 831-2864-5918
                    </p>
                    <p>
                        <i class="fas fa-map-marker-alt" aria-label="Alamat: Jl. Walisongo No.1, Semarang, Jawa Tengah 50185"></i>
                        Jl. Walisongo No.1, Semarang, Jawa Tengah 50185
                    </p>
                    <div class="d-flex flex-column flex-md-row gap-3">
                        <div>
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3959.948!2d110.413345!3d-6.974694!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e708b4d8f1b1f3b%3A0x4c8b2b1e1f2b3c4d!2sUIN%20Walisongo%20Semarang!5e0!3m2!1sid!2sid!4v1747100267009!5m2!1sid!2sid" width="250" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <a href="https://instagram.com/ibsflow.id" target="_blank"><i class="fab fa-instagram fa-2x"></i></a>
                                <span>ibsflow.id</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="https://wa.me/6283128645918" target="_blank"><i class="fab fa-whatsapp fa-2x"></i></a>
                                <span>+62 831-2864-5918</span>
                            </div>
                            <div class="mt-3 text-center">
                                <p>Dukung kami dengan donasi untuk pengembangan IBSflow</p>
                                <img src="img/donate.jpeg" alt="Kode QR Donasi" style="width: 150px; height: auto;" loading="lazy">
                                <span class="d-block text-primary fw-bold">Pindai di Sini</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <p>Buku Panduan IBSflow: 
                            <a href="https://drive.google.com/file/d/1nw2hySJF9OOUihkwk3PeFLn4XSVq6nsC/view?usp=drive_link"><span>Klik di Sini</span></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>
</body>
</html>