<?php
require_once 'config/config.php';
require_once 'psnetbot.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
$pengguna_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn) {
    if (isset($_POST['action']) && $_POST['action'] === 'addThread') {
        $judul = $_POST['judul'] ?? '';
        $isi = $_POST['isi'] ?? '';
        $gambar = null;

        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png'];
            $maxSize = 2 * 1024 * 1024;
            $fileType = $_FILES['gambar']['type'];
            $fileSize = $_FILES['gambar']['size'];
            $fileName = $_FILES['gambar']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = 'thread_' . time() . '_' . uniqid() . '.' . $fileExt;
            $uploadPath = 'uploads/' . $newFileName;

            if (!in_array($fileType, $allowedTypes) || $fileSize > $maxSize) {
                header("Location: forum.php?error=" . urlencode($lang['image_error']));
                exit;
            }
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadPath)) {
                $gambar = $newFileName;
            } else {
                header("Location: forum.php?error=" . urlencode("Gagal mengunggah gambar."));
                exit;
            }
        }

        try {
            $stmt = $conn->prepare("INSERT INTO thread (pengguna_id, judul, isi, suka, gambar) VALUES (?, ?, ?, 0, ?)");
            $stmt->execute([$pengguna_id, $judul, $isi, $gambar]);
            header("Location: forum.php?success=" . urlencode($lang['thread_success']));
            exit;
        } catch (PDOException $e) {
            die("Gagal membuat thread: " . $e->getMessage());
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'addReply') {
        $thread_id = $_POST['thread_id'] ?? '';
        $isi = $_POST['isi'] ?? '';

        try {
            $stmt = $conn->prepare("INSERT INTO komentar (thread_id, pengguna_id, isi) VALUES (?, ?, ?)");
            $stmt->execute([$thread_id, $pengguna_id, $isi]);
            header("Location: forum.php?success=" . urlencode($lang['reply_success']));
            exit;
        } catch (PDOException $e) {
            die("Gagal mengirim balasan: " . $e->getMessage());
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'like') {
        $thread_id = $_POST['thread_id'] ?? '';
        try {
            $stmt = $conn->prepare("SELECT * FROM thread_likes WHERE thread_id = :thread_id AND pengguna_id = :pengguna_id");
            $stmt->execute(['thread_id' => $thread_id, 'pengguna_id' => $pengguna_id]);
            $alreadyLiked = $stmt->fetch(PDO::FETCH_ASSOC) !== false;

            if ($alreadyLiked) {
                $stmt = $conn->prepare("DELETE FROM thread_likes WHERE thread_id = :thread_id AND pengguna_id = :pengguna_id");
                $stmt->execute(['thread_id' => $thread_id, 'pengguna_id' => $pengguna_id]);
                $stmt = $conn->prepare("UPDATE thread SET suka = suka - 1 WHERE id = :id");
                $stmt->execute(['id' => $thread_id]);
            } else {
                $stmt = $conn->prepare("INSERT INTO thread_likes (thread_id, pengguna_id) VALUES (:thread_id, :pengguna_id)");
                $stmt->execute(['thread_id' => $thread_id, 'pengguna_id' => $pengguna_id]);
                $stmt = $conn->prepare("UPDATE thread SET suka = suka + 1 WHERE id = :id");
                $stmt->execute(['id' => $thread_id]);
            }
            header("Location: forum.php");
            exit;
        } catch (PDOException $e) {
            die("Gagal memproses like: " . $e->getMessage());
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'deleteThread') {
        $thread_id = $_POST['thread_id'] ?? '';
        try {
            $stmt = $conn->prepare("SELECT pengguna_id, gambar FROM thread WHERE id = ?");
            $stmt->execute([$thread_id]);
            $thread = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($thread && $thread['pengguna_id'] == $pengguna_id) {
                $conn->beginTransaction();
                $stmt = $conn->prepare("DELETE FROM komentar WHERE thread_id = ?");
                $stmt->execute([$thread_id]);
                $stmt = $conn->prepare("DELETE FROM thread_likes WHERE thread_id = ?");
                $stmt->execute([$thread_id]);
                $stmt = $conn->prepare("DELETE FROM thread WHERE id = ?");
                $stmt->execute([$thread_id]);
                if ($thread['gambar'] && file_exists('uploads/' . $thread['gambar'])) {
                    unlink('uploads/' . $thread['gambar']);
                }
                $conn->commit();
                header("Location: forum.php?success=" . urlencode($lang['delete_success']));
                exit;
            } else {
                die("Anda tidak memiliki izin untuk menghapus thread ini.");
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            die("Gagal menghapus thread: " . $e->getMessage());
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'editThread') {
        $thread_id = $_POST['thread_id'] ?? '';
        $judul = $_POST['judul'] ?? '';
        $isi = $_POST['isi'] ?? '';
        $gambar = null;

        try {
            $stmt = $conn->prepare("SELECT pengguna_id, gambar FROM thread WHERE id = ?");
            $stmt->execute([$thread_id]);
            $thread = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($thread && $thread['pengguna_id'] == $pengguna_id) {
                if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                    $allowedTypes = ['image/jpeg', 'image/png'];
                    $maxSize = 2 * 1024 * 1024;
                    $fileType = $_FILES['gambar']['type'];
                    $fileSize = $_FILES['gambar']['size'];
                    $fileName = $_FILES['gambar']['name'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $newFileName = 'thread_' . time() . '_' . uniqid() . '.' . $fileExt;
                    $uploadPath = 'uploads/' . $newFileName;

                    if (!in_array($fileType, $allowedTypes) || $fileSize > $maxSize) {
                        header("Location: forum.php?error=" . urlencode($lang['image_error']));
                        exit;
                    }
                    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadPath)) {
                        if ($thread['gambar'] && file_exists('uploads/' . $thread['gambar'])) {
                            unlink('uploads/' . $thread['gambar']);
                        }
                        $gambar = $newFileName;
                    } else {
                        header("Location: forum.php?error=" . urlencode("Gagal mengunggah gambar."));
                        exit;
                    }
                } else {
                    $gambar = $thread['gambar'];
                }

                $stmt = $conn->prepare("UPDATE thread SET judul = ?, isi = ?, gambar = ? WHERE id = ?");
                $stmt->execute([$judul, $isi, $gambar, $thread_id]);
                header("Location: forum.php?success=" . urlencode($lang['update_success']));
                exit;
            } else {
                die("Anda tidak memiliki izin untuk mengedit thread ini.");
            }
        } catch (PDOException $e) {
            die("Gagal mengedit thread: " . $e->getMessage());
        }
    }
}

try {
    $stmt = $conn->query("SELECT t.*, p.nama AS penulis, p.profile_picture FROM thread t JOIN pengguna p ON t.pengguna_id = p.id ORDER BY t.created_at DESC");
    $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $comments = [];
    $likedThreads = [];
    foreach ($threads as $thread) {
        $stmt = $conn->prepare("SELECT k.*, p.nama AS penulis_komentar, p.profile_picture AS comment_profile_picture FROM komentar k JOIN pengguna p ON k.pengguna_id = p.id WHERE k.thread_id = ? ORDER BY k.created_at ASC");
        $stmt->execute([$thread['id']]);
        $comments[$thread['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($isLoggedIn) {
            $stmt = $conn->prepare("SELECT * FROM thread_likes WHERE thread_id = :thread_id AND pengguna_id = :pengguna_id");
            $stmt->execute(['thread_id' => $thread['id'], 'pengguna_id' => $pengguna_id]);
            $likedThreads[$thread['id']] = $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        }
    }
} catch (PDOException $e) {
    die("Gagal mengambil data thread: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Komunitas IBSflow</title>
    <link rel="icon" type="image/png" href="img/logo_ibsflow.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        background: linear-gradient(135deg, #f0f4f8 0%, #e6f3f5 80%); 
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
        background: url('https://www.transparenttextures.com/patterns/subtle-stripes.png');
        opacity: 0.08;
        z-index: -1;
    }

    /* RTL Support */
    [dir="rtl"] { text-align: right; }
    [dir="rtl"] .header-right { flex-direction: row-reverse; }
    [dir="rtl"] .language-dropdown { right: auto; left: 0; }
    [dir="rtl"] .thread-form { text-align: right; }
    [dir="rtl"] .thread-card .actions { flex-direction: row-reverse; }
    [dir="rtl"] .thread-card .comment { flex-direction: row-reverse; }
    [dir="rtl"] .thread-card .reply-form { text-align: right; }
    [dir="rtl"] .thread-card .edit-form { text-align: right; }
    [dir="rtl"] .thread-card .meta .thread-actions { flex-direction: row-reverse; }

    /* Header */
    header { 
        background: linear-gradient(90deg, var(--primary), darken(var(--primary), 15%)); 
        padding: 20px 40px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.25); 
        position: sticky; 
        top: 0; 
        z-index: 100; 
        animation: slideDown 0.6s cubic-bezier(0.25, 0.1, 0.25, 1); 
    }
    @keyframes slideDown {
        from { transform: translateY(-100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    header .logo { 
        font-size: 30px; 
        font-weight: 700; 
        color: var(--accent); 
        text-transform: uppercase; 
        letter-spacing: 2px; 
        text-shadow: 3px 3px 8px rgba(0, 0, 0, 0.3); 
    }
    header nav ul { 
        list-style: none; 
        display: flex; 
        gap: 30px; 
    }
    header nav ul li a { 
        color: var(--accent); 
        text-decoration: none; 
        font-size: 17px; 
        padding: 10px 20px; 
        border-radius: 8px; 
        transition: all 0.4s ease; 
        position: relative; 
        overflow: hidden; 
    }
    header nav ul li a::after { 
        content: ''; 
        position: absolute; 
        width: 0; 
        height: 3px; 
        background: var(--secondary); 
        bottom: 5px; 
        left: 50%; 
        transform: translateX(-50%); 
        transition: width 0.4s ease; 
    }
    header nav ul li a:hover { 
        background: rgba(var(--secondary), 0.3); 
        color: var(--primary); 
    }
    header nav ul li a:hover::after { 
        width: 80%; 
    }

    /* CTA Button */
    .cta-btn { 
        background: var(--secondary); 
        padding: 12px 25px; 
        border-radius: 30px; 
        color: var(--accent); 
        font-weight: 600; 
        text-decoration: none; 
        transition: all 0.4s ease; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
        border: 2px solid var(--accent); 
    }
    .cta-btn:hover { 
        transform: scale(1.08); 
        background: var(--primary); 
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3); 
    }

    /* Forum Section */
    .forum { 
        padding: 70px 40px; 
        max-width: 950px; 
        margin: 0 auto; 
        background: rgba(255, 255, 255, 0.15); 
        border-radius: 25px; 
        backdrop-filter: blur(12px); 
        border: 1px solid rgba(0, 0, 0, 0.05); 
    }
    .forum h1 { 
        font-size: 42px; 
        color: var(--primary); 
        margin-bottom: 50px; 
        text-align: center; 
        position: relative; 
        font-weight: 700; 
        text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.1); 
        animation: fadeIn 1s ease-out; 
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .forum h1::after { 
        content: ''; 
        width: 70px; 
        height: 5px; 
        background: var(--secondary); 
        position: absolute; 
        bottom: -15px; 
        left: 50%; 
        transform: translateX(-50%); 
        border-radius: 5px; 
        box-shadow: 0 2px 5px rgba(var(--secondary), 0.3); 
    }

    /* Thread Form */
    .forum .thread-form { 
        background: linear-gradient(145deg, #ffffff, rgba(230, 243, 245, 0.8)); 
        padding: 30px; 
        border-radius: 20px; 
        margin-bottom: 35px; 
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1); 
        position: relative; 
        overflow: hidden; 
        animation: slideIn 0.6s ease-out; 
    }
    @keyframes slideIn {
        from { transform: translateX(-60px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .forum .thread-form::before { 
        content: ''; 
        position: absolute; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: radial-gradient(circle at top left, rgba(var(--secondary), 0.1), transparent 70%); 
        z-index: -1; 
    }
    .forum .thread-form h2 { 
        font-size: 28px; 
        color: var(--primary); 
        margin-bottom: 25px; 
        position: relative; 
        font-weight: 600; 
    }
    .forum .thread-form h2::before { 
        content: '\f075'; 
        font-family: 'Font Awesome 5 Free'; 
        font-weight: 900; 
        color: var(--secondary); 
        margin-right: 15px; 
        animation: pulse 2s infinite; 
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    .forum .thread-form input, 
    .forum .thread-form textarea, 
    .forum .thread-form input[type="file"] { 
        width: 100%; 
        padding: 14px; 
        margin-bottom: 18px; 
        border: 2px solid var(--primary); 
        border-radius: 12px; 
        background: #f9fbfd; 
        transition: all 0.4s ease; 
        font-size: 15px; 
    }
    .forum .thread-form input:focus, 
    .forum .thread-form textarea:focus { 
        border-color: var(--secondary); 
        box-shadow: 0 0 12px rgba(var(--secondary), 0.4); 
        outline: none; 
    }
    .forum .thread-form input[type="file"] { 
        padding: 8px; 
    }
    .forum .thread-form textarea { 
        height: 150px; 
        resize: vertical; 
    }
    .forum .thread-form button { 
        padding: 14px 30px; 
        background: var(--secondary); 
        border: none; 
        border-radius: 30px; 
        cursor: pointer; 
        color: var(--accent); 
        font-weight: 600; 
        transition: all 0.4s ease; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
    }
    .forum .thread-form button:hover { 
        transform: scale(1.07); 
        background: var(--primary); 
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3); 
    }

    /* Thread Card */
    .forum .thread-list .thread-card { 
        background: linear-gradient(145deg, #ffffff, #e6f3f5); 
        padding: 25px; 
        border-radius: 20px; 
        margin-bottom: 30px; 
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); 
        border-left: 6px solid var(--primary); 
        position: relative; 
        overflow: hidden; 
        animation: slideIn 0.6s ease-out; 
    }
    .forum .thread-card::before { 
        content: ''; 
        position: absolute; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 5px; 
        background: var(--secondary); 
        border-radius: 20px 20px 0 0; 
    }
    .forum .thread-card h3 { 
        font-size: 24px; 
        color: var(--primary); 
        margin-bottom: 15px; 
        position: relative; 
        font-weight: 600; 
        padding-left: 30px; 
    }
    .forum .thread-card h3::before { 
        content: '\f0e6'; 
        font-family: 'Font Awesome 5 Free'; 
        font-weight: 900; 
        color: var(--secondary); 
        position: absolute; 
        left: 0; 
        top: 50%; 
        transform: translateY(-50%); 
        font-size: 22px; 
    }
    .forum .thread-card p { 
        font-size: 16px; 
        color: #444; 
        margin-bottom: 20px; 
        padding: 18px; 
        border-radius: 15px; 
        background: rgba(var(--secondary), 0.1); 
        border-left: 6px solid var(--primary); 
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05); 
        position: relative; 
        line-height: 1.8; 
        animation: popIn 0.6s ease-out; 
    }
    @keyframes popIn {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .forum .thread-card p::before { 
        content: '\f27a'; 
        font-family: 'Font Awesome 5 Free'; 
        font-weight: 900; 
        color: var(--secondary); 
        position: absolute; 
        top: 12px; 
        right: 12px; 
        opacity: 0.8; 
        font-size: 20px; 
    }
    .forum .thread-card .thread-image { 
        max-width: 100%; 
        height: auto; 
        border-radius: 15px; 
        margin: 0 auto 20px; 
        display: block; 
        border: 3px solid rgba(var(--primary), 0.5); 
        transition: transform 0.5s ease; 
    }
    .forum .thread-card .thread-image:hover { 
        transform: scale(1.05); 
    }
    .forum .thread-card .meta { 
        font-size: 14px; 
        color: var(--primary); 
        margin-bottom: 20px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        background: rgba(var(--primary), 0.08); 
        padding: 10px 15px; 
        border-radius: 10px; 
        font-weight: 500; 
    }
    .forum .thread-card .meta .thread-actions { 
        display: flex; 
        gap: 15px; 
    }

    /* Actions (Like, Reply) */
    .forum .thread-card .actions { 
        display: flex; 
        gap: 25px; 
        margin-bottom: 20px; 
        align-items: center; 
        padding: 12px; 
        background: rgba(var(--secondary), 0.08); 
        border-radius: 10px; 
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); 
    }
    .forum .thread-card .actions .like-button { 
        display: flex; 
        align-items: center; 
        gap: 10px; 
    }
    .forum .thread-card .actions .like-button button { 
        background: none; 
        border: none; 
        cursor: pointer; 
        font-size: 18px; 
        transition: transform 0.4s ease; 
    }
    .forum .thread-card .actions .like-button button i { 
        color: var(--primary); 
    }
    .forum .thread-card .actions .like-button button.liked i { 
        color: #ff4d4d; 
        animation: heartBeat 0.6s ease; 
    }
    @keyframes heartBeat {
        0% { transform: scale(1); }
        50% { transform: scale(1.4); }
        100% { transform: scale(1); }
    }
    .forum .thread-card .actions .like-button button:hover i { 
        color: var(--secondary); 
        transform: scale(1.2); 
    }
    .forum .thread-card .actions .like-count { 
        font-size: 15px; 
        color: var(--primary); 
        font-weight: 600; 
        background: rgba(var(--accent), 0.9); 
        padding: 4px 12px; 
        border-radius: 15px; 
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); 
    }
    .forum .thread-card .actions button.reply-btn { 
        background: none; 
        border: none; 
        color: var(--primary); 
        cursor: pointer; 
        font-size: 15px; 
        font-weight: 600; 
        transition: all 0.4s ease; 
        padding: 8px 15px; 
        border-radius: 20px; 
    }
    .forum .thread-card .actions button.reply-btn:hover { 
        color: var(--accent); 
        background: var(--secondary); 
        transform: scale(1.07); 
    }

    /* Comments Section */
    .forum .thread-card .comments { 
        margin-top: 25px; 
        border-top: 2px solid rgba(var(--primary), 0.1); 
        padding-top: 20px; 
    }
    .forum .thread-card .comment { 
        background: linear-gradient(145deg, #ffffff, #f0f4f8); 
        padding: 15px; 
        border-radius: 12px; 
        margin-bottom: 15px; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); 
        display: flex; 
        align-items: flex-start; 
        gap: 15px; 
        transition: transform 0.4s ease; 
    }
    .forum .thread-card .comment:hover { 
        transform: translateX(8px); 
    }
    .forum .thread-card .comment .profile-picture { 
        width: 35px; 
        height: 35px; 
        border-radius: 50%; 
        object-fit: cover; 
        border: 3px solid rgba(var(--secondary), 0.5); 
        transition: transform 0.4s ease; 
    }
    .forum .thread-card .comment .profile-picture:hover { 
        transform: scale(1.15); 
    }
    .forum .thread-card .comment .content { 
        flex-grow: 1; 
        background: rgba(var(--primary), 0.05); 
        padding: 10px; 
        border-radius: 10px; 
    }
    .forum .thread-card .comment .content p { 
        font-size: 14px; 
        color: #555; 
        margin-bottom: 8px; 
    }
    .forum .thread-card .comment .content .meta { 
        font-size: 13px; 
        color: var(--primary); 
        font-weight: 500; 
    }

    /* Reply Form */
    .forum .thread-card .reply-form { 
        display: none; 
        margin-top: 20px; 
        background: linear-gradient(145deg, #e6f3f5, #f5f6f0); 
        padding: 20px; 
        border-radius: 15px; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); 
        animation: slideIn 0.6s ease-out; 
    }
    .forum .thread-card .reply-form.active { 
        display: block; 
    }
    .forum .thread-card .reply-form textarea { 
        width: 100%; 
        padding: 14px; 
        border: 2px solid var(--primary); 
        border-radius: 12px; 
        background: #f9fbfd; 
        height: 100px; 
        resize: vertical; 
        transition: all 0.4s ease; 
    }
    .forum .thread-card .reply-form textarea:focus { 
        border-color: var(--secondary); 
        box-shadow: 0 0 12px rgba(var(--secondary), 0.4); 
        outline: none; 
    }
    .forum .thread-card .reply-form button { 
        padding: 12px 25px; 
        background: var(--secondary); 
        border: none; 
        border-radius: 25px; 
        cursor: pointer; 
        color: var(--accent); 
        font-weight: 600; 
        transition: all 0.4s ease; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
    }
    .forum .thread-card .reply-form button:hover { 
        transform: scale(1.07); 
        background: var(--primary); 
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3); 
    }

    /* Edit Form */
    .forum .thread-card .edit-form { 
        display: none; 
        margin-top: 20px; 
        background: linear-gradient(145deg, #e6f3f5, #f5f6f0); 
        padding: 20px; 
        border-radius: 15px; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); 
        animation: slideIn 0.6s ease-out; 
    }
    .forum .thread-card .edit-form.active { 
        display: block; 
    }
    .forum .thread-card .edit-form input, 
    .forum .thread-card .edit-form textarea, 
    .forum .thread-card .edit-form input[type="file"] { 
        width: 100%; 
        padding: 14px; 
        margin-bottom: 18px; 
        border: 2px solid var(--primary); 
        border-radius: 12px; 
        background: #f9fbfd; 
        transition: all 0.4s ease; 
        font-size: 15px; 
    }
    .forum .thread-card .edit-form input:focus, 
    .forum .thread-card .edit-form textarea:focus { 
        border-color: var(--secondary); 
        box-shadow: 0 0 12px rgba(var(--secondary), 0.4); 
        outline: none; 
    }
    .forum .thread-card .edit-form input[type="file"] { 
        padding: 8px; 
    }
    .forum .thread-card .edit-form textarea { 
        height: 150px; 
        resize: vertical; 
    }
    .forum .thread-card .edit-form button { 
        padding: 12px 25px; 
        background: var(--secondary); 
        border: none; 
        border-radius: 25px; 
        cursor: pointer; 
        color: var(--accent); 
        font-weight: 600; 
        transition: all 0.4s ease; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
    }
    .forum .thread-card .edit-form button:hover { 
        transform: scale(1.07); 
        background: var(--primary); 
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3); 
    }

    /* Icons */
    .delete-icon { 
        background: none; 
        border: none; 
        color: #ff4d4d; 
        font-size: 18px; 
        cursor: pointer; 
        transition: all 0.4s ease; 
        padding: 5px; 
    }
    .delete-icon:hover { 
        color: #cc0000; 
        transform: rotate(10deg) scale(1.15); 
    }
    .edit-icon { 
        background: none; 
        border: none; 
        color: var(--secondary); 
        font-size: 18px; 
        cursor: pointer; 
        transition: all 0.4s ease; 
        padding: 5px; 
    }
    .edit-icon:hover { 
        color: var(--primary); 
        transform: rotate(-10deg) scale(1.15); 
    }

    /* Alert, Success, Error */
    .alert { 
        background: linear-gradient(145deg, #ffffff, #e6f3f5); 
        padding: 18px; 
        border-radius: 12px; 
        border: 2px solid var(--primary); 
        display: flex; 
        align-items: center; 
        gap: 15px; 
        margin-bottom: 25px; 
        color: var(--primary); 
        font-size: 16px; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); 
        animation: fadeIn 0.6s ease-out; 
    }
    .alert i { 
        color: var(--secondary); 
        font-size: 22px; 
    }
    .alert a { 
        color: var(--secondary); 
        font-weight: 600; 
        text-decoration: none; 
        transition: color 0.4s ease; 
    }
    .alert a:hover { 
        color: var(--primary); 
        text-decoration: underline; 
    }
    .success { 
        color: var(--accent); 
        padding: 15px; 
        margin-bottom: 25px; 
        border-radius: 12px; 
        background: var(--primary); 
        text-align: center; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
        animation: slideIn 0.6s ease-out; 
    }
    .error { 
        color: var(--accent); 
        padding: 15px; 
        margin-bottom: 25px; 
        border-radius: 12px; 
        background: #e74c3c; 
        text-align: center; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); 
        animation: slideIn 0.6s ease-out; 
    }

    /* Footer */
    footer { 
        background: linear-gradient(90deg, var(--primary), darken(var(--primary), 15%)); 
        color: var(--accent); 
        padding: 35px; 
        text-align: center; 
        box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.3); 
    }
    footer a { 
        color: var(--secondary); 
        text-decoration: none; 
        font-weight: 600; 
        transition: color 0.4s ease; 
    }
    footer a:hover { 
        color: var(--accent); 
    }

    /* Responsivitas */
    @media (max-width: 768px) {
        .forum { padding: 50px 20px; }
        .forum h1 { font-size: 32px; }
        .forum .thread-form { padding: 20px; }
        .forum .thread-form h2 { font-size: 22px; }
        .forum .thread-card { padding: 20px; }
        .forum .thread-card h3 { font-size: 20px; }
        .forum .thread-card p { font-size: 15px; padding: 12px; }
        .forum .thread-card .actions { flex-direction: column; gap: 12px; }
        .forum .thread-card .meta { flex-direction: column; gap: 8px; }
        header { padding: 15px 20px; }
        header .logo { font-size: 26px; }
        header nav ul { gap: 15px; }
        header nav ul li a { font-size: 15px; padding: 8px 12px; }
    }
    @media (max-width: 480px) {
        .forum { max-width: 100%; padding: 30px 10px; }
        .forum .thread-form input, .forum .thread-form textarea { font-size: 14px; }
        .forum .thread-card .actions button.reply-btn { font-size: 13px; }
        .forum .thread-card .meta { font-size: 13px; }
    }
</style>
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="forum">
        <h1>Forum</h1>
        <?php if (isset($_GET['success'])): ?>
            <div class="success">Thread berhasil dibuat.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <?php if ($isLoggedIn): ?>
        <div class="thread-form">
            <h2>Buat Diskusi Baru</h2>
            <form method="POST" action="forum.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="addThread">
                <input type="text" name="judul" placeholder="Judul Diskusi" required>
                <textarea name="isi" placeholder="Isi Diskusi" required></textarea>
                <input type="file" name="gambar" accept="image/jpeg,image/png">
                <button type="submit">Kirim</button>
            </form>
        </div>
        <?php else: ?>
        <div class="alert">
            <i class="fas fa-info-circle"></i>
            <span>Silakan <a href="login-register.php">Masuk/Daftar</a> untuk membuat diskusi</span>
        </div>
        <?php endif; ?>

        <div class="thread-list">
            <?php foreach ($threads as $thread): ?>
            <div class="thread-card">
                <div class="meta">
                    <span>Oleh: <?php echo htmlspecialchars($thread['penulis']); ?> | <?php echo date('d F Y', strtotime($thread['created_at'])); ?></span>
                    <?php if ($isLoggedIn && $thread['pengguna_id'] == $pengguna_id): ?>
                    <div class="thread-actions">
                        <form method="POST" action="forum.php" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus thread ini? Tindakan ini tidak dapat dibatalkan');">
                            <input type="hidden" name="action" value="deleteThread">
                            <input type="hidden" name="thread_id" value="<?php echo $thread['id']; ?>">
                            <button type="submit" class="delete-icon" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        <button onclick="toggleEditForm(this, <?php echo $thread['id']; ?>)" class="edit-icon" title="Ubah thread">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <h3 class="text-center"><?php echo htmlspecialchars($thread['judul']); ?></h3>
                <?php if ($thread['gambar']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($thread['gambar']); ?>" alt="Gambar Thread" class="thread-image">
                <?php endif; ?>
                <p><?php echo htmlspecialchars($thread['isi']); ?></p>
                <div class="actions">
                    <?php if ($isLoggedIn): ?>
                    <form method="POST" action="forum.php" style="display: inline;">
                        <input type="hidden" name="action" value="like">
                        <input type="hidden" name="thread_id" value="<?php echo $thread['id']; ?>">
                        <div class="like-button">
                            <button type="submit" class="<?php echo $likedThreads[$thread['id']] ? 'liked' : ''; ?>">
                                <i class="<?php echo $likedThreads[$thread['id']] ? 'fas fa-heart' : 'far fa-heart'; ?>"></i>
                            </button>
                            <span class="like-count"><?php echo $thread['suka']; ?></span>
                        </div>
                    </form>
                    <button class="reply-btn" onclick="toggleReplyForm(this, <?php echo $thread['id']; ?>)"><i class="fas fa-reply"></i> Balas</button>
                    <?php endif; ?>
                </div>
                <div class="comments">
                    <?php if (isset($comments[$thread['id']])): ?>
                        <?php foreach ($comments[$thread['id']] as $comment): ?>
                        <div class="comment">
                            <img src="<?php echo $comment['comment_profile_picture'] ? 'img/' . htmlspecialchars($comment['comment_profile_picture']) : 'img/default.jpeg'; ?>" alt="Foto Profil" class="profile-picture">
                            <div class="content">
                                <p><?php echo htmlspecialchars($comment['isi']); ?></p>
                                <div class="meta">Oleh: <?php echo htmlspecialchars($comment['penulis_komentar']); ?> | <?php echo date('d F Y H:i', strtotime($comment['created_at'])); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if ($isLoggedIn): ?>
                <form method="POST" action="forum.php" class="reply-form" id="reply-form-<?php echo $thread['id']; ?>">
                    <input type="hidden" name="action" value="addReply">
                    <input type="hidden" name="thread_id" value="<?php echo $thread['id']; ?>">
                    <textarea name="isi" placeholder="Isi Balasan" required></textarea>
                    <button type="submit">Kirim</button>
                </form>
                <form method="POST" action="forum.php" class="edit-form" id="edit-form-<?php echo $thread['id']; ?>" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="editThread">
                    <input type="hidden" name="thread_id" value="<?php echo $thread['id']; ?>">
                    <input type="text" name="judul" value="<?php echo htmlspecialchars($thread['judul']); ?>" required>
                    <textarea name="isi" required><?php echo htmlspecialchars($thread['isi']); ?></textarea>
                    <input type="file" name="gambar" accept="image/jpeg,image/png">
                    <?php if ($thread['gambar']): ?>
                        <p>Gambar saat ini: <img src="uploads/<?php echo htmlspecialchars($thread['gambar']); ?>" alt="Gambar Saat Ini" style="max-width: 100px; border-radius: 5px;"></p>
                    <?php endif; ?>
                    <button type="submit">Kirim</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php require_once 'footer.php'; ?>
    <script>
        function toggleReplyForm(button, threadId) {
            const form = document.getElementById(`reply-form-${threadId}`);
            if (form) {
                form.classList.toggle('active');
                if (form.classList.contains('active')) {
                    form.querySelector('textarea').focus(); // Fokus ke textarea saat form muncul
                }
            } else {
                console.error('Form reply tidak ditemukan untuk thread ID:', threadId);
            }
        }

        function toggleEditForm(button, threadId) {
            const form = document.getElementById(`edit-form-${threadId}`);
            form.classList.toggle('active');
        }
    </script>
</body>
</html>