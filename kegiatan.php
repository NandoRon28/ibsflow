<?php
require_once 'config/config.php';
require_once 'psnetbot.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
$pengguna_id = $_SESSION['user_id'] ?? null;

try {
    // Ambil kegiatan dengan informasi pengguna yang mengunggah
    $stmt = $conn->query("SELECT k.*, p.nama as pengunggah_nama 
                         FROM kegiatan k 
                         LEFT JOIN pengguna p ON k.pengguna_id = p.id 
                         ORDER BY k.tanggal DESC");
    $kegiatan = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}

// Proses tambah kegiatan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn && $userRole === 'pengelola' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $judul = $_POST['judul'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $penyelenggara = $_POST['penyelenggara'] ?? '';
    $tempat = $_POST['tempat'] ?? '';
    $jumlah_peserta = $_POST['jumlah_peserta'] ?? null;

    // Proses upload gambar
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (in_array($file_ext, $allowed_ext)) {
            $gambar = uniqid() . '.' . $file_ext;
            $upload_path = 'img/' . $gambar;
            move_uploaded_file($file_tmp, $upload_path);
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO kegiatan (pengguna_id, judul, deskripsi, tanggal, penyelenggara, tempat, jumlah_peserta, gambar) 
                               VALUES (:pengguna_id, :judul, :deskripsi, :tanggal, :penyelenggara, :tempat, :jumlah_peserta, :gambar)");
        $stmt->execute([
            'pengguna_id' => $pengguna_id,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'tanggal' => $tanggal,
            'penyelenggara' => $penyelenggara,
            'tempat' => $tempat,
            'jumlah_peserta' => $jumlah_peserta,
            'gambar' => $gambar
        ]);
        header("Location: kegiatan.php?success=" . urlencode($lang['add_success']));
        exit();
    } catch (PDOException $e) {
        die("Gagal menambahkan kegiatan: " . $e->getMessage());
    }
}

// Proses edit kegiatan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn && $userRole === 'pengelola' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['kegiatan_id'] ?? null;
    $judul = $_POST['judul'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $penyelenggara = $_POST['penyelenggara'] ?? '';
    $tempat = $_POST['tempat'] ?? '';
    $jumlah_peserta = $_POST['jumlah_peserta'] ?? null;

    // Verifikasi kepemilikan kegiatan
    $stmt = $conn->prepare("SELECT pengguna_id FROM kegiatan WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $kegiatan_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($kegiatan_data['pengguna_id'] !== $pengguna_id) {
        header("Location: kegiatan.php?error=" . urlencode($lang['error_no_permission']));
        exit();
    }

    // Ambil gambar lama
    $stmt = $conn->prepare("SELECT gambar FROM kegiatan WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $kegiatan_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $gambar = $kegiatan_data['gambar'];

    // Proses upload gambar baru
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (in_array($file_ext, $allowed_ext)) {
            $new_gambar = uniqid() . '.' . $file_ext;
            $upload_path = 'img/' . $new_gambar;
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Hapus gambar lama jika ada
                if ($gambar && file_exists('img/' . $gambar)) {
                    unlink('img/' . $gambar);
                }
                $gambar = $new_gambar;
            }
        }
    }

    try {
        $stmt = $conn->prepare("UPDATE kegiatan SET judul = :judul, deskripsi = :deskripsi, tanggal = :tanggal, penyelenggara = :penyelenggara, 
                               tempat = :tempat, jumlah_peserta = :jumlah_peserta, gambar = :gambar WHERE id = :id");
        $stmt->execute([
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'tanggal' => $tanggal,
            'penyelenggara' => $penyelenggara,
            'tempat' => $tempat,
            'jumlah_peserta' => $jumlah_peserta,
            'gambar' => $gambar,
            'id' => $id
        ]);
        header("Location: kegiatan.php?success=" . urlencode($lang['update_success']));
        exit();
    } catch (PDOException $e) {
        die("Gagal memperbarui kegiatan: " . $e->getMessage());
    }
}

// Proses hapus kegiatan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn && $userRole === 'pengelola' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['kegiatan_id'] ?? null;

    // Verifikasi kepemilikan kegiatan
    $stmt = $conn->prepare("SELECT pengguna_id, gambar FROM kegiatan WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $kegiatan_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kegiatan_data) {
        header("Location: kegiatan.php?error=" . urlencode($lang['error_not_found']));
        exit();
    }

    if ($kegiatan_data['pengguna_id'] !== $pengguna_id) {
        header("Location: kegiatan.php?error=" . urlencode($lang['error_no_permission']));
        exit();
    }

    try {
        // Hapus gambar dari server jika ada
        if ($kegiatan_data['gambar'] && file_exists('img/' . $kegiatan_data['gambar'])) {
            unlink('img/' . $kegiatan_data['gambar']);
        }

        // Hapus kegiatan dari database
        $stmt = $conn->prepare("DELETE FROM kegiatan WHERE id = :id");
        $stmt->execute(['id' => $id]);

        header("Location: kegiatan.php?success=" . urlencode($lang['delete_success']));
        exit();
    } catch (PDOException $e) {
        die("Gagal menghapus kegiatan: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kegiatan & Berita Terbaru - IBSflow</title>
    <link rel="icon" type="image/png" href="img/logo_ibsflow.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    :root {
        --primary: #01579B;
        --secondary: #40C4FF;
        --accent: #ffffff;
    }

    * { 
        margin: 0; 
        padding: 0; 
        box-sizing: border-box; 
        font-family: 'Poppins', sans-serif; 
    }
    body { 
        background: linear-gradient(120deg, #f0f4f8 30%, #e6f3f5 100%); 
        color: #333; 
        line-height: 1.6; 
        position: relative; 
        overflow-x: hidden; 
    }
    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('img/logo.png');
        opacity: 0.8;
        z-index: -1;
    }

    /* RTL Support */
    [dir="rtl"] { text-align: right; }
    [dir="rtl"] .header-right { flex-direction: row-reverse; }
    [dir="rtl"] .language-dropdown { right: auto; left: 0; }
    [dir="rtl"] .event-card { flex-direction: row-reverse; }
    [dir="rtl"] .event-content { text-align: right; }
    [dir="rtl"] .edit-btn { right: auto; left: 70px; }
    [dir="rtl"] .delete-btn { right: auto; left: 10px; }
    [dir="rtl"] .button-container { flex-direction: row-reverse; }

    /* Header */
    header { 
        background: linear-gradient(90deg, var(--primary) 0%, darken(var(--primary), 10%) 100%); 
        padding: 20px 40px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); 
        position: sticky; 
        top: 0; 
        z-index: 100; 
        animation: slideDown 0.5s ease-out; 
    }
    @keyframes slideDown {
        from { transform: translateY(-100%); }
        to { transform: translateY(0); }
    }
    header .logo { 
        font-size: 28px; 
        font-weight: 700; 
        color: var(--accent); 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.3); 
    }
    header nav ul { 
        list-style: none; 
        display: flex; 
        gap: 25px; 
    }
    header nav ul li a { 
        color: var(--accent); 
        text-decoration: none; 
        font-size: 16px; 
        padding: 8px 15px; 
        border-radius: 5px; 
        transition: all 0.3s ease; 
        position: relative; 
    }
    header nav ul li a::after { 
        content: ''; 
        position: absolute; 
        width: 0; 
        height: 2px; 
        background: var(--secondary); 
        bottom: 0; 
        left: 0; 
        transition: width 0.3s ease; 
    }
    header nav ul li a:hover { 
        background: var(--secondary); 
        color: var(--primary); 
    }
    header nav ul li a:hover::after { 
        width: 100%; 
    }

    /* Events Section */
    .events { 
        padding: 60px 40px; 
        max-width: 1200px; 
        margin: 0 auto; 
        background: rgba(255, 255, 255, 0.1); 
        border-radius: 20px; 
        backdrop-filter: blur(10px); 
        text-align: center; 
        border: 1px solid rgba(0, 0, 0, 0.05); 
    }
    h1 { 
        font-size: 40px; 
        color: var(--primary); 
        margin-bottom: 40px; 
        position: relative; 
        text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.1); 
        animation: fadeIn 1s ease-out; 
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    h1::after { 
        content: ''; 
        width: 60px; 
        height: 4px; 
        background: var(--secondary); 
        position: absolute; 
        bottom: -10px; 
        left: 50%; 
        transform: translateX(-50%); 
        border-radius: 2px; 
    }

    /* Event Grid */
    .event-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); 
        gap: 30px; 
        margin: 0 auto; 
    }
    .event-card { 
        background: linear-gradient(145deg, #ffffff, #e6f3f5); 
        border-radius: 15px; 
        padding: 20px; 
        display: flex; 
        align-items: center; 
        gap: 20px; 
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1); 
        transition: transform 0.3s ease, box-shadow 0.3s ease; 
        position: relative; 
        overflow: hidden; 
        border: 1px solid rgba(0, 0, 0, 0.05); 
        animation: slideIn 0.5s ease-out; 
    }
    @keyframes slideIn {
        from { transform: translateX(-50px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .event-card:hover { 
        transform: translateY(-10px); 
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15); 
    }
    .event-card img { 
        width: 150px; 
        height: 100px; 
        object-fit: cover; 
        border-radius: 10px; 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
        transition: transform 0.3s ease; 
    }
    .event-card:hover img { 
        transform: scale(1.1); 
    }
    .event-content { 
        flex-grow: 1; 
        text-align: left; 
    }
    .event-content h3 { 
        font-size: 22px; 
        color: var(--primary); 
        margin-bottom: 10px;  
        position: relative; 
        font-weight: 600; 
    }
    .event-content p { 
        font-size: 14px; 
        color: #555; 
        margin-bottom: 12px; 
        padding: 12px; 
        background: rgba(64, 196, 255, 0.1); 
        border-radius: 10px; 
        border-left: 4px solid var(--primary); 
        line-height: 1.8; 
    }
    .event-content span { 
        font-size: 13px; 
        color: var(--primary); 
        display: flex; 
        align-items: center; 
        gap: 8px; 
        margin-bottom: 8px; 
        background: rgba(1, 87, 155, 0.05); 
        padding: 6px 12px; 
        border-radius: 8px; 
        font-weight: 500; 
    }
    .event-content span::before { 
        content: '\f073'; 
        font-family: 'Font Awesome 5 Free'; 
        font-weight: 900; 
        color: var(--secondary); 
        font-size: 16px; 
    }
    .event-content a { 
        color: var(--primary); 
        text-decoration: none; 
        font-weight: 600; 
        transition: color 0.3s ease; 
    }
    .event-content a:hover { 
        color: var(--secondary); 
    }

    /* Edit/Delete Buttons */
    .edit-btn, .delete-btn { 
        padding: 8px 15px; 
        border-radius: 20px; 
        font-size: 13px; 
        text-decoration: none; 
        position: absolute; 
        top: 15px; 
        transition: all 0.3s ease; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
    }
    .edit-btn { 
        background: var(--secondary); 
        color: var(--accent); 
        right: 80px; 
    }
    .delete-btn { 
        background: #e74c3c; 
        color: var(--accent); 
        right: 15px; 
    }
    .edit-btn:hover, .delete-btn:hover { 
        transform: scale(1.1); 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); 
    }

    /* Buttons */
    .button-container { 
        display: flex; 
        justify-content: center; 
        margin-top: 40px; 
        margin-bottom: 20px; 
        gap: 20px; 
    }
    .cta-btn, .see-all-btn { 
        display: inline-block; 
        padding: 12px 30px; 
        background: var(--primary); 
        color: var(--accent); 
        text-decoration: none; 
        border-radius: 25px; 
        transition: all 0.3s ease; 
        font-weight: 600; 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); 
        border: 2px solid var(--accent); 
    }
    .cta-btn:hover, .see-all-btn:hover { 
        transform: scale(1.05); 
        background: var(--secondary); 
        color: var(--primary); 
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2); 
    }

    /* Forms */
    .add-event-form, .edit-event-form { 
        background: linear-gradient(145deg, #ffffff, #e6f3f5); 
        padding: 25px; 
        border-radius: 15px; 
        margin-top: 40px; 
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1); 
        display: none; 
        animation: slideIn 0.5s ease-out; 
        border: 1px solid rgba(0, 0, 0, 0.05); 
    }
    .add-event-form.active, .edit-event-form.active { 
        display: block; 
    }
    .add-event-form input, .add-event-form textarea, 
    .edit-event-form input, .edit-event-form textarea { 
        width: 100%; 
        padding: 12px; 
        margin-bottom: 15px; 
        border: 2px solid var(--primary); 
        border-radius: 10px; 
        background: #f9fbfd; 
        transition: border-color 0.3s ease, box-shadow 0.3s ease; 
        font-size: 14px; 
    }
    .add-event-form input:focus, .add-event-form textarea:focus, 
    .edit-event-form input:focus, .edit-event-form textarea:focus { 
        border-color: var(--secondary); 
        box-shadow: 0 0 8px rgba(64, 196, 255, 0.3); 
        outline: none; 
    }
    .add-event-form textarea, .edit-event-form textarea { 
        height: 120px; 
        resize: vertical; 
    }
    .add-event-form button, .edit-event-form button { 
        padding: 12px 25px; 
        background: var(--secondary); 
        border: none; 
        border-radius: 25px; 
        color: var(--accent); 
        cursor: pointer; 
        font-weight: 600; 
        transition: all 0.3s ease; 
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); 
    }
    .add-event-form button:hover, .edit-event-form button:hover { 
        transform: scale(1.05); 
        background: var(--primary); 
        color: var(--accent); 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
    }

    /* Alert, Success, Error */
    .alert { 
        background: linear-gradient(145deg, #ffffff, #e6f3f5); 
        padding: 15px; 
        border-radius: 10px; 
        border: 2px solid var(--primary); 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        gap: 12px; 
        margin: 20px auto; 
        color: var(--primary); 
        font-size: 16px; 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
        animation: fadeIn 0.5s ease-out; 
        max-width: 600px; 
    }
    .alert i { 
        color: var(--secondary); 
        font-size: 22px; 
    }
    .alert a { 
        color: var(--secondary); 
        font-weight: 600; 
        text-decoration: none; 
        transition: color 0.3s ease; 
    }
    .alert a:hover { 
        color: var(--primary); 
        text-decoration: underline; 
    }
    .success, .error { 
        color: var(--accent); 
        padding: 15px; 
        margin: 20px auto; 
        border-radius: 10px; 
        text-align: center; 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); 
        animation: slideIn 0.5s ease-out; 
        max-width: 600px; 
        font-weight: 500; 
    }
    .success { background: var(--primary); }
    .error { background: #e74c3c; }

    /* Footer */
    footer { 
        background: linear-gradient(90deg, var(--primary) 0%, darken(var(--primary), 10%) 100%); 
        color: var(--accent); 
        padding: 30px; 
        text-align: center; 
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.3); 
    }
    footer a { 
        color: var(--secondary); 
        text-decoration: none; 
        font-weight: 600; 
        transition: color 0.3s ease; 
    }
    footer a:hover { 
        color: var(--accent); 
    }

    /* Responsivitas */
    @media (max-width: 768px) {
        .events { padding: 40px 20px; }
        .event-grid { grid-template-columns: 1fr; }
        .event-card { flex-direction: column; text-align: center; }
        .event-card img { width: 100%; max-width: 300px; }
        .event-content { text-align: center; }
        .event-content h3 { padding-left: 0; }
        .event-content h3::before { display: none; }
        .edit-btn { right: 80px; }
        .delete-btn { right: 15px; }
        header { padding: 15px 20px; }
        header .logo { font-size: 24px; }
        header nav ul { gap: 15px; }
        header nav ul li a { font-size: 14px; padding: 6px 10px; }
    }
    @media (max-width: 480px) {
        .events { max-width: 100%; padding: 20px 10px; }
        .event-card img { max-width: 100%; }
        .add-event-form input, .add-event-form textarea, 
        .edit-event-form input, .edit-event-form textarea { font-size: 14px; }
    }
</style>
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="events">
        <h1>Kegiatan & Berita Terbaru</h1>
        <?php if (isset($_GET['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <div class="event-grid" id="event-list">
            <?php foreach ($kegiatan as $event): ?>
            <div class="event-card">
                <img src="img/<?php echo htmlspecialchars($event['gambar'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($event['judul']); ?>">
                <div class="event-content">
                    <h3><?php echo htmlspecialchars($event['judul']); ?></h3>
                    <p><?php echo htmlspecialchars($event['deskripsi']); ?></p>
                    <span><?php echo date('d F Y', strtotime($event['tanggal'])); ?></span>
                    <a href="detailkegiatan.php?id=<?php echo $event['id']; ?>">Baca Selengkapnya</a>
                </div>
                <?php if ($isLoggedIn && $userRole === 'pengelola' && $event['pengguna_id'] === $pengguna_id): ?>
                    <a href="#" class="edit-btn" onclick="toggleEditForm(<?php echo $event['id']; ?>)">Ubah</a>
                    <a href="#" class="delete-btn" onclick="confirmDelete(<?php echo $event['id']; ?>)">Hapus</a>
                    <div class="edit-event-form" id="edit-event-form-<?php echo $event['id']; ?>">
                        <form method="POST" action="kegiatan.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="kegiatan_id" value="<?php echo $event['id']; ?>">
                            <input type="text" name="judul" value="<?php echo htmlspecialchars($event['judul']); ?>" placeholder="Judul Kegiatan" required>
                            <textarea name="deskripsi" placeholder="Deskripsi Kegiatan" required><?php echo htmlspecialchars($event['deskripsi']); ?></textarea>
                            <input type="date" name="tanggal" value="<?php echo $event['tanggal']; ?>" required>
                            <input type="text" name="penyelenggara" value="<?php echo htmlspecialchars($event['penyelenggara']); ?>" placeholder="Penyelenggara">
                            <input type="text" name="tempat" value="<?php echo htmlspecialchars($event['tempat']); ?>" placeholder="Tempat">
                            <input type="number" name="jumlah_peserta" value="<?php echo htmlspecialchars($event['jumlah_peserta']); ?>" placeholder="Jumlah Peserta">
                            <input type="file" name="gambar" accept="image/*">
                            <button type="submit">Selesai</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="button-container">
            <a href="kegiatan.php" class="see-all-btn">Lihat Semua Kegiatan</a>
        </div>

        <?php if ($isLoggedIn && $userRole === 'pengelola'): ?>
            <div class="button-container">
                <button class="cta-btn" onclick="toggleAddForm()">Tambah Kegiatan</button>
            </div>
            <div class="add-event-form" id="add-event-form">
                <form method="POST" action="kegiatan.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <input type="text" name="judul" placeholder="Judul Kegiatan" required>
                    <textarea name="deskripsi" placeholder="Deskripsi Kegiatan" required></textarea>
                    <input type="date" name="tanggal" required>
                    <input type="text" name="penyelenggara" placeholder="Penyelenggara">
                    <input type="text" name="tempat" placeholder="Tempat">
                    <input type="number" name="jumlah_peserta" placeholder="Jumlah Peserta">
                    <input type="file" name="gambar" accept="image/*">
                    <button type="submit">Tambah Kegiatan</button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert">
                <i class="fas fa-info-circle"></i>
                <span>Silakan <a href="login-register.php">Masuk/Daftar</a> sebagai pengelola untuk menambah, mengubah, atau menghapus kegiatan</span>
            </div>
        <?php endif; ?>
    </section>

    <?php require_once 'footer.php'; ?>
    
    <script>
        function toggleAddForm() {
            const form = document.getElementById('add-event-form');
            form.classList.toggle('active');
        }

        function toggleEditForm(id) {
            const form = document.getElementById('edit-event-form-' + id);
            form.classList.toggle('active');
            if (form.classList.contains('active')) {
                form.querySelector('input[name="judul"]').focus();
            }
        }

        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus kegiatan ini? Tindakan ini tidak dapat dibatalkan')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'kegiatan.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="kegiatan_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>