<?php
require_once 'config/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-register.php?error=Akses ditolak.");
    exit;
}
$pesantren_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$stmt = $conn->prepare("SELECT * FROM pesantren WHERE id = ?");
$stmt->execute([$pesantren_id]);
$pesantren = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING);
    $kategori = filter_input(INPUT_POST, 'kategori', FILTER_SANITIZE_STRING);
    $lokasi = filter_input(INPUT_POST, 'lokasi', FILTER_SANITIZE_STRING);
    $stmt = $conn->prepare("UPDATE pesantren SET nama = ?, kategori = ?, lokasi = ? WHERE id = ?");
    $stmt->execute([$nama, $kategori, $lokasi, $pesantren_id]);
    logAdminActivity($conn, $_SESSION['user_id'], "Edit pesantren ID $pesantren_id");
    header("Location: direktori.php?success=Berhasil mengedit pesantren.");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pesantren</title>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f5f6f0; padding: 20px; }
        .form { max-width: 500px; margin: 0 auto; background: #d4e9e2; padding: 20px; border-radius: 10px; }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #d4e9e2; border-radius: 5px; }
        button { padding: 10px 20px; background: #f4c430; border: none; border-radius: 25px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="form">
        <h2>Edit Pesantren</h2>
        <form method="POST">
            <input type="text" name="nama" value="<?php echo htmlspecialchars($pesantren['nama']); ?>" required>
            <select name="kategori" required>
                <option value="Tahfidz" <?php echo $pesantren['kategori'] === 'Tahfidz' ? 'selected' : ''; ?>>Tahfidz</option>
                <option value="Riset" <?php echo $pesantren['kategori'] === 'Riset' ? 'selected' : ''; ?>>Riset</option>
                <option value="Salafi" <?php echo $pesantren['kategori'] === 'Salafi' ? 'selected' : ''; ?>>Salafi</option>
                <option value="Modern" <?php echo $pesantren['kategori'] === 'Modern' ? 'selected' : ''; ?>>Modern</option>
            </select>
            <input type="text" name="lokasi" value="<?php echo htmlspecialchars($pesantren['lokasi']); ?>" required>
            <button type="submit">Simpan</button>
        </form>
    </div>
</body>
</html>