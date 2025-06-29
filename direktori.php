<?php
require_once 'config/config.php';
require_once 'psnetbot.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';

$stmt = $conn->query("SELECT * FROM pesantren");
$pesantrens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses hapus pesantren
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && $isLoggedIn && $userRole === 'pengelola') {
    $pesantren_id = $_POST['pesantren_id'] ?? '';
    if ($pesantren_id) {
        try {
            $stmt = $conn->prepare("SELECT * FROM pengelola_pesantren WHERE pengguna_id = ? AND pesantren_id = ?");
            $stmt->execute([$_SESSION['user_id'], $pesantren_id]);
            $canDelete = $stmt->fetch(PDO::FETCH_ASSOC) !== false;
            if ($canDelete) {
                $conn->beginTransaction();
                // Hapus entri di pengelola_pesantren
                $stmt = $conn->prepare("DELETE FROM pengelola_pesantren WHERE pesantren_id = ?");
                $stmt->execute([$pesantren_id]);
                // Hapus pesantren
                $stmt = $conn->prepare("DELETE FROM pesantren WHERE id = ?");
                $stmt->execute([$pesantren_id]);
                $conn->commit();
                $success = $lang['delete_success'] ?? 'Pesantren berhasil dihapus.';
                header("Location: direktori.php?success=" . urlencode($success));
                exit;
            } else {
                error_log("Pengguna tidak memiliki izin untuk menghapus pesantren_id: $pesantren_id");
                $error = $lang['error_no_permission'] ?? 'Anda tidak memiliki izin untuk menghapus pesantren ini.';
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Gagal menghapus pesantren: " . $e->getMessage());
            $error = $lang['error_delete_failed'] ?? 'Gagal menghapus pesantren.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direktori Pesantren - IBSflow</title>
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

        /* Directory Section */
        .directory { 
            padding: 40px 20px; 
            max-width: 1100px; 
            margin: 0 auto; 
        }
        h1 { 
            font-size: 32px; 
            font-weight: 600; 
            color: #003087; 
            text-align: center; 
            margin-bottom: 30px; 
            position: relative; 
        }
        h1::after { 
            content: ''; 
            width: 40px; 
            height: 3px; 
            background: #4a90e2; 
            position: absolute; 
            bottom: -8px; 
            left: 50%; 
            transform: translateX(-50%); 
        }

        /* Filter Section */
        .filter-section { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin-bottom: 30px; 
        }
        .filter-section label { 
            font-size: 15px; 
            color: #003087; 
            margin-right: 10px; 
        }
        .filter-section select { 
            padding: 8px 15px; 
            border: 1px solid #4a90e2; 
            border-radius: 5px; 
            background: #fff; 
            color: #003087; 
            font-size: 14px; 
            cursor: pointer; 
            transition: border-color 0.3s ease; 
        }
        .filter-section select:focus { 
            border-color: #007bff; 
            outline: none; 
        }

        /* Directory Grid */
        .directory-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 20px; 
        }
        .directory-card { 
            background: #fff; 
            border-radius: 10px; 
            padding: 15px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
            position: relative; 
        }
        .directory-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15); 
        }
        .directory-card img { 
            width: 100%; 
            height: 150px; 
            object-fit: cover; 
            border-radius: 8px; 
            margin-bottom: 10px; 
        }
        .directory-card h3 { 
            font-size: 18px; 
            font-weight: 600; 
            color: #003087; 
            margin-bottom: 8px; 
        }
        .directory-card p { 
            font-size: 14px; 
            color: #555; 
            margin-bottom: 5px; 
        }
        .directory-card p span { 
            font-weight: 600; 
            color: #4a90e2; 
        }
        .directory-card a { 
            display: inline-block; 
            margin-top: 10px; 
            padding: 6px 12px; 
            background: #4a90e2; 
            color: #fff; 
            text-decoration: none; 
            border-radius: 5px; 
            font-size: 14px; 
            transition: background 0.3s ease; 
        }
        .directory-card a:hover { 
            background: #007bff; 
            color: #fff; 
        }
        .delete-icon { 
            position: absolute; 
            top: 10px; 
            right: 10px; 
            background: none; 
            border: none; 
            color: #e74c3c; 
            font-size: 16px; 
            cursor: pointer; 
            transition: color 0.3s ease; 
        }
        .delete-icon:hover { 
            color: #c0392b; 
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
            .directory { padding: 30px 15px; }
            .directory-grid { grid-template-columns: 1fr; }
            .directory-card img { height: 120px; }
            .filter-section select { font-size: 13px; padding: 6px 10px; }
            header { padding: 10px 15px; }
            header .logo { font-size: 20px; }
            header nav ul { gap: 10px; }
            header nav ul li a { font-size: 13px; padding: 5px 8px; }
        }
        @media (max-width: 480px) {
            h1 { font-size: 24px; }
            .directory-card h3 { font-size: 16px; }
            .directory-card p { font-size: 13px; }
            .directory-card a { font-size: 13px; padding: 5px 10px; }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="directory">
        <?php if (isset($_GET['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <h1>Direktori Pesantren</h1>
        <div class="filter-section">
            <label for="category">Filter Kategori:</label>
            <select id="category" onchange="filterPesantren()">
                <option value="all">Semua Kategori</option>
                <option value="tahfidz">Tahfidz</option>
                <option value="riset">Riset</option>
                <option value="salafi">Salafi</option>
                <option value="modern">Modern</option>
            </select>
        </div>
        <div class="directory-grid" id="pesantren-list">
            <?php foreach ($pesantrens as $pesantren): ?>
            <div class="directory-card" data-category="<?php echo strtolower(htmlspecialchars($pesantren['kategori'])); ?>">
                <img src="<?php echo $pesantren['gambar'] ? 'img/' . htmlspecialchars($pesantren['gambar']) : 'img/ibsdefault.jpg'; ?>" alt="<?php echo htmlspecialchars($pesantren['nama']); ?>">
                <h3><?php echo htmlspecialchars($pesantren['nama']); ?></h3>
                <p><span>Kategori:</span> <?php echo htmlspecialchars($pesantren['kategori']); ?></p>
                <p><span>Lokasi:</span> <?php echo htmlspecialchars($pesantren['lokasi']); ?></p>
                <a href="detailpondok.php?id=<?php echo $pesantren['id']; ?>">Lihat Detail</a>
                <?php if ($isLoggedIn && $userRole === 'pengelola'): ?>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM pengelola_pesantren WHERE pengguna_id = ? AND pesantren_id = ?");
                    $stmt->execute([$_SESSION['user_id'], $pesantren['id']]);
                    $canDelete = $stmt->fetch(PDO::FETCH_ASSOC) !== false;
                    ?>
                    <?php if ($canDelete): ?>
                        <form method="POST" action="direktori.php" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesantren ini? Tindakan ini tidak dapat dibatalkan');">
                            <input type="hidden" name="pesantren_id" value="<?php echo $pesantren['id']; ?>">
                            <input type="hidden" name="delete" value="1">
                            <button type="submit" class="delete-icon" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php require_once 'footer.php'; ?>

    <script>
        function filterPesantren() {
            const category = document.getElementById('category').value.toLowerCase();
            const cards = document.querySelectorAll('.directory-card');
            cards.forEach(card => {
                const cardCategory = card.getAttribute('data-category').toLowerCase();
                if (category === 'all' || cardCategory === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>