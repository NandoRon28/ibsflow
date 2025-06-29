<?php
require_once 'config/config.php';
require_once 'psnetbot.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
$pengguna_id = $_SESSION['user_id'] ?? null;

$id = isset($_GET['id']) ? $_GET['id'] : null;
$detail = null;
$feedbacks = [];

if ($id) {
    try {
        $stmt = $conn->prepare("SELECT k.*, p.nama as pesantren_nama FROM kolaborasi k JOIN pesantren p ON k.pesantren_id = p.id WHERE k.id = ?");
        $stmt->execute([$id]);
        $detail = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare("SELECT kf.*, p.nama FROM kolaborasi_feedback kf JOIN pengguna p ON kf.pengguna_id = p.id WHERE kf.kolaborasi_id = ? ORDER BY kf.created_at DESC");
        $stmt->execute([$id]);
        $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die($lang['fetch_data_failed'] . ": " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn && $userRole === 'pengelola') {
    if (isset($_POST['action']) && $_POST['action'] === 'edit_proposal') {
        $judul = $_POST['judul'] ?? '';
        $deskripsi = $_POST['deskripsi'] ?? '';
        if ($detail && $detail['pengguna_id'] == $pengguna_id) {
            try {
                $stmt = $conn->prepare("UPDATE kolaborasi SET judul = ?, deskripsi = ? WHERE id = ? AND pengguna_id = ?");
                $stmt->execute([$judul, $deskripsi, $id, $pengguna_id]);
                header("Location: detailpromosi.php?id=$id&success=" . urlencode($lang['update_success']));
            } catch (PDOException $e) {
                die($lang['fetch_data_failed'] . ": " . $e->getMessage());
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'submit_feedback') {
        $feedback = $_POST['feedback'] ?? '';
        if ($feedback) {
            try {
                $stmt = $conn->prepare("INSERT INTO kolaborasi_feedback (kolaborasi_id, pengguna_id, feedback) VALUES (?, ?, ?)");
                $stmt->execute([$id, $pengguna_id, $feedback]);
                header("Location: detailpromosi.php?id=$id&success=" . urlencode($lang['feedback_success']));
            } catch (PDOException $e) {
                die($lang['fetch_data_failed'] . ": " . $e->getMessage());
            }
        }
    }
}

if (!$detail) {
    header("HTTP/1.0 404 Not Found");
    exit($lang['proposal_not_found']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Promosi - Promosi & Kolaborasi IBSflow</title>
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
    [dir="rtl"] h1 { text-align: center; }
    [dir="rtl"] .feedback-item { text-align: right; }

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

    /* Detail Section */
    .detail { 
        padding: 50px 20px; 
        max-width: 850px; 
        margin: 0 auto; 
        background: rgba(255, 255, 255, 0.9); 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); 
    }
    .detail-view { 
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
    h1 { 
        font-size: 32px; 
        color: var(--primary); 
        margin-bottom: 15px; 
        text-align: center; 
        font-weight: 600; 
        position: relative; 
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
        bottom: -6px; 
        left: 50%; 
        transform: translateX(-50%); 
    }
    .meta { 
        font-size: 13px; 
        color: var(--primary); 
        margin-bottom: 15px; 
        padding: 6px; 
        background: rgba(var(--primary), 0.05); 
        border-radius: 6px; 
        text-align: center; 
    }
    p { 
        font-size: 15px; 
        color: #4B5563; 
        margin-bottom: 15px; 
        padding: 12px; 
        background: rgba(var(--secondary), 0.05); 
        border-left: 3px solid rgba(var(--secondary), 0.3); 
        border-radius: 6px; 
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); 
        animation: popIn 0.7s ease-out; 
    }
    @keyframes popIn {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    /* Edit Button */
    .edit-btn { 
        display: inline-block; 
        padding: 6px 15px; 
        background: var(--secondary); 
        color: var(--accent); 
        text-decoration: none; 
        border-radius: 15px; 
        margin-top: 10px; 
        transition: all 0.3s ease; 
        font-weight: 500; 
    }
    .edit-btn:hover { 
        background: var(--primary); 
        transform: scale(1.05); 
    }

    /* Form */
    form { 
        margin-top: 20px; 
        padding: 15px; 
        border-radius: 10px; 
        background: var(--accent); 
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); 
        animation: slideIn 0.7s ease-out; 
    }
    form h3 { 
        font-size: 18px; 
        color: var(--primary); 
        margin-bottom: 12px; 
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
    .submit-btn { 
        padding: 8px 18px; 
        background: var(--secondary); 
        border: none; 
        border-radius: 20px; 
        cursor: pointer; 
        color: var(--accent); 
        font-weight: 500; 
        transition: all 0.3s ease; 
    }
    .submit-btn:hover { 
        background: var(--primary); 
        transform: scale(1.05); 
    }

    /* Feedback Section */
    .feedback-section { 
        margin-top: 25px; 
    }
    .feedback-section h3 { 
        font-size: 20px; 
        color: var(--primary); 
        margin-bottom: 15px; 
        font-weight: 500; 
        padding-left: 20px; 
        position: relative; 
    }
    .feedback-section h3::before { 
        content: '\f075'; 
        font-family: 'Font Awesome 5 Free'; 
        font-weight: 900; 
        color: var(--secondary); 
        position: absolute; 
        left: 0; 
        top: 50%; 
        transform: translateY(-50%); 
    }
    .feedback-item { 
        background: var(--accent); 
        padding: 12px; 
        border-radius: 8px; 
        margin-bottom: 10px; 
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.05); 
        animation: fadeIn 0.7s ease-out; 
        transition: transform 0.3s ease; 
    }
    .feedback-item:hover { 
        transform: translateX(4px); 
    }
    .feedback-item .author { 
        font-weight: 500; 
        color: var(--primary); 
        display: block; 
        margin-bottom: 5px; 
    }
    .feedback-item p { 
        font-size: 14px; 
        color: #4B5563; 
        margin-bottom: 5px; 
        background: none; 
        padding: 0; 
        border-left: none; 
        box-shadow: none; 
    }
    .feedback-item .date { 
        font-size: 12px; 
        color: var(--primary); 
        padding: 4px; 
        background: rgba(var(--primary), 0.05); 
        border-radius: 4px; 
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
        .detail { padding: 30px 15px; }
        .detail h1 { font-size: 28px; }
        .detail-view { padding: 15px; }
        .feedback-section h3 { font-size: 18px; }
        header { padding: 10px 15px; }
        header .logo { font-size: 22px; }
        header nav ul { gap: 12px; }
        header nav ul li a { font-size: 13px; padding: 4px 8px; }
    }
    @media (max-width: 480px) {
        .detail { max-width: 100%; padding: 20px 10px; }
        .detail-view form input, .detail-view form textarea { font-size: 13px; }
    }
</style>
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="detail">
        <?php if ($detail): ?>
        <div class="detail-view">
            <h1><?php echo htmlspecialchars($detail['judul']); ?></h1>
            <div class="meta">Diajukan oleh: <?php echo htmlspecialchars($detail['pesantren_nama']); ?> | <?php echo date('d M Y', strtotime($detail['created_at'])); ?></div>
            <p><?php echo htmlspecialchars($detail['deskripsi']); ?></p>
            <?php if ($isLoggedIn && $userRole === 'pengelola' && $detail['pengguna_id'] == $pengguna_id): ?>
                <form method="POST" action="detailpromosi.php?id=<?php echo $id; ?>">
                    <input type="hidden" name="action" value="edit_proposal">
                    <h3>Ubah Proposal</h3>
                    <input type="text" name="judul" value="<?php echo htmlspecialchars($detail['judul']); ?>" required>
                    <textarea name="deskripsi" required><?php echo htmlspecialchars($detail['deskripsi']); ?></textarea>
                    <button type="submit" class="submit-btn">Simpan Perubahan</button>
                </form>
            <?php endif; ?>
            <a href="promosi.php" class="cta-btn" style="margin-top: 10px;">Kembali</a>
            <div class="feedback-section">
                <h3>Umpan Balik dari Pengelola</h3>
                <?php if ($isLoggedIn && $userRole === 'pengelola'): ?>
                    <form method="POST" action="detailpromosi.php?id=<?php echo $id; ?>">
                        <input type="hidden" name="action" value="submit_feedback">
                        <textarea name="feedback" placeholder="Tulis umpan balik" required></textarea>
                        <button type="submit" class="submit-btn">Kirim Umpan Balik</button>
                    </form>
                <?php endif; ?>
                <div class="feedback-list">
                    <?php foreach ($feedbacks as $feedback): ?>
                        <div class="feedback-item">
                            <span class="author"><?php echo htmlspecialchars($feedback['nama']); ?>:</span>
                            <p><?php echo htmlspecialchars($feedback['feedback']); ?></p>
                            <span class="date"><?php echo date('d M Y H:i', strtotime($feedback['created_at'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="success">Umpan balik berhasil dikirim</div>
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
    </script>
</body>
</html>