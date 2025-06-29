<?php
require_once 'config/config.php';
require_once 'psnetbot.php';

// Pastikan $lang didefinisikan dengan nilai default jika belum ada
$lang = isset($lang) && is_array($lang) ? $lang : [];

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
$pengguna_id = $_SESSION['user_id'] ?? null;

$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM kegiatan WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$kegiatan) {
    header("HTTP/1.0 404 Not Found");
    exit($lang['event_not_found'] ?? 'Event not found');
}

// Cek apakah pengguna sudah menyukai kegiatan ini
$hasLiked = false;
if ($isLoggedIn && $id) {
    $stmt = $conn->prepare("SELECT * FROM kegiatan_likes WHERE kegiatan_id = :kegiatan_id AND pengguna_id = :pengguna_id");
    $stmt->execute(['kegiatan_id' => $id, 'pengguna_id' => $pengguna_id]);
    $hasLiked = $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

// Ambil komentar
$komentar = [];
if ($id) {
    $stmt = $conn->prepare("SELECT kk.*, p.nama FROM kegiatan_komentar kk JOIN pengguna p ON kk.pengguna_id = p.id WHERE kk.kegiatan_id = :id ORDER BY kk.created_at DESC");
    $stmt->execute(['id' => $id]);
    $komentar = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Proses tambah komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn && isset($_POST['komentar_isi'])) {
    $isi = trim($_POST['komentar_isi']);
    if (!empty($isi)) {
        $stmt = $conn->prepare("INSERT INTO kegiatan_komentar (kegiatan_id, pengguna_id, isi) VALUES (:kegiatan_id, :pengguna_id, :isi)");
        $stmt->execute(['kegiatan_id' => $id, 'pengguna_id' => $pengguna_id, 'isi' => $isi]);
        header("Location: detailkegiatan.php?id=$id");
        exit();
    }
}

// Proses suka
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like']) && $isLoggedIn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM kegiatan_likes WHERE kegiatan_id = :kegiatan_id AND pengguna_id = :pengguna_id");
        $stmt->execute(['kegiatan_id' => $id, 'pengguna_id' => $pengguna_id]);
        $alreadyLiked = $stmt->fetch(PDO::FETCH_ASSOC) !== false;

        if ($alreadyLiked) {
            // Batalkan like
            $stmt = $conn->prepare("DELETE FROM kegiatan_likes WHERE kegiatan_id = :kegiatan_id AND pengguna_id = :pengguna_id");
            $stmt->execute(['kegiatan_id' => $id, 'pengguna_id' => $pengguna_id]);
            $stmt = $conn->prepare("UPDATE kegiatan SET suka = suka - 1 WHERE id = :id");
            $stmt->execute(['id' => $id]);
        } else {
            // Tambah like
            $stmt = $conn->prepare("INSERT INTO kegiatan_likes (kegiatan_id, pengguna_id) VALUES (:kegiatan_id, :pengguna_id)");
            $stmt->execute(['kegiatan_id' => $id, 'pengguna_id' => $pengguna_id]);
            $stmt = $conn->prepare("UPDATE kegiatan SET suka = suka + 1 WHERE id = :id");
            $stmt->execute(['id' => $id]);
        }
        header("Location: detailkegiatan.php?id=$id");
        exit();
    } catch (PDOException $e) {
        error_log("Gagal memproses like: " . $e->getMessage());
        exit("Gagal memproses like.");
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kegiatan - IBSflow</title>
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
        background: url('https://www.transparenttextures.com/patterns/light-noise.png');
        opacity: 0.05;
        z-index: -1;
    }

    /* RTL Support */
    [dir="rtl"] { text-align: right; }
    [dir="rtl"] .event-header { text-align: center; }
    [dir="rtl"] .event-info .info-item { flex-direction: row-reverse; }
    [dir="rtl"] .event-info .info-item i { margin-right: 0; margin-left: 10px; }
    [dir="rtl"] .comments-section { text-align: right; }
    [dir="rtl"] .comment-list li { text-align: right; }
    [dir="rtl"] .like-section { right: auto; left: 20px; }

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

    /* Event Detail */
    .event-detail { 
        padding: 60px 20px; 
        max-width: 900px; 
        margin: 0 auto; 
        background: rgba(255, 255, 255, 0.1); 
        border-radius: 20px; 
        backdrop-filter: blur(10px); 
        border: 1px solid rgba(0, 0, 0, 0.05); 
    }
    .event-header { 
        text-align: center; 
        margin-bottom: 40px; 
        animation: fadeIn 1s ease-out; 
    }
    .event-header img { 
        width: 100%; 
        max-width: 600px; 
        height: auto; 
        border-radius: 15px; 
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1); 
        transition: transform 0.3s ease; 
    }
    .event-header img:hover { 
        transform: scale(1.03); 
    }
    .event-header h1 { 
        font-size: 36px; 
        color: var(--primary); 
        margin: 20px 0; 
        position: relative; 
        font-weight: 600; 
        text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.1); 
    }
    .event-header h1::after { 
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
    .event-header span { 
        font-size: 16px; 
        color: var(--primary); 
        display: block; 
        margin-top: 10px; 
        background: rgba(1, 87, 155, 0.05); 
        padding: 8px 15px; 
        border-radius: 8px; 
        font-weight: 500; 
    }

    /* Event Info */
    .event-info { 
        position: relative; 
        background: linear-gradient(145deg, #ffffff, #e6f3f5); 
        padding: 30px; 
        border-radius: 15px; 
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1); 
        margin-bottom: 20px; 
        animation: slideIn 0.5s ease-out; 
    }
    .event-info p { 
        font-size: 16px; 
        color: #444; 
        margin-bottom: 15px; 
        padding: 15px; 
        background: rgba(64, 196, 255, 0.1); 
        border-left: 5px solid var(--primary); 
        border-radius: 10px; 
        position: relative; 
        line-height: 1.8; 
    }
    .event-info p::before { 
        content: '\f15c'; 
        font-family: 'Font Awesome 5 Free'; 
        font-weight: 900; 
        color: var(--secondary); 
        position: absolute; 
        top: 10px; 
        right: 10px; 
        opacity: 0.7; 
        font-size: 18px; 
    }
    .info-item { 
        display: flex; 
        align-items: center; 
        margin-bottom: 12px; 
        padding: 10px; 
        background: rgba(1, 87, 155, 0.05); 
        border-radius: 10px; 
    }
    .info-item i { 
        color: var(--primary); 
        margin-right: 12px; 
        font-size: 16px; 
    }
    .info-item span { 
        font-size: 14px; 
        color: #333; 
    }

    /* Like Section */
    .like-section { 
        position: absolute; 
        top: 20px; 
        right: 20px; 
        display: flex; 
        align-items: center; 
        gap: 10px; 
        background: rgba(255, 255, 255, 0.9); 
        padding: 8px 15px; 
        border-radius: 20px; 
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); 
    }
    .like-icon { 
        font-size: 18px; 
        background: none; 
        border: none; 
        cursor: pointer; 
        transition: all 0.3s ease; 
    }
    .like-icon.liked { 
        color: #ff4d4d; 
        transform: scale(1.2); 
    }
    .like-icon:hover { 
        color: #ff4d4d; 
        transform: scale(1.1); 
    }
    .like-icon:disabled { 
        color: #ccc; 
        cursor: not-allowed; 
    }
    .like-count { 
        font-size: 16px; 
        color: var(--primary); 
        font-weight: 600; 
    }

    /* Comments Section */
    .comments-section { 
        background: linear-gradient(145deg, #ffffff, #e6f3f5); 
        padding: 30px; 
        border-radius: 15px; 
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1); 
        margin-bottom: 20px; 
        animation: slideIn 0.5s ease-out; 
    }
    .comments-section h3 { 
        font-size: 24px; 
        color: var(--primary); 
        margin-bottom: 20px; 
        padding-left: 30px; 
        position: relative; 
        font-weight: 600; 
    }
    .comments-section h3::before { 
        content: '\f075'; 
        font-family: 'Font Awesome 5 Free'; 
        font-weight: 900; 
        color: var(--secondary); 
        position: absolute; 
        left: 0; 
        top: 50%; 
        transform: translateY(-50%); 
        font-size: 20px; 
    }
    .comment-form textarea { 
        width: 100%; 
        padding: 12px; 
        margin-bottom: 15px; 
        border: 2px solid var(--primary); 
        border-radius: 10px; 
        background: #f9fbfd; 
        transition: border-color 0.3s ease, box-shadow 0.3s ease; 
        font-size: 14px; 
    }
    .comment-form textarea:focus { 
        border-color: var(--secondary); 
        box-shadow: 0 0 8px rgba(64, 196, 255, 0.3); 
        outline: none; 
    }
    .comment-form button { 
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
    .comment-form button:hover { 
        transform: scale(1.05); 
        background: var(--primary); 
        color: var(--accent); 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
    }
    .comment-list { 
        margin-top: 20px; 
    }
    .comment-list li { 
        background: linear-gradient(145deg, #f5f6f0, #e6f3f5); 
        padding: 15px; 
        border-radius: 10px; 
        margin-bottom: 15px; 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); 
        transition: transform 0.3s ease; 
        animation: fadeIn 0.5s ease-out; 
    }
    .comment-list li:hover { 
        transform: translateX(5px); 
    }
    .comment-author { 
        font-weight: 600; 
        color: var(--primary); 
        display: block; 
        margin-bottom: 5px; 
    }
    .comment-list p { 
        font-size: 14px; 
        color: #555; 
        margin-bottom: 5px; 
    }
    .comment-date { 
        font-size: 12px; 
        color: var(--primary); 
        background: rgba(1, 87, 155, 0.05); 
        padding: 5px 10px; 
        border-radius: 5px; 
    }

    /* Alert */
    .alert { 
        background: linear-gradient(145deg, #ffffff, #e6f3f5); 
        padding: 15px; 
        border-radius: 10px; 
        border: 2px solid var(--primary); 
        display: flex; 
        align-items: center; 
        gap: 12px; 
        margin-bottom: 20px; 
        color: var(--primary); 
        font-size: 16px; 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
        animation: fadeIn 0.5s ease-out; 
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

    /* Back Button */
    .btn-back { 
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
        text-align: center; 
        margin-top: 20px; 
        display: block; 
    }
    .btn-back:hover { 
        transform: scale(1.05); 
        background: var(--secondary); 
        color: var(--primary); 
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2); 
    }

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
        .event-detail { padding: 40px 20px; }
        .event-header h1 { font-size: 28px; }
        .event-header img { max-width: 100%; }
        .event-info { padding: 20px; }
        .comments-section { padding: 20px; }
        .like-section { 
            position: static; 
            text-align: right; 
            margin-top: 10px; 
        }
        header { padding: 15px 20px; }
        header .logo { font-size: 24px; }
        header nav ul { gap: 15px; }
        header nav ul li a { font-size: 14px; padding: 6px 10px; }
    }
    @media (max-width: 480px) {
        .event-detail { max-width: 100%; padding: 20px 10px; }
        .event-info p { font-size: 14px; }
        .comment-form textarea { font-size: 14px; }
    }
</style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="event-detail">
        <div class="event-header">
            <img src="img/<?php echo htmlspecialchars($kegiatan['gambar'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($kegiatan['judul']); ?>">
            <h1><?php echo htmlspecialchars($kegiatan['judul']); ?></h1>
            <span>Tanggal: <?php echo date('d F Y', strtotime($kegiatan['tanggal'])); ?></span>
        </div>
        <div class="event-info">
            <p><?php echo htmlspecialchars($kegiatan['deskripsi']); ?></p>
            <div class="info-item">
                <i class="fas fa-users"></i>
                <span>Penyelenggara: <?php echo htmlspecialchars($kegiatan['penyelenggara'] ?? 'Tidak Diketahui'); ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Tempat: <?php echo htmlspecialchars($kegiatan['tempat'] ?? 'Tidak Diketahui'); ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-user-friends"></i>
                <span>Jumlah Peserta: <?php echo number_format($kegiatan['jumlah_peserta'] ?? 0); ?></span>
            </div>
            <div class="like-section">    
                <?php if ($isLoggedIn): ?>
                    <form method="POST" style="display:inline;">
                        <button type="submit" name="like" class="like-icon <?php echo $hasLiked ? 'liked' : ''; ?>" title="<?php echo htmlspecialchars($hasLiked ? ($lang['unlike'] ?? 'Unlike') : ($lang['like'] ?? 'Like')); ?>">
                            <i class="fas fa-heart"></i>
                        </button>
                        <span class="like-count"><?php echo $kegiatan['suka'] ?? 0; ?></span>
                    </form>
                <?php else: ?>
                    <button disabled class="like-icon" title="Suka">
                        <i class="fas fa-heart"></i>
                    </button>
                    <span class="like-count"><?php echo $kegiatan['suka'] ?? 0; ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="comments-section">
            <h3>Komentar</h3>
            <?php if ($isLoggedIn): ?>
                <div class="comment-form">
                    <form method="POST" action="">
                        <textarea name="komentar_isi" placeholder="<?php echo htmlspecialchars($lang['write_comment'] ?? 'Tulis Komentar'); ?>" required></textarea>
                        <button type="submit">Kirim</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert">
                    <i class="fas fa-info-circle"></i>
                    <span><a href="login-register.php"><?php echo htmlspecialchars($lang['login'] ?? 'Masuk'); ?></a> <?php echo htmlspecialchars($lang['to_comment'] ?? 'untuk berkomentar'); ?></span>
                </div>
            <?php endif; ?>
            <ul class="comment-list">
                <?php foreach ($komentar as $comment): ?>
                    <li>
                        <span class="comment-author"><?php echo htmlspecialchars($comment['nama']); ?>:</span>
                        <p><?php echo htmlspecialchars($comment['isi']); ?></p>
                        <span class="comment-date"><?php echo date('d F Y H:i', strtotime($comment['created_at'])); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <a href="kegiatan.php" class="btn-back">Kembali ke Kegiatan</a>
    </main>

    <?php require_once 'footer.php'; ?>
</body>
</html>