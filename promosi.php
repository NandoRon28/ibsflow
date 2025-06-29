<?php
require_once 'config/config.php';
require_once 'psnetbot.php';
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
$pesantren_id = $_SESSION['pesantren_id'] ?? null;
$pengguna_id = $_SESSION['user_id'] ?? null;

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn && $userRole === 'pengelola') {
    // Validasi CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token tidak valid.");
    } 
    if (isset($_POST['action']) && $_POST['action'] === 'submit_proposal') {
        $judul = $_POST['judul'] ?? '';
        $deskripsi = $_POST['deskripsi'] ?? '';

        if (!$pesantren_id) {
            header("Location: kontak.php?error=" . urlencode($lang['register_pesantren_first']));
            exit();
        }

        try {
            $stmt = $conn->prepare("INSERT INTO kolaborasi (pengguna_id, pesantren_id, judul, deskripsi) VALUES (?, ?, ?, ?)");
            $stmt->execute([$pengguna_id, $pesantren_id, $judul, $deskripsi]);
            header("Location: promosi.php?success=" . urlencode($lang['proposal_success']));
            exit;
        } catch (PDOException $e) {
            die("Gagal mengunggah proposal: " . $e->getMessage());
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_proposal') {
        $kolaborasi_id = $_POST['kolaborasi_id'] ?? '';
        try {
            // Validasi bahwa pengguna adalah pengunggah proposal
            $stmt = $conn->prepare("SELECT pengguna_id FROM kolaborasi WHERE id = ?");
            $stmt->execute([$kolaborasi_id]);
            $kolaborasi = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($kolaborasi && $kolaborasi['pengguna_id'] == $pengguna_id) {
                $stmt = $conn->prepare("DELETE FROM kolaborasi WHERE id = ?");
                $stmt->execute([$kolaborasi_id]);
                header("Location: promosi.php?success=" . urlencode($lang['delete_success']));
                exit;
            } else {
                die("Anda tidak memiliki izin untuk menghapus proposal ini.");
            }
        } catch (PDOException $e) {
            die("Gagal menghapus proposal: " . $e->getMessage());
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit_proposal') {
        $kolaborasi_id = $_POST['kolaborasi_id'] ?? '';
        $judul = $_POST['judul'] ?? '';
        $deskripsi = $_POST['deskripsi'] ?? '';

        try {
            // Validasi bahwa pengguna adalah pengunggah proposal
            $stmt = $conn->prepare("SELECT pengguna_id FROM kolaborasi WHERE id = ?");
            $stmt->execute([$kolaborasi_id]);
            $kolaborasi = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($kolaborasi && $kolaborasi['pengguna_id'] == $pengguna_id) {
                $stmt = $conn->prepare("UPDATE kolaborasi SET judul = ?, deskripsi = ? WHERE id = ?");
                $stmt->execute([$judul, $deskripsi, $kolaborasi_id]);
                header("Location: promosi.php?success=" . urlencode($lang['update_success']));
                exit;
            } else {
                die("Anda tidak memiliki izin untuk mengedit proposal ini.");
            }
        } catch (PDOException $e) {
            die("Gagal mengedit proposal: " . $e->getMessage());
        }
    }
}

try {
    $stmt = $conn->query("SELECT k.*, p.nama as pesantren_nama FROM kolaborasi k JOIN pesantren p ON k.pesantren_id = p.id ORDER BY k.created_at DESC");
    $kolaborasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Gagal mengambil data kolaborasi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promosi & Kolaborasi IBSflow</title>
    <link rel="icon" type="image/png" href="img/logo_ibsflow.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    :root {
        --primary: #1E3A8A;
        --secondary: #3B82F6;
        --accent: #F9FAFB;
    }

    * { 
        margin: 0; 
        padding: 0; 
        box-sizing: border-box; 
        font-family: 'Poppins', sans-serif; 
    }
    body { 
        background: linear-gradient(135deg, #E0E7FF 0%, #F3F4F6 100%); 
        color: #1F2937; 
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
        background: url('https://www.transparenttextures.com/patterns/light-paper-fibers.png');
        opacity: 0.05;
        z-index: -1;
    }

    /* RTL Support */
    [dir="rtl"] { text-align: right; }
    [dir="rtl"] .header-right { flex-direction: row-reverse; }
    [dir="rtl"] .language-dropdown { right: auto; left: 0; }
    [dir="rtl"] .collaboration-list { direction: rtl; }
    [dir="rtl"] .collaboration-card { text-align: right; }
    [dir="rtl"] .promo-collaboration form { text-align: right; }
    [dir="rtl"] .collaboration-card .actions { flex-direction: row-reverse; right: auto; left: 10px; }

    /* Header */
    header { 
        background: linear-gradient(90deg, var(--primary), darken(var(--primary), 10%)); 
        padding: 15px 30px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
        position: sticky; 
        top: 0; 
        z-index: 100; 
        animation: slideDown 0.5s ease-out; 
    }
    @keyframes slideDown {
        from { transform: translateY(-100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    header .logo { 
        font-size: 26px; 
        font-weight: 600; 
        color: var(--accent); 
        text-transform: uppercase; 
        letter-spacing: 1px; 
    }
    header nav ul { 
        list-style: none; 
        display: flex; 
        gap: 20px; 
    }
    header nav ul li a { 
        color: var(--accent); 
        text-decoration: none; 
        font-size: 15px; 
        padding: 6px 12px; 
        border-radius: 4px; 
        transition: color 0.3s ease; 
    }
    header nav ul li a:hover { 
        color: var(--secondary); 
    }

    /* CTA Button */
    .cta-btn { 
        background: var(--secondary); 
        padding: 8px 18px; 
        border-radius: 20px; 
        color: var(--accent); 
        font-weight: 500; 
        text-decoration: none; 
        transition: all 0.3s ease; 
    }
    .cta-btn:hover { 
        transform: scale(1.05); 
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); 
    }

    /* Promo Section */
    .promo { 
        padding: 50px 20px; 
        max-width: 1100px; 
        margin: 0 auto; 
        background: rgba(255, 255, 255, 0.9); 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); 
    }
    h1 { 
        font-size: 36px; 
        color: var(--primary); 
        margin-bottom: 30px; 
        text-align: center; 
        position: relative; 
        font-weight: 600; 
        animation: fadeIn 0.7s ease-out; 
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    h1::after { 
        content: ''; 
        width: 50px; 
        height: 3px; 
        background: var(--secondary); 
        position: absolute; 
        bottom: -8px; 
        left: 50%; 
        transform: translateX(-50%); 
    }

    /* Promo Collaboration */
    .promo-collaboration { 
        padding: 25px; 
        border-radius: 10px; 
        background: var(--accent); 
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); 
        animation: slideIn 0.7s ease-out; 
    }
    @keyframes slideIn {
        from { transform: translateX(-30px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    h2 { 
        font-size: 24px; 
        color: var(--primary); 
        margin-bottom: 20px; 
        font-weight: 500; 
        padding-left: 35px; 
        position: relative; 
    }
    h2::before { 
        content: '\f0ac'; 
        font-family: 'Font Awesome 5 Free'; 
        font-weight: 900; 
        color: var(--secondary); 
        position: absolute; 
        left: 0; 
        top: 50%; 
        transform: translateY(-50%); 
    }

    /* Collaboration List */
    .collaboration-list { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
        gap: 20px; 
        margin-bottom: 25px; 
    }
    .collaboration-card { 
        background: var(--accent); 
        padding: 15px; 
        border-radius: 8px; 
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); 
        transition: transform 0.3s ease; 
        position: relative; 
        overflow: hidden; 
        animation: popIn 0.7s ease-out; 
    }
    @keyframes popIn {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .collaboration-card:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
    }
    .collaboration-card h3 { 
        font-size: 18px; 
        color: var(--primary); 
        margin-bottom: 10px; 
        font-weight: 500; 
        padding-left: 20px; 
        position: relative; 
    }
    .collaboration-card h3::before { 
        content: '\f0ac'; 
        font-family: 'Font Awesome 5 Free'; 
        font-weight: 900; 
        color: var(--secondary); 
        position: absolute; 
        left: 0; 
        top: 50%; 
        transform: translateY(-50%); 
    }
    .collaboration-card p { 
        font-size: 14px; 
        color: #4B5563; 
        margin-bottom: 12px; 
        padding: 10px; 
        background: rgba(var(--secondary), 0.05); 
        border-left: 3px solid rgba(var(--secondary), 0.3); 
        border-radius: 6px; 
    }
    .collaboration-card .meta { 
        font-size: 13px; 
        color: var(--primary); 
        margin-bottom: 10px; 
        padding: 6px; 
        background: rgba(var(--primary), 0.05); 
        border-radius: 6px; 
    }
    .collaboration-card .actions { 
        display: flex; 
        gap: 8px; 
        position: absolute; 
        top: 10px; 
        right: 10px; 
    }
    .detail-btn { 
        padding: 6px 15px; 
        background: var(--secondary); 
        color: var(--accent); 
        text-decoration: none; 
        border-radius: 15px; 
        font-size: 13px; 
        font-weight: 500; 
        transition: all 0.3s ease; 
    }
    .detail-btn:hover { 
        background: var(--primary); 
        transform: scale(1.05); 
    }

    /* Form */
    form { 
        padding: 20px; 
        border-radius: 10px; 
        background: var(--accent); 
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); 
        animation: slideIn 0.7s ease-out; 
    }
    form h3 { 
        font-size: 18px; 
        color: var(--primary); 
        margin-bottom: 15px; 
        font-weight: 500; 
        padding-left: 20px; 
        position: relative; 
    }
    form h3::before { 
        content: '\f0ac'; 
        font-family: 'Font Awesome 5 Free'; 
        font-weight: 900; 
        color: var(--secondary); 
        position: absolute; 
        left: 0; 
        top: 50%; 
        transform: translateY(-50%); 
    }
    form input, form textarea { 
        width: 100%; 
        padding: 10px; 
        margin-bottom: 12px; 
        border: 1px solid #D1D5DB; 
        border-radius: 6px; 
        background: #FFFFFF; 
        transition: border-color 0.3s ease, box-shadow 0.3s ease; 
    }
    form input:focus, form textarea:focus { 
        border-color: var(--secondary); 
        box-shadow: 0 0 8px rgba(var(--secondary), 0.2); 
        outline: none; 
    }
    form textarea { 
        height: 90px; 
        resize: vertical; 
    }
    .edit-form { 
        display: none; 
        margin-top: 15px; 
    }
    .edit-form.active { 
        display: block; 
    }
    .upload-btn { 
        padding: 8px 18px; 
        background: var(--secondary); 
        border: none; 
        border-radius: 20px; 
        cursor: pointer; 
        color: var(--accent); 
        font-weight: 500; 
        transition: all 0.3s ease; 
    }
    .upload-btn:hover { 
        background: var(--primary); 
        transform: scale(1.05); 
    }

    /* Icons */
    .delete-icon { 
        background: none; 
        border: none; 
        color: #EF4444; 
        font-size: 15px; 
        cursor: pointer; 
        transition: all 0.3s ease; 
    }
    .delete-icon:hover { 
        color: #DC2626; 
        transform: scale(1.1); 
    }
    .edit-icon { 
        background: none; 
        border: none; 
        color: var(--secondary); 
        font-size: 15px; 
        cursor: pointer; 
        transition: all 0.3s ease; 
    }
    .edit-icon:hover { 
        color: var(--primary); 
        transform: scale(1.1); 
    }

    /* Alert, Success */
    .alert { 
        padding: 12px; 
        border-radius: 8px; 
        border: 1px solid rgba(var(--primary), 0.2); 
        display: flex; 
        align-items: center; 
        gap: 8px; 
        margin-bottom: 20px; 
        color: var(--primary); 
        font-size: 14px; 
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.05); 
        animation: fadeIn 0.7s ease-out; 
        max-width: 500px; 
        margin-left: auto; 
        margin-right: auto; 
        background: var(--accent); 
    }
    .alert i { 
        color: var(--secondary); 
        font-size: 18px; 
    }
    .alert a { 
        color: var(--secondary); 
        font-weight: 500; 
        text-decoration: none; 
        transition: color 0.3s ease; 
    }
    .alert a:hover { 
        color: var(--primary); 
    }
    .success { 
        color: var(--accent); 
        padding: 10px; 
        margin-bottom: 20px; 
        border-radius: 8px; 
        background: var(--primary); 
        text-align: center; 
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); 
        animation: slideIn 0.7s ease-out; 
        max-width: 500px; 
        margin-left: auto; 
        margin-right: auto; 
    }

    /* Footer */
    footer { 
        background: linear-gradient(90deg, var(--primary), darken(var(--primary), 10%)); 
        color: var(--accent); 
        padding: 20px; 
        text-align: center; 
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1); 
    }
    footer a { 
        color: var(--secondary); 
        text-decoration: none; 
        font-weight: 500; 
        transition: color 0.3s ease; 
    }
    footer a:hover { 
        color: var(--accent); 
    }

    /* Responsivitas */
    @media (max-width: 768px) {
        .promo { padding: 30px 15px; }
        .promo h1 { font-size: 28px; }
        .promo-collaboration { padding: 15px; }
        .collaboration-list { grid-template-columns: 1fr; }
        header { padding: 10px 15px; }
        header .logo { font-size: 22px; }
        header nav ul { gap: 12px; }
        header nav ul li a { font-size: 13px; padding: 4px 8px; }
    }
    @media (max-width: 480px) {
        .promo { padding: 20px 10px; }
        .promo-collaboration form input, .promo-collaboration form textarea { font-size: 13px; }
    }
</style>
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="promo">
        <h1>Promosi & Kolaborasi</h1>
        <div class="promo-collaboration">
            <h2>Proposal Kolaborasi</h2>
            <div class="collaboration-list">
                <?php foreach ($kolaborasi as $item): ?>
                <div class="collaboration-card">
                    <h3><?php echo htmlspecialchars($item['judul']); ?></h3>
                    <p><?php echo htmlspecialchars($item['deskripsi']); ?></p>
                    <div class="meta">Diajukan oleh: <?php echo htmlspecialchars($item['pesantren_nama'] ?? 'Admin'); ?> | <?php echo date('d M Y', strtotime($item['created_at'])); ?> | Tipe: <?php echo htmlspecialchars($item['tipe'] ?? 'kolaborasi'); ?></div>
                    <?php if ($isLoggedIn && $userRole === 'pengelola' && $item['pengguna_id'] == $pengguna_id && $item['tipe'] === 'kolaborasi'): ?>
                    <div class="actions">
                        <form method="POST" action="promosi.php" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proposal ini? Tindakan ini tidak dapat dibatalkan');">
                            <input type="hidden" name="action" value="delete_proposal">
                            <input type="hidden" name="kolaborasi_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="delete-icon" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        <button onclick="toggleEditForm(this, <?php echo $item['id']; ?>)" class="edit-icon" title="Ubah proposal">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                    <a href="detailpromosi.php?id=<?php echo $item['id']; ?>" class="detail-btn">Lihat Detail</a>
                    <?php if ($isLoggedIn && $userRole === 'pengelola' && $item['pengguna_id'] == $pengguna_id && $item['tipe'] === 'kolaborasi'): ?>
                    <form method="POST" action="promosi.php" class="edit-form" id="edit-form-<?php echo $item['id']; ?>">
                        <input type="hidden" name="action" value="edit_proposal">
                        <input type="hidden" name="kolaborasi_id" value="<?php echo $item['id']; ?>">
                        <h3>Ubah Proposal</h3>
                        <input type="text" name="judul" value="<?php echo htmlspecialchars($item['judul']); ?>" required>
                        <textarea name="deskripsi" required><?php echo htmlspecialchars($item['deskripsi']); ?></textarea>
                        <button type="submit" class="upload-btn">Unggah</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if ($isLoggedIn && $userRole === 'pengelola'): ?>
                <?php if (!$pesantren_id): ?>
                    <div class="alert">
                        <i class="fas fa-info-circle"></i>
                        <span>Silakan daftarkan pesantren Anda terlebih dahulu di halaman <a href="index.php#contact">kontak</a></span>
                    </div>
                <?php else: ?>
                <form method="POST" action="promosi.php">
                    <input type="hidden" name="action" value="submit_proposal">
                    <h3>Unggah Proposal Baru</h3>
                    <input type="text" name="judul" placeholder="Judul proposal" required>
                    <textarea name="deskripsi" placeholder="Deskripsi proposal" required></textarea>
                    <button type="submit" class="upload-btn">Unggah</button>
                </form>
                <?php endif; ?>
            <?php else: ?>
            <div class="alert">
                <i class="fas fa-info-circle"></i>
                <span>Silakan <a href="login-register.php" onclick="console.log('Navigating to login-register.php');">Masuk/Daftar</a> sebagai pengelola untuk mengunggah proposal.</span>
            </div>
            <?php endif; ?>
        </div>
        <?php if (isset($_GET['success'])): ?>
            <div class="success">Berhasil</div>
        <?php endif; ?>
    </section>

    <?php require_once 'footer.php'; ?>

    <script src="js/translations.js"></script>

    <script>
        if (!localStorage.getItem('selectedLang')) {
            localStorage.setItem('selectedLang', '<?php echo htmlspecialchars($selectedLang); ?>');
        }

        if (typeof changeLanguage === 'undefined') {
            console.error('File translations.js tidak dimuat atau fungsi changeLanguage tidak ditemukan.');
        }

        function toggleEditForm(button, kolaborasiId) {
            const form = document.getElementById(`edit-form-${kolaborasiId}`);
            if (form) {
                form.classList.toggle('active');
                if (form.classList.contains('active')) {
                    form.querySelector('input[name="judul"]').focus();
                }
            } else {
                console.error('Form edit tidak ditemukan untuk kolaborasi ID:', kolaborasiId);
            }
        }
    </script>
</body>
</html>