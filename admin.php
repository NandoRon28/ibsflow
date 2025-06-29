<?php
require_once 'config/config.php';
require_once 'psnetbot.php';

// Inisialisasi variabel $action
$action = $_POST['action'] ?? '';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';

if (!$isLoggedIn || $userRole !== 'admin') {
    header("Location: login-register.php?error=Akses ditolak. Hanya admin yang diperbolehkan.");
    exit;
}

// Generate CSRF token untuk keamanan formulir
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Proses semua aksi POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token tidak valid.");
    }

    // Sanitasi input
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    $pesantren_id = filter_input(INPUT_POST, 'pesantren_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $thread_id = filter_input(INPUT_POST, 'thread_id', FILTER_SANITIZE_NUMBER_INT);
    $kegiatan_id = filter_input(INPUT_POST, 'kegiatan_id', FILTER_SANITIZE_NUMBER_INT);
    $kolaborasi_id = filter_input(INPUT_POST, 'kolaborasi_id', FILTER_SANITIZE_NUMBER_INT);

    try {
        // Hapus Thread
        if ($action === 'deleteThread') {
            $stmt = $conn->prepare("DELETE FROM thread WHERE id = ?");
            $stmt->execute([$thread_id]);
            header("Location: admin.php?success=Thread berhasil dihapus.");
            exit;
        }
        // Tambah Thread
        elseif ($action === 'addThread') {
            $judul = filter_input(INPUT_POST, 'judul', FILTER_SANITIZE_STRING);
            $konten = filter_input(INPUT_POST, 'konten', FILTER_SANITIZE_STRING);
            $stmt = $conn->prepare("INSERT INTO thread (judul, konten, pengguna_id) VALUES (?, ?, ?)");
            $stmt->execute([$judul, $konten, $_SESSION['user_id']]);
            header("Location: admin.php?success=Thread berhasil ditambahkan.");
            exit;
        }
        // Edit Thread
        elseif ($action === 'editThread') {
            $judul = filter_input(INPUT_POST, 'judul', FILTER_SANITIZE_STRING);
            $konten = filter_input(INPUT_POST, 'konten', FILTER_SANITIZE_STRING);
            $stmt = $conn->prepare("UPDATE thread SET judul = ?, konten = ? WHERE id = ?");
            $stmt->execute([$judul, $konten, $thread_id]);
            header("Location: admin.php?success=" . urlencode($lang['update_success']));
            exit;
        }
        // Update Pesantren
        elseif ($action === 'updatePesantren') {
            $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING);
            $kategori = filter_input(INPUT_POST, 'kategori', FILTER_SANITIZE_STRING);
            $lokasi = filter_input(INPUT_POST, 'lokasi', FILTER_SANITIZE_STRING);
            $lokasi_map = $_POST['lokasi_map'] ?? ''; // Ambil tanpa sanitasi berlebihan
            $jumlah_santri = filter_input(INPUT_POST, 'jumlah_santri', FILTER_SANITIZE_NUMBER_INT);
            $tahun_berdiri = filter_input(INPUT_POST, 'tahun_berdiri', FILTER_SANITIZE_NUMBER_INT);
            $akreditasi = filter_input(INPUT_POST, 'akreditasi', FILTER_SANITIZE_STRING);
            $telepon = filter_input(INPUT_POST, 'telepon', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $website = filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL);
            $whatsapp = filter_input(INPUT_POST, 'whatsapp', FILTER_SANITIZE_STRING);
            $deskripsi = filter_input(INPUT_POST, 'deskripsi', FILTER_SANITIZE_STRING);
            $fasilitas = filter_input(INPUT_POST, 'fasilitas', FILTER_SANITIZE_STRING);

            // Validasi iframe
            if (!empty($lokasi_map) && strpos($lokasi_map, 'https://www.google.com/maps/embed') === false) {
                die("Input lokasi_map tidak valid. Harus berupa iframe Google Maps.");
            }

            // Ambil data pesantren yang ada untuk mendapatkan gambar saat ini
            $stmt = $conn->prepare("SELECT gambar FROM pesantren WHERE id = ?");
            $stmt->execute([$pesantren_id]);
            $currentPesantren = $stmt->fetch(PDO::FETCH_ASSOC);
            $gambar = $currentPesantren['gambar'] ?? null;

            // Proses upload gambar jika ada
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
                        if ($currentPesantren['gambar'] && file_exists('img/' . $currentPesantren['gambar']) && $currentPesantren['gambar'] !== 'psdefault.jpg') {
                            unlink('img/' . $currentPesantren['gambar']);
                        }
                    }
                }
            }

            $stmt = $conn->prepare("UPDATE pesantren SET nama = ?, kategori = ?, lokasi = ?, lokasi_map = ?, gambar = ?, jumlah_santri = ?, tahun_berdiri = ?, akreditasi = ?, telepon = ?, email = ?, website = ?, whatsapp = ?, deskripsi = ?, fasilitas = ? WHERE id = ?");
            $stmt->execute([$nama, $kategori, $lokasi, $lokasi_map, $gambar, $jumlah_santri, $tahun_berdiri, $akreditasi, $telepon, $email, $website, $whatsapp, $deskripsi, $fasilitas, $pesantren_id]);
            header("Location: admin.php?success=" . urlencode($lang['update_success']));
            exit;
        }
        // Tambah Pesantren
        elseif ($action === 'addPesantren') {
            $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING);
            $kategori = filter_input(INPUT_POST, 'kategori', FILTER_SANITIZE_STRING);
            $lokasi = filter_input(INPUT_POST, 'lokasi', FILTER_SANITIZE_STRING);
            $lokasi_map = $_POST['lokasi_map'] ?? ''; // Ambil tanpa sanitasi berlebihan
            $jumlah_santri = filter_input(INPUT_POST, 'jumlah_santri', FILTER_SANITIZE_NUMBER_INT);
            $tahun_berdiri = filter_input(INPUT_POST, 'tahun_berdiri', FILTER_SANITIZE_NUMBER_INT);
            $akreditasi = filter_input(INPUT_POST, 'akreditasi', FILTER_SANITIZE_STRING);
            $telepon = filter_input(INPUT_POST, 'telepon', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $website = filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL);
            $whatsapp = filter_input(INPUT_POST, 'whatsapp', FILTER_SANITIZE_STRING);
            $deskripsi = filter_input(INPUT_POST, 'deskripsi', FILTER_SANITIZE_STRING);
            $fasilitas = filter_input(INPUT_POST, 'fasilitas', FILTER_SANITIZE_STRING);

            // Validasi iframe
            if (!empty($lokasi_map) && strpos($lokasi_map, 'https://www.google.com/maps/embed') === false) {
                die("Input lokasi_map tidak valid. Harus berupa iframe Google Maps.");
            }

            $gambar = 'psdefault.jpg'; // Gambar default
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
                    }
                }
            }

            $stmt = $conn->prepare("INSERT INTO pesantren (nama, kategori, lokasi, lokasi_map, gambar, jumlah_santri, tahun_berdiri, akreditasi, telepon, email, website, whatsapp, deskripsi, fasilitas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $kategori, $lokasi, $lokasi_map, $gambar, $jumlah_santri, $tahun_berdiri, $akreditasi, $telepon, $email, $website, $whatsapp, $deskripsi, $fasilitas]);
            header("Location: admin.php?success=Pesantren berhasil ditambahkan.");
            exit;
        }
        // Hapus Pesantren
        elseif ($action === 'deletePesantren') {
            // Ambil data pesantren untuk mendapatkan gambar
            $stmt = $conn->prepare("SELECT gambar FROM pesantren WHERE id = ?");
            $stmt->execute([$pesantren_id]);
            $pesantren = $stmt->fetch(PDO::FETCH_ASSOC);

            // Hapus gambar jika ada
            if ($pesantren['gambar'] && file_exists('img/' . $pesantren['gambar']) && $pesantren['gambar'] !== 'psdefault.jpg') {
                unlink('img/' . $pesantren['gambar']);
            }

            $stmt = $conn->prepare("DELETE FROM pesantren WHERE id = ?");
            $stmt->execute([$pesantren_id]);
            header("Location: admin.php?success=Pesantren berhasil dihapus.");
            exit;
        }
        // Tambah Pengguna
        elseif ($action === 'addUser') {
            $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = password_hash(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING), PASSWORD_DEFAULT);
            $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
            $stmt = $conn->prepare("INSERT INTO pengguna (nama, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama, $email, $password, $role]);
            header("Location: admin.php?success=Pengguna berhasil ditambahkan.");
            exit;
        }
        // Edit Pengguna
        elseif ($action === 'editUser') {
            $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
            $stmt = $conn->prepare("UPDATE pengguna SET nama = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$nama, $email, $role, $user_id]);
            header("Location: admin.php?success=" . urlencode($lang['update_success']));
            exit;
        }
        // Hapus Pengguna
        elseif ($action === 'deleteUser') {
            $stmt = $conn->prepare("DELETE FROM pengguna WHERE id = ?");
            $stmt->execute([$user_id]);
            header("Location: admin.php?success=Pengguna berhasil dihapus.");
            exit;
        }
        // Tambah Kegiatan
        elseif ($action === 'addKegiatan') {
            $judul = filter_input(INPUT_POST, 'judul', FILTER_SANITIZE_STRING);
            $deskripsi = filter_input(INPUT_POST, 'deskripsi', FILTER_SANITIZE_STRING);
            $tanggal = filter_input(INPUT_POST, 'tanggal', FILTER_SANITIZE_STRING);
            $penyelenggara = filter_input(INPUT_POST, 'penyelenggara', FILTER_SANITIZE_STRING);
            $tempat = filter_input(INPUT_POST, 'tempat', FILTER_SANITIZE_STRING);
            $jumlah_peserta = filter_input(INPUT_POST, 'jumlah_peserta', FILTER_SANITIZE_NUMBER_INT);

            $gambar = 'default.jpg'; // Gambar default
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
                    }
                }
            }

            $stmt = $conn->prepare("INSERT INTO kegiatan (judul, deskripsi, tanggal, penyelenggara, tempat, jumlah_peserta, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$judul, $deskripsi, $tanggal, $penyelenggara, $tempat, $jumlah_peserta, $gambar]);
            header("Location: admin.php?success=Kegiatan berhasil ditambahkan.");
            exit;
        }
        // Edit Kegiatan
        elseif ($action === 'editKegiatan') {
            $judul = filter_input(INPUT_POST, 'judul', FILTER_SANITIZE_STRING);
            $deskripsi = filter_input(INPUT_POST, 'deskripsi', FILTER_SANITIZE_STRING);
            $tanggal = filter_input(INPUT_POST, 'tanggal', FILTER_SANITIZE_STRING);
            $penyelenggara = filter_input(INPUT_POST, 'penyelenggara', FILTER_SANITIZE_STRING);
            $tempat = filter_input(INPUT_POST, 'tempat', FILTER_SANITIZE_STRING);
            $jumlah_peserta = filter_input(INPUT_POST, 'jumlah_peserta', FILTER_SANITIZE_NUMBER_INT);

            // Ambil data kegiatan yang ada untuk mendapatkan gambar saat ini
            $stmt = $conn->prepare("SELECT gambar FROM kegiatan WHERE id = ?");
            $stmt->execute([$kegiatan_id]);
            $currentKegiatan = $stmt->fetch(PDO::FETCH_ASSOC);
            $gambar = $currentKegiatan['gambar'] ?? 'default.jpg';

            // Proses upload gambar jika ada
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
                        if ($currentKegiatan['gambar'] && file_exists('img/' . $currentKegiatan['gambar']) && $currentKegiatan['gambar'] !== 'default.jpg') {
                            unlink('img/' . $currentKegiatan['gambar']);
                        }
                    }
                }
            }

            $stmt = $conn->prepare("UPDATE kegiatan SET judul = ?, deskripsi = ?, tanggal = ?, penyelenggara = ?, tempat = ?, jumlah_peserta = ?, gambar = ? WHERE id = ?");
            $stmt->execute([$judul, $deskripsi, $tanggal, $penyelenggara, $tempat, $jumlah_peserta, $gambar, $kegiatan_id]);
            header("Location: admin.php?success=" . urlencode($lang['update_success']));
            exit;
        }
        // Hapus Kegiatan
        elseif ($action === 'deleteKegiatan') {
            // Ambil data kegiatan untuk mendapatkan gambar
            $stmt = $conn->prepare("SELECT gambar FROM kegiatan WHERE id = ?");
            $stmt->execute([$kegiatan_id]);
            $kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

            // Hapus gambar jika ada
            if ($kegiatan['gambar'] && file_exists('img/' . $kegiatan['gambar']) && $kegiatan['gambar'] !== 'default.jpg') {
                unlink('img/' . $kegiatan['gambar']);
            }

            $stmt = $conn->prepare("DELETE FROM kegiatan WHERE id = ?");
            $stmt->execute([$kegiatan_id]);
            header("Location: admin.php?success=Kegiatan berhasil dihapus.");
            exit;
        }
        // Edit Kolaborasi (Promosi)
        elseif ($action === 'editKolaborasi') {
            $judul = filter_input(INPUT_POST, 'judul', FILTER_SANITIZE_STRING);
            $deskripsi = filter_input(INPUT_POST, 'deskripsi', FILTER_SANITIZE_STRING);
            $stmt = $conn->prepare("UPDATE kolaborasi SET judul = ?, deskripsi = ? WHERE id = ?");
            $stmt->execute([$judul, $deskripsi, $kolaborasi_id]);
            header("Location: admin.php?success=" . urlencode($lang['update_success']));
            exit;
        }
        // Hapus Kolaborasi (Promosi)
        elseif ($action === 'deleteKolaborasi') {
            $stmt = $conn->prepare("DELETE FROM kolaborasi WHERE id = ?");
            $stmt->execute([$kolaborasi_id]);
            header("Location: admin.php?success=Promosi berhasil dihapus.");
            exit;
        }
        // Tambah Kolaborasi (Promosi)
        elseif ($action === 'addKolaborasi') {
            $judul = filter_input(INPUT_POST, 'judul', FILTER_SANITIZE_STRING);
            $deskripsi = filter_input(INPUT_POST, 'deskripsi', FILTER_SANITIZE_STRING);
            $tipe = filter_input(INPUT_POST, 'tipe', FILTER_SANITIZE_STRING);
            $stmt = $conn->prepare("INSERT INTO kolaborasi (pengguna_id, pesantren_id, judul, deskripsi, tipe) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], null, $judul, $deskripsi, $tipe]);
            header("Location: admin.php?success=Promosi berhasil ditambahkan.");
            exit;
        }
        // Verifikasi Pengguna
        elseif ($action === 'verifyUser') {
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
            $stmt = $conn->prepare("UPDATE pengguna SET status = 'verified' WHERE id = ?");
            $stmt->execute([$user_id]);
            header("Location: admin.php?success=Pengguna berhasil diverifikasi.");
            exit;
        }
        // Tolak Pengguna
        elseif ($action === 'rejectUser') {
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
            $stmt = $conn->prepare("UPDATE pengguna SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$user_id]);
            header("Location: admin.php?success=Pengguna ditolak.");
            exit;
        }
    } catch (PDOException $e) {
        die("Gagal memproses aksi: " . $e->getMessage());
    }
}

// Ambil data untuk ditampilkan
try {
    $threads = $conn->query("SELECT t.*, p.nama as pengguna_nama FROM thread t JOIN pengguna p ON t.pengguna_id = p.id")->fetchAll(PDO::FETCH_ASSOC);
    $pesantren = $conn->query("SELECT * FROM pesantren")->fetchAll(PDO::FETCH_ASSOC);
    $users = $conn->query("SELECT * FROM pengguna WHERE status = 'pending' OR status = 'verified'")->fetchAll(PDO::FETCH_ASSOC);
    $kegiatan = $conn->query("SELECT * FROM kegiatan")->fetchAll(PDO::FETCH_ASSOC);
    $kolaborasi = $conn->query("SELECT k.*, p.nama as pesantren_nama FROM kolaborasi k LEFT JOIN pesantren p ON k.pesantren_id = p.id")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Gagal mengambil data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($selectedLang); ?>" dir="<?php echo $selectedLang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - PSNet</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: #f5f6f0; color: #333; line-height: 1.6; }
        [dir="rtl"] { text-align: right; }
        [dir="rtl"] .tabs { flex-direction: row-reverse; }
        [dir="rtl"] .tab-content { text-align: right; }
        [dir="rtl"] .form-group label { text-align: right; }
        [dir="rtl"] .action-buttons { flex-direction: row-reverse; }
        header { background: linear-gradient(90deg, #1a3c34 0%, #2e856e 100%); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); position: sticky; top: 0; z-index: 100; }
        header .logo { font-size: 28px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 1px; }
        header nav ul { list-style: none; display: flex; gap: 25px; }
        header nav ul li a { color: #fff; text-decoration: none; font-size: 16px; padding: 8px 15px; border-radius: 5px; transition: background 0.3s, color 0.3s; }
        header nav ul li a:hover { background: #f4c430; color: #1a3c34; }
        .admin { max-width: 1200px; margin: 40px auto; padding: 20px; }
        h1 { font-size: 36px; color: #1a3c34; text-align: center; margin-bottom: 30px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 30px; justify-content: center; flex-wrap: wrap; }
        .tab { padding: 12px 25px; background: #d4e9e2; color: #1a3c34; cursor: pointer; border-radius: 25px; transition: all 0.3s; font-weight: bold; }
        .tab:hover { background: #2e856e; color: #fff; }
        .tab.active { background: #2e856e; color: #fff; }
        .tab-content { display: none; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
        .tab-content.active { display: block; }
        h2 { font-size: 28px; color: #1a3c34; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; color: #1a3c34; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #d4e9e2; border-radius: 5px; background: #f5f6f0; }
        textarea { height: 100px; resize: none; }
        .submit-btn { padding: 10px 20px; background: #f4c430; border: none; border-radius: 25px; color: #1a3c34; cursor: pointer; transition: transform 0.3s; font-weight: bold; }
        .submit-btn:hover { transform: scale(1.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; border-bottom: 1px solid #d4e9e2; text-align: left; }
        th { background: #2e856e; color: #fff; font-weight: bold; }
        .action-buttons { display: flex; gap: 10px; }
        .edit-btn, .delete-btn { padding: 8px; border: none; border-radius: 5px; cursor: pointer; transition: all 0.3s; }
        .edit-btn { background: #2e856e; color: #fff; }
        .edit-btn:hover { background: #f4c430; color: #1a3c34; }
        .delete-btn { background: #ff4d4d; color: #fff; }
        .delete-btn:hover { background: #cc0000; }
        .edit-form { display: none; margin-top: 15px; }
        .edit-form.active { display: block; }
        .success { background: #2ecc71; color: #fff; padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; }
        footer { background: linear-gradient(90deg, #1a3c34 0%, #2e856e 100%); color: #fff; padding: 30px; text-align: center; }
        footer a { color: #f4c430; text-decoration: none; }
        @media (max-width: 768px) {
            .admin { padding: 10px; }
            .tabs { flex-direction: column; align-items: center; }
            .tab { width: 100%; text-align: center; }
            table { font-size: 14px; }
            th, td { padding: 10px; }
            .form-group { width: 100%; }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="admin">
        <h1>Dashboard Admin</h1>
        <?php if (isset($_GET['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" data-tab="pesantren">Pesantren</div>
            <div class="tab" data-tab="pengguna">Pengguna</div>
            <div class="tab" data-tab="kegiatan">Kegiatan</div>
            <div class="tab" data-tab="promosi">Promosi</div>
            <div class="tab" data-tab="forum">Forum</div>
        </div>

        <!-- Tab Pesantren -->
        <div class="tab-content active" id="pesantren">
            <h2>Kelola Pesantren</h2>
            <form method="POST" action="admin.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="addPesantren">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label for="nama">Pesantren Name</label>
                    <input type="text" name="nama" placeholder="Pesantren Name" required>
                </div>
                <div class="form-group">
                    <label for="kategori">category</label>
                    <select name="kategori" required>
                        <option value="Tahfidz">Tahfidz</option>
                        <option value="Riset">Riset</option>
                        <option value="Salafi">Salafi</option>
                        <option value="Modern">Modern</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="lokasi">Location</label>
                    <input type="text" name="lokasi" placeholder="Location" required>
                </div>
                <div class="form-group">
                    <label for="lokasi_map">Map Location</label>
                    <textarea name="lokasi_map" placeholder="Enter google map embed of your pesantren location"></textarea>
                </div>
                <div class="form-group">
                    <label for="gambar">Pesantren Image</label>
                    <input type="file" name="gambar" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="jumlah_santri">Number of students</label>
                    <input type="number" name="jumlah_santri" placeholder="Enter your Pesantren number of students">
                </div>
                <div class="form-group">
                    <label for="tahun_berdiri">The year of establishment</label>
                    <input type="number" name="tahun_berdiri" placeholder="The year of establishment">
                </div>
                <div class="form-group">
                    <label for="akreditasi">Accreditation</label>
                    <input type="text" name="akreditasi" placeholder="Accreditation">
                </div>
                <div class="form-group">
                    <label for="telepon">Phone</label>
                    <input type="text" name="telepon" placeholder="Phone">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" placeholder="Email">
                </div>
                <div class="form-group">
                    <label for="website">Website</label>
                    <input type="text" name="website" placeholder="Website">
                </div>
                <div class="form-group">
                    <label for="whatsapp">WhatsApp Number</label>
                    <input type="text" name="whatsapp" placeholder="WhatsApp Number">
                </div>
                <div class="form-group">
                    <label for="deskripsi">Description</label>
                    <textarea name="deskripsi" placeholder="Description"></textarea>
                </div>
                <div class="form-group">
                    <label for="fasilitas">Facilities</label>
                    <textarea name="fasilitas" placeholder="Facilities"></textarea>
                </div>
                <button type="submit" class="submit-btn">Tambah Pesantren</button>
            </form>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Lokasi</th>
                    <th>Jumlah Santri</th>
                    <th>Tahun Berdiri</th>
                    <th>Akreditasi</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Aksi</th>
                </tr>
                <?php foreach ($pesantren as $p): ?>
                <tr>
                    <td><?php echo $p['id']; ?></td>
                    <td><?php echo htmlspecialchars($p['nama']); ?></td>
                    <td><?php echo htmlspecialchars($p['kategori']); ?></td>
                    <td><?php echo htmlspecialchars($p['lokasi']); ?></td>
                    <td><?php echo htmlspecialchars($p['jumlah_santri'] ?? $lang['not_available']); ?></td>
                    <td><?php echo htmlspecialchars($p['tahun_berdiri'] ?? $lang['not_available']); ?></td>
                    <td><?php echo htmlspecialchars($p['akreditasi'] ?? $lang['not_available']); ?></td>
                    <td><?php echo htmlspecialchars($p['telepon'] ?? $lang['not_available']); ?></td>
                    <td><?php echo htmlspecialchars($p['email'] ?? $lang['not_available']); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button type="button" class="edit-btn" onclick="toggleEditForm(this, 'pesantren-<?php echo $p['id']; ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="admin.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this pesantren? This action cannot be undone');">
                                <input type="hidden" name="action" value="deletePesantren">
                                <input type="hidden" name="pesantren_id" value="<?php echo $p['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <button type="submit" class="delete-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="10">
                        <form method="POST" action="admin.php" class="edit-form" id="edit-pesantren-<?php echo $p['id']; ?>" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="updatePesantren">
                            <input type="hidden" name="pesantren_id" value="<?php echo $p['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="form-group">
                                <label for="nama">Name</label>
                                <input type="text" name="nama" value="<?php echo htmlspecialchars($p['nama']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="kategori">Category</label>
                                <select name="kategori" required>
                                    <option value="Tahfidz" <?php echo $p['kategori'] === 'Tahfidz' ? 'selected' : ''; ?>>Tahfidz</option>
                                    <option value="Riset" <?php echo $p['kategori'] === 'Riset' ? 'selected' : ''; ?>>Riset</option>
                                    <option value="Salafi" <?php echo $p['kategori'] === 'Salafi' ? 'selected' : ''; ?>>Salafi</option>
                                    <option value="Modern" <?php echo $p['kategori'] === 'Modern' ? 'selected' : ''; ?>>Modern</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="lokasi">Location</label>
                                <input type="text" name="lokasi" value="<?php echo htmlspecialchars($p['lokasi']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="lokasi_map">Map Location</label>
                                <textarea name="lokasi_map"><?php echo htmlspecialchars($p['lokasi_map'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="gambar">Pesantren Image</label>
                                <input type="file" name="gambar" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label for="jumlah_santri">Number of students</label>
                                <input type="number" name="jumlah_santri" value="<?php echo htmlspecialchars($p['jumlah_santri'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="tahun_berdiri">The year of establishment</label>
                                <input type="number" name="tahun_berdiri" value="<?php echo htmlspecialchars($p['tahun_berdiri'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="akreditasi">Accreditation</label>
                                <input type="text" name="akreditasi" value="<?php echo htmlspecialchars($p['akreditasi'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="telepon">Phone</label>
                                <input type="text" name="telepon" value="<?php echo htmlspecialchars($p['telepon'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($p['email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="website">Website</label>
                                <input type="text" name="website" value="<?php echo htmlspecialchars($p['website'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="whatsapp">WhatsApp Number</label>
                                <input type="text" name="whatsapp" value="<?php echo htmlspecialchars($p['whatsapp'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="deskripsi">Description</label>
                                <textarea name="deskripsi"><?php echo htmlspecialchars($p['deskripsi'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="fasilitas">Facilities</label>
                                <textarea name="fasilitas"><?php echo htmlspecialchars($p['fasilitas'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="submit-btn">Save changes</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Tab Pengguna -->
        <div class="tab-content" id="pengguna">
            <h2>Kelola Pengguna</h2>
            <h3>Pendaftaran Pending</h3>
            <table>
                <tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Dokumen Verifikasi</th><th>Aksi</th></tr>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['nama']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo htmlspecialchars($user['status']); ?></td>
                    <td><?php if ($user['verification_doc']): ?><a href="Uploads/verification/<?php echo htmlspecialchars($user['verification_doc']); ?>" target="_blank">Lihat Dokumen</a><?php else: ?>Tidak ada<?php endif; ?></td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($user['status'] === 'pending'): ?>
                                <form method="POST" action="admin.php" style="display:inline;" onsubmit="return confirm('Verifikasi akun ini?');">
                                    <input type="hidden" name="action" value="verifyUser">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <button type="submit" class="edit-btn">Verifikasi</button>
                                </form>
                                <form method="POST" action="admin.php" style="display:inline;" onsubmit="return confirm('Tolak akun ini?');">
                                    <input type="hidden" name="action" value="rejectUser">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <button type="submit" class="delete-btn">Tolak</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <form method="POST" action="admin.php">
                <input type="hidden" name="action" value="addUser">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label for="nama">Nama Pengguna</label>
                    <input type="text" name="nama" placeholder="Nama Pengguna" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" required>
                        <option value="santri">Santri</option>
                        <option value="pengelola">Pengelola</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="submit-btn">Tambah Pengguna</button>
            </form>
            <table>
                <tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['nama']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button type="button" class="edit-btn" onclick="toggleEditForm(this, 'user-<?php echo $user['id']; ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="admin.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this pesantren? This action cannot be undone');">
                                <input type="hidden" name="action" value="deleteUser">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <button type="submit" class="delete-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        <form method="POST" action="admin.php" class="edit-form" id="edit-user-<?php echo $user['id']; ?>">
                            <input type="hidden" name="action" value="editUser">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="form-group">
                                <label for="nama">Nama Pengguna</label>
                                <input type="text" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select name="role" required>
                                    <option value="santri" <?php echo $user['role'] === 'santri' ? 'selected' : ''; ?>>Santri</option>
                                    <option value="pengelola" <?php echo $user['role'] === 'pengelola' ? 'selected' : ''; ?>>Pengelola</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <button type="submit" class="submit-btn">Simpan Perubahan</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Tab Kegiatan -->
        <div class="tab-content" id="kegiatan">
            <h2>Kelola Kegiatan</h2>
            <form method="POST" action="admin.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="addKegiatan">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label for="judul">Judul Kegiatan</label>
                    <input type="text" name="judul" placeholder="Judul Kegiatan" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Kegiatan</label>
                    <textarea name="deskripsi" placeholder="Deskripsi Kegiatan" required></textarea>
                </div>
                <div class="form-group">
                    <label for="tanggal">Date</label>
                    <input type="date" name="tanggal" required>
                </div>
                <div class="form-group">
                    <label for="penyelenggara">Organizer</label>
                    <input type="text" name="penyelenggara" placeholder="Organizer">
                </div>
                <div class="form-group">
                    <label for="tempat">Place/Event Location</label>
                    <input type="text" name="tempat" placeholder="Place/Event Location">
                </div>
                <div class="form-group">
                    <label for="jumlah_peserta">Number of Participant</label>
                    <input type="number" name="jumlah_peserta" placeholder="Number of Participant">
                </div>
                <div class="form-group">
                    <label for="gambar">Pesantren Image</label>
                    <input type="file" name="gambar" accept="image/*">
                </div>
                <button type="submit" class="submit-btn">Tambah Kegiatan</button>
            </form>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Deskripsi</th>
                    <th>Tanggal</th>
                    <th>Penyelenggara</th>
                    <th>Tempat</th>
                    <th>Jumlah Peserta</th>
                    <th>Aksi</th>
                </tr>
                <?php foreach ($kegiatan as $k): ?>
                <tr>
                    <td><?php echo $k['id']; ?></td>
                    <td><?php echo htmlspecialchars($k['judul']); ?></td>
                    <td><?php echo htmlspecialchars($k['deskripsi']); ?></td>
                    <td><?php echo htmlspecialchars($k['tanggal']); ?></td>
                    <td><?php echo htmlspecialchars($k['penyelenggara'] ?? $lang['not_available']); ?></td>
                    <td><?php echo htmlspecialchars($k['tempat'] ?? $lang['not_available']); ?></td>
                    <td><?php echo htmlspecialchars($k['jumlah_peserta'] ?? $lang['not_available']); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button type="button" class="edit-btn" onclick="toggleEditForm(this, 'kegiatan-<?php echo $k['id']; ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="admin.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this pesantren? This action cannot be undone');">
                                <input type="hidden" name="action" value="deleteKegiatan">
                                <input type="hidden" name="kegiatan_id" value="<?php echo $k['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <button type="submit" class="delete-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="8">
                        <form method="POST" action="admin.php" class="edit-form" id="edit-kegiatan-<?php echo $k['id']; ?>" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="editKegiatan">
                            <input type="hidden" name="kegiatan_id" value="<?php echo $k['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="form-group">
                                <label for="judul">Judul Kegiatan</label>
                                <input type="text" name="judul" value="<?php echo htmlspecialchars($k['judul']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="deskripsi">Deskripsi Kegiatan</label>
                                <textarea name="deskripsi" required><?php echo htmlspecialchars($k['deskripsi']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="tanggal">Date</label>
                                <input type="date" name="tanggal" value="<?php echo htmlspecialchars($k['tanggal']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="penyelenggara">Organizer</label>
                                <input type="text" name="penyelenggara" value="<?php echo htmlspecialchars($k['penyelenggara'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="tempat">Place/Event Location</label>
                                <input type="text" name="tempat" value="<?php echo htmlspecialchars($k['tempat'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="jumlah_peserta">Number of Participant</label>
                                <input type="number" name="jumlah_peserta" value="<?php echo htmlspecialchars($k['jumlah_peserta'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="gambar">Pesantren Image</label>
                                <input type="file" name="gambar" accept="image/*">
                            </div>
                            <button type="submit" class="submit-btn">Save_changes</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Tab Promosi -->
        <div class="tab-content" id="promosi">
            <h2>Kelola Promosi</h2>
            <form method="POST" action="admin.php">
                <input type="hidden" name="action" value="addKolaborasi">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label for="judul">Judul Promosi</label>
                    <input type="text" name="judul" placeholder="Judul Promosi" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Promosi</label>
                    <textarea name="deskripsi" placeholder="Deskripsi Promosi" required></textarea>
                </div>
                <input type="hidden" name="tipe" value="promosi">
                <button type="submit" class="submit-btn">Tambah Promosi</button>
            </form>
            <table>
                <tr><th>ID</th><th>Judul</th><th>Deskripsi</th><th>Pesantren</th><th>Tipe</th><th>Tanggal Dibuat</th><th>Aksi</th></tr>
                <?php foreach ($kolaborasi as $k): ?>
                <tr>
                    <td><?php echo $k['id']; ?></td>
                    <td><?php echo htmlspecialchars($k['judul']); ?></td>
                    <td><?php echo htmlspecialchars($k['deskripsi']); ?></td>
                    <td><?php echo htmlspecialchars($k['pesantren_nama'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($k['tipe'] ?? 'kolaborasi'); ?></td>
                    <td><?php echo date('d M Y', strtotime($k['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button type="button" class="edit-btn" onclick="toggleEditForm(this, 'kolaborasi-<?php echo $k['id']; ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="admin.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this pesantren? This action cannot be undone');">
                                <input type="hidden" name="action" value="deleteKolaborasi">
                                <input type="hidden" name="kolaborasi_id" value="<?php echo $k['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <button type="submit" class="delete-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="7">
                        <form method="POST" action="admin.php" class="edit-form" id="edit-kolaborasi-<?php echo $k['id']; ?>">
                            <input type="hidden" name="action" value="editKolaborasi">
                            <input type="hidden" name="kolaborasi_id" value="<?php echo $k['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="form-group">
                                <label for="judul">Judul Promosi</label>
                                <input type="text" name="judul" value="<?php echo htmlspecialchars($k['judul']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="deskripsi">Deskripsi Promosi</label>
                                <textarea name="deskripsi" required><?php echo htmlspecialchars($k['deskripsi']); ?></textarea>
                            </div>
                            <button type="submit" class="submit-btn">Simpan Perubahan</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Tab Forum -->
        <div class="tab-content" id="forum">
            <h2>Kelola Thread Forum</h2>
            <form method="POST" action="admin.php">
                <input type="hidden" name="action" value="addThread">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label for="judul">Judul Thread</label>
                    <input type="text" name="judul" placeholder="Judul Thread" required>
                </div>
                <div class="form-group">
                    <label for="konten">Konten Thread</label>
                    <textarea name="konten" placeholder="Konten Thread" required></textarea>
                </div>
                <button type="submit" class="submit-btn">Tambah Thread</button>
            </form>
            <table>
                <tr><th>ID</th><th>Judul</th><th>Konten</th><th>Pengguna</th><th>Aksi</th></tr>
                <?php foreach ($threads as $thread): ?>
                <tr>
                    <td><?php echo $thread['id']; ?></td>
                    <td><?php echo htmlspecialchars($thread['judul']); ?></td>
                    <td><?php echo htmlspecialchars($thread['konten']); ?></td>
                    <td><?php echo htmlspecialchars($thread['pengguna_nama']); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button type="button" class="edit-btn" onclick="toggleEditForm(this, 'thread-<?php echo $thread['id']; ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="admin.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this pesantren? This action cannot be undone');">
                                <input type="hidden" name="action" value="deleteThread">
                                <input type="hidden" name="thread_id" value="<?php echo $thread['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <button type="submit" class="delete-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        <form method="POST" action="admin.php" class="edit-form" id="edit-thread-<?php echo $thread['id']; ?>">
                            <input type="hidden" name="action" value="editThread">
                            <input type="hidden" name="thread_id" value="<?php echo $thread['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <div class="form-group">
                                <label for="judul">Judul Thread</label>
                                <input type="text" name="judul" value="<?php echo htmlspecialchars($thread['judul']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="konten">Konten Thread</label>
                                <textarea name="konten" required><?php echo htmlspecialchars($thread['konten']); ?></textarea>
                            </div>
                            <button type="submit" class="submit-btn">Simpan Perubahan</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <?php require_once 'footer.php'; ?>
    
    <script src="js/translations.js"></script>
    
    <script>
        // Logika untuk tab
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                tab.classList.add('active');
                document.getElementById(tab.dataset.tab).classList.add('active');
            });
        });

        // Fungsi untuk menampilkan/sembunyikan formulir edit
        function toggleEditForm(button, formId) {
            const form = document.getElementById(`edit-${formId}`);
            form.classList.toggle('active');
        }
    </script>
</body>
</html>