<?php
require_once 'config/config.php';
require_once 'psnetbot.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
$pengguna_id = $_SESSION['user_id'] ?? null;

$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM pesantren WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $pesantren = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$pesantren) {
    header("HTTP/1.0 404 Not Found");
    exit($lang['pesantren_not_found'] ?? 'Pesantren tidak ditemukan.');
}

// Cek apakah pengguna adalah pengelola yang terkait dengan pesantren ini
$isPengelolaAuthorized = false;
if ($isLoggedIn && $userRole === 'pengelola') {
    $stmt = $conn->prepare("SELECT * FROM pengelola_pesantren WHERE pengguna_id = :pengguna_id AND pesantren_id = :pesantren_id");
    $stmt->execute(['pengguna_id' => $pengguna_id, 'pesantren_id' => $id]);
    $isPengelolaAuthorized = $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

// Proses edit pesantren
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn && $userRole === 'pengelola' && $isPengelolaAuthorized && !isset($_POST['delete'])) {
    $nama = $_POST['nama'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $lokasi = $_POST['lokasi'] ?? '';
    $lokasi_map = $_POST['lokasi_map'] ?? '';
    $jumlah_santri = $_POST['jumlah_santri'] ?? null;
    $tahun_berdiri = $_POST['tahun_berdiri'] ?? null;
    $akreditasi = $_POST['akreditasi'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $email = $_POST['email'] ?? '';
    $website = $_POST['website'] ?? '';
    $whatsapp = $_POST['whatsapp'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $fasilitas = $_POST['fasilitas'] ?? '';

    // Proses upload gambar
    $gambar = $pesantren['gambar'];
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid() . '.' . $file_ext;
            $upload_path = 'img/' . $new_file_name;
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $gambar = $new_file_name;
                // Hapus gambar lama jika ada
                if ($pesantren['gambar'] && file_exists('img/' . $pesantren['gambar'])) {
                    unlink('img/' . $pesantren['gambar']);
                }
            }
        }
    }

    try {
        $stmt = $conn->prepare("UPDATE pesantren SET nama = ?, kategori = ?, lokasi = ?, lokasi_map = ?, gambar = ?, jumlah_santri = ?, tahun_berdiri = ?, akreditasi = ?, telepon = ?, email = ?, website = ?, whatsapp = ?, deskripsi = ?, fasilitas = ? WHERE id = ?");
        $stmt->execute([$nama, $kategori, $lokasi, $lokasi_map, $gambar, $jumlah_santri, $tahun_berdiri, $akreditasi, $telepon, $email, $website, $whatsapp, $deskripsi, $fasilitas, $id]);
        header("Location: detailpondok.php?id=$id&success=" . urlencode($lang['update_success'] ?? 'Pesantren berhasil diperbarui.'));
        exit();
    } catch (PDOException $e) {
        die("Gagal memperbarui data: " . $e->getMessage());
    }
}

// Proses hapus pesantren
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn && $userRole === 'pengelola' && $isPengelolaAuthorized && isset($_POST['delete'])) {
    try {
        $conn->beginTransaction();

        // Hapus entri dari kolaborasi
        $stmt = $conn->prepare("DELETE FROM kolaborasi WHERE pesantren_id = ?");
        $stmt->execute([$id]);

        // Hapus entri dari pengelola_pesantren
        $stmt = $conn->prepare("DELETE FROM pengelola_pesantren WHERE pesantren_id = ?");
        $stmt->execute([$id]);

        // Hapus gambar jika ada
        if ($pesantren['gambar'] && file_exists('img/' . $pesantren['gambar']) && $pesantren['gambar'] !== 'ibsdefault.jpg') {
            unlink('img/' . $pesantren['gambar']);
        }

        // Hapus pesantren dari tabel pesantren
        $stmt = $conn->prepare("DELETE FROM pesantren WHERE id = ?");
        $stmt->execute([$id]);

        $conn->commit();
        header("Location: direktori.php?success=" . urlencode($lang['delete_success'] ?? 'Pesantren berhasil dihapus.'));
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        die("Gagal menghapus pesantren: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesantren - IBSflow</title>
    <link rel="icon" type="image/png" href="img/logo_ibsflow.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { 
            background-color: #f5f6f0; 
            color: #333; 
            line-height: 1.6; 
            overflow-x: hidden; 
        }

        /* Header */
        header { 
            background: linear-gradient(90deg, #003087 0%, #4a90e2 100%); 
            padding: 15px 30px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15); 
            position: sticky; 
            top: 0; 
            z-index: 100; 
        }
        header .logo { 
            font-size: 24px; 
            font-weight: 600; 
            color: #fff; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }
        header nav ul { 
            list-style: none; 
            display: flex; 
            gap: 15px; 
        }
        header nav ul li a { 
            color: #fff; 
            text-decoration: none; 
            font-size: 15px; 
            padding: 6px 12px; 
            border-radius: 5px; 
            transition: background 0.3s ease; 
        }
        header nav ul li a:hover { 
            background: #007bff; 
            color: #fff; 
        }

        /* Pesantren Detail */
        .pesantren-detail { 
            padding: 40px 20px; 
            max-width: 900px; 
            margin: 0 auto; 
        }
        .detail-header { 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .detail-header img { 
            width: 100%; 
            max-width: 600px; 
            height: auto; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
            margin-bottom: 15px; 
        }
        .detail-header h1 { 
            font-size: 28px; 
            font-weight: 600; 
            color: #003087; 
            margin-bottom: 10px; 
        }
        .detail-header p { 
            font-size: 16px; 
            color: #555; 
        }

        /* Button Group */
        .button-group { 
            display: flex; 
            justify-content: center; 
            gap: 10px; 
            margin-bottom: 20px; 
        }
        .cta-btn, .delete-btn { 
            padding: 8px 20px; 
            border: none; 
            border-radius: 5px; 
            font-size: 14px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: background 0.3s ease; 
        }
        .cta-btn { 
            background: #4a90e2; 
            color: #fff; 
        }
        .cta-btn:hover { 
            background: #007bff; 
            color: #fff; 
        }
        .delete-btn { 
            background: #e74c3c; 
            color: #fff; 
        }
        .delete-btn:hover { 
            background: #c0392b; 
        }

        /* Edit Form */
        .edit-form { 
            background: #fff; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
            margin-bottom: 20px; 
            display: none; 
        }
        .edit-form.active { 
            display: block; 
        }
        .edit-form input, 
        .edit-form textarea, 
        .edit-form select { 
            width: 100%; 
            padding: 8px; 
            margin-bottom: 10px; 
            border: 1px solid #b3d7ff; 
            border-radius: 5px; 
            font-size: 14px; 
            transition: border-color 0.3s ease; 
        }
        .edit-form input:focus, 
        .edit-form textarea:focus, 
        .edit-form select:focus { 
            border-color: #4a90e2; 
            outline: none; 
        }
        .edit-form textarea { 
            height: 80px; 
            resize: none; 
        }
        .edit-form button { 
            padding: 8px 20px; 
            background: #4a90e2; 
            border: none; 
            border-radius: 5px; 
            color: #fff; 
            font-weight: 600; 
            cursor: pointer; 
            transition: background 0.3s ease; 
        }
        .edit-form button:hover { 
            background: #007bff; 
            color: #fff; 
        }

        /* Alert */
        .alert { 
            background: #e6f3ff; 
            padding: 12px; 
            border-radius: 5px; 
            border: 1px solid #4a90e2; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            margin-bottom: 20px; 
            color: #003087; 
            font-size: 14px; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); 
        }
        .alert i { 
            color: #4a90e2; 
            font-size: 16px; 
        }
        .alert a { 
            color: #4a90e2; 
            font-weight: 600; 
            text-decoration: none; 
            transition: color 0.3s ease; 
        }
        .alert a:hover { 
            color: #007bff; 
        }

        /* Detail Info */
        .detail-info { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 20px; 
            margin-bottom: 20px; 
        }
        .info-card { 
            background: #fff; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
            margin-bottom: 20px; 
        }
        .info-card h3 { 
            font-size: 20px; 
            font-weight: 600; 
            color: #003087; 
            margin-bottom: 15px; 
            border-bottom: 2px solid #b3d7ff; 
            padding-bottom: 5px; 
        }
        .info-item { 
            display: flex; 
            align-items: center; 
            margin-bottom: 10px; 
            font-size: 14px; 
        }
        .info-item i { 
            font-size: 16px; 
            color: #4a90e2; 
            margin-right: 8px; 
        }
        .info-card p { 
            font-size: 14px; 
            color: #555; 
            line-height: 1.8; 
        }
        .website-link { 
            color: #4a90e2; 
            text-decoration: none; 
            transition: color 0.3s ease; 
        }
        .website-link:hover { 
            color: #007bff; 
        }

        /* Facilities */
        .fasilitas-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); 
            gap: 10px; 
        }
        .fasilitas-item { 
            display: flex; 
            align-items: center; 
            font-size: 14px; 
            color: #555; 
        }
        .fasilitas-item i { 
            font-size: 16px; 
            color: #4a90e2; 
            margin-right: 8px; 
        }

        /* Map Section */
        .map-section iframe { 
            width: 100%; 
            height: 250px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
        }

        /* Success Message */
        .success { 
            background: #28a745; 
            color: #fff; 
            padding: 10px; 
            border-radius: 5px; 
            text-align: center; 
            max-width: 500px; 
            margin: 0 auto 20px; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); 
        }

        /* Back Button */
        .back-btn { 
            display: inline-block; 
            padding: 8px 20px; 
            background: #4a90e2; 
            color: #fff; 
            text-decoration: none; 
            border-radius: 5px; 
            font-size: 14px; 
            font-weight: 600; 
            transition: background 0.3s ease; 
            text-align: center; 
            margin-top: 20px; 
        }
        .back-btn:hover { 
            background: #007bff; 
            color: #fff; 
        }

        /* Footer */
        footer { 
            background: linear-gradient(90deg, #003087 0%, #4a90e2 100%); 
            color: #fff; 
            padding: 20px; 
            text-align: center; 
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.15); 
        }
        footer a { 
            color: #f8f9fa; 
            text-decoration: none; 
            font-weight: 600; 
            transition: color 0.3s ease; 
        }
        footer a:hover { 
            color: #007bff; 
        }

        /* Responsivitas */
        @media (max-width: 768px) {
            .pesantren-detail { padding: 30px 15px; }
            .detail-info { grid-template-columns: 1fr; }
            .detail-header img { max-width: 100%; }
            .detail-header h1 { font-size: 24px; }
            .info-card h3 { font-size: 18px; }
            header { padding: 10px 15px; }
            header .logo { font-size: 20px; }
            header nav ul { gap: 10px; }
            header nav ul li a { font-size: 13px; padding: 5px 8px; }
        }
        @media (max-width: 480px) {
            .detail-header h1 { font-size: 20px; }
            .detail-header p { font-size: 14px; }
            .info-item { font-size: 13px; }
            .fasilitas-item { font-size: 13px; }
            .edit-form input, 
            .edit-form textarea, 
            .edit-form select { font-size: 13px; }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="pesantren-detail">
        <?php if (isset($_GET['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <div class="detail-header">
            <img src="<?php echo $pesantren['gambar'] ? 'img/' . htmlspecialchars($pesantren['gambar']) : 'img/ibsdefault.jpg'; ?>" alt="<?php echo htmlspecialchars($pesantren['nama']); ?>">
            <h1><?php echo htmlspecialchars($pesantren['nama']); ?></h1>
            <p>Kategori: <?php echo htmlspecialchars($pesantren['kategori']); ?></p>
        </div>

        <?php if ($isLoggedIn && $userRole === 'pengelola' && $isPengelolaAuthorized): ?>
            <div class="button-group">
                <button class="cta-btn" onclick="toggleEditForm()">Ubah Informasi</button>
                <form method="POST" action="detailpondok.php?id=<?php echo $id; ?>" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesantren ini? Tindakan ini tidak dapat dibatalkan');">
                    <input type="hidden" name="delete" value="1">
                    <button type="submit" class="delete-btn">Hapus Pesantren</button>
                </form>
            </div>
            <div class="edit-form" id="edit-form">
                <form method="POST" action="detailpondok.php?id=<?php echo $id; ?>" enctype="multipart/form-data">
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($pesantren['nama']); ?>" placeholder="Nama Pesantren" required>
                    <select name="kategori" required>
                        <option value="Tahfidz" <?php echo $pesantren['kategori'] === 'Tahfidz' ? 'selected' : ''; ?>>Tahfidz</option>
                        <option value="Riset" <?php echo $pesantren['kategori'] === 'Riset' ? 'selected' : ''; ?>>Riset</option>
                        <option value="Salafi" <?php echo $pesantren['kategori'] === 'Salafi' ? 'selected' : ''; ?>>Salafi</option>
                        <option value="Modern" <?php echo $pesantren['kategori'] === 'Modern' ? 'selected' : ''; ?>>Modern</option>
                        <option value="Wirausaha" <?php echo $pesantren['kategori'] === 'Wirausaha' ? 'selected' : ''; ?>>Wirausaha</option>
                    </select>
                    <input type="text" name="lokasi" value="<?php echo htmlspecialchars($pesantren['lokasi']); ?>" placeholder="Lokasi" required>
                    <textarea name="lokasi_map" placeholder="Kode Embed Google Maps"><?php echo htmlspecialchars($pesantren['lokasi_map']); ?></textarea>
                    <input type="file" name="gambar" accept="image/*">
                    <input type="number" name="jumlah_santri" value="<?php echo htmlspecialchars($pesantren['jumlah_santri']); ?>" placeholder="Jumlah Santri">
                    <input type="number" name="tahun_berdiri" value="<?php echo htmlspecialchars($pesantren['tahun_berdiri']); ?>" placeholder="Tahun Berdiri">
                    <input type="text" name="akreditasi" value="<?php echo htmlspecialchars($pesantren['akreditasi']); ?>" placeholder="Akreditasi">
                    <input type="text" name="telepon" value="<?php echo htmlspecialchars($pesantren['telepon']); ?>" placeholder="Telepon">
                    <input type="email" name="email" value="<?php echo htmlspecialchars($pesantren['email']); ?>" placeholder="Email">
                    <input type="text" name="website" value="<?php echo htmlspecialchars($pesantren['website']); ?>" placeholder="Website">
                    <input type="text" name="whatsapp" value="<?php echo htmlspecialchars($pesantren['whatsapp']); ?>" placeholder="Nomor WhatsApp">
                    <textarea name="deskripsi" placeholder="Deskripsi"><?php echo htmlspecialchars($pesantren['deskripsi']); ?></textarea>
                    <textarea name="fasilitas" placeholder="Fasilitas (pisahkan dengan koma)"><?php echo htmlspecialchars($pesantren['fasilitas']); ?></textarea>
                    <button type="submit">Simpan Perubahan</button>
                </form>
            </div>
        <?php elseif (!$isLoggedIn || ($isLoggedIn && $userRole === 'santri')): ?>
            <div class="alert">
                <i class="fas fa-info-circle"></i>
                <span>Silakan <a href="login-register.php">masuk</a> sebagai pengelola untuk mengubah informasi pesantren.</span>
            </div>
        <?php endif; ?>

        <div class="detail-info">
            <div class="info-card">
                <h3>Informasi Umum</h3>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Lokasi: <?php echo htmlspecialchars($pesantren['lokasi']); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-user-graduate"></i>
                    <span>Jumlah Santri: <?php echo number_format($pesantren['jumlah_santri'] ?? 0); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Tahun Berdiri: <?php echo htmlspecialchars($pesantren['tahun_berdiri'] ?? 'Tidak Tersedia'); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-star"></i>
                    <span>Akreditasi: <?php echo htmlspecialchars($pesantren['akreditasi'] ?? 'Tidak Tersedia'); ?></span>
                </div>
            </div>
            
            <div class="info-card">
                <h3>Kontak</h3>
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <span>Telepon: <?php echo htmlspecialchars($pesantren['telepon'] ?? 'Tidak Tersedia'); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <span>Email: <?php echo htmlspecialchars($pesantren['email'] ?? 'Tidak Tersedia'); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-globe"></i>
                    <span>Website:
                        <?php if ($pesantren['website']): ?>
                            <a href="<?php echo strpos($pesantren['website'], 'http') === 0 ? htmlspecialchars($pesantren['website']) : 'http://' . htmlspecialchars($pesantren['website']); ?>" target="_blank" rel="noopener noreferrer" class="website-link"><?php echo htmlspecialchars($pesantren['website']); ?></a>
                        <?php else: ?>
                            <span>Tidak Tersedia</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-item">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp: <?php echo htmlspecialchars($pesantren['whatsapp'] ?? 'Tidak Tersedia'); ?></span>
                </div>
            </div>
        </div>

        <div class="info-card">
            <h3>Deskripsi</h3>
            <p><?php echo htmlspecialchars($pesantren['deskripsi'] ?? 'Deskripsi tidak tersedia.'); ?></p>
        </div>

        <div class="info-card">
            <h3>Fasilitas</h3>
            <div class="fasilitas-grid">
                <?php
                $fasilitas = explode(',', $pesantren['fasilitas']);
                foreach ($fasilitas as $fasilitas_item):
                    $fasilitas_item = trim($fasilitas_item);
                    if (!empty($fasilitas_item)):
                ?>
                <div class="fasilitas-item">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($fasilitas_item); ?></span>
                </div>
                <?php endif; endforeach; ?>
            </div>
        </div>

        <?php if ($pesantren['lokasi_map']): ?>
        <div class="info-card map-section">
            <h3>Lokasi di Google Maps</h3>
            <?php echo $pesantren['lokasi_map']; ?>
        </div>
        <?php endif; ?>

        <a href="direktori.php" class="back-btn">Kembali ke Direktori</a>
    </main>

    <?php require_once 'footer.php'; ?>

    <script>
        function toggleEditForm() {
            const form = document.getElementById('edit-form');
            form.classList.toggle('active');
        }
    </script>
</body>
</html>