<?php
require_once 'config/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-register.php?error=Akses ditolak.");
    exit;
}

$promosi_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$stmt = $conn->prepare("SELECT * FROM promosi WHERE id = ?");
$stmt->execute([$promosi_id]);
$promosi = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token tidak valid.");
    }
    $judul = filter_input(INPUT_POST, 'judul', FILTER_SANITIZE_STRING);
    $deskripsi = filter_input(INPUT_POST, 'deskripsi', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $stmt = $conn->prepare("UPDATE promosi SET judul = ?, deskripsi = ?, status = ? WHERE id = ?");
    $stmt->execute([$judul, $deskripsi, $status, $promosi_id]);
    logAdminActivity($conn, $_SESSION['user_id'], "Edit promosi ID $promosi_id");
    header("Location: admin.php?success=Berhasil mengedit promosi.");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Promosi</title>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f5f6f0; padding: 20px; }
        .form { max-width: 500px; margin: 0 auto; background: #d4e9e2; padding: 20px; border-radius: 10px; }
        input, textarea, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #d4e9e2; border-radius: 5px; }
        textarea { height: 150px; resize: none; }
        button { padding: 10px 20px; background: #f4c430; border: none; border-radius: 25px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="form">
        <h2>Edit Promosi</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="text" name="judul" value="<?php echo htmlspecialchars($promosi['judul']); ?>" required>
            <textarea name="deskripsi" required><?php echo htmlspecialchars($promosi['deskripsi']); ?></textarea>
            <select name="status" required>
                <option value="draft" <?php echo $promosi['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                <option value="published" <?php echo $promosi['status'] === 'published' ? 'selected' : ''; ?>>Dipublikasikan</option>
                <option value="archived" <?php echo $promosi['status'] === 'archived' ? 'selected' : ''; ?>>Diarsipkan</option>
            </select>
            <button type="submit">Simpan</button>
        </form>
    </div>
</body>
</html>