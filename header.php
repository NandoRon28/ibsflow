<?php
require_once 'config/config.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
$pengguna_id = $_SESSION['user_id'] ?? null;

// Ambil data pengguna untuk foto profil
$userData = null;
if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT nama, profile_picture FROM pengguna WHERE id = ?");
    $stmt->execute([$pengguna_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($selectedLang); ?>" dir="<?php echo $selectedLang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - IBSflow</title>
    <link rel="icon" type="image/png" href="img/logo_ibsflow.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {--primary: #000;--secondary: #01579B;--accent: #ffffff;}
        body {font-family: 'Poppins', sans-serif;background-color: #f5f6f0;color: #333;line-height: 1.6;}
        [dir="rtl"] {text-align: right;}
        [dir="rtl"] .navbar-nav {flex-direction: row-reverse;margin: 0 auto;}
        [dir="rtl"] .dropdown-menu {right: auto;left: 0;}
        /* Navbar */
        .navbar {background: linear-gradient(90deg, var(--primary), var(--secondary));box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);position: sticky;top: 0;z-index: 100;padding: 1rem 2rem;justify-content: center !important; /* Memusatkan item navigasi */}
        .navbar-brand {font-size: 28px;font-weight: 700;color: #fff;text-transform: uppercase;letter-spacing: 1px;}
        .navbar-brand:hover {color: var(--accent);}
        .nav-link {color: #fff !important;font-size: 16px;padding: 8px 15px !important;border-radius: 5px;transition: background 0.3s, color 0.3s;}
        .nav-link:hover {background: var(--accent);color: var(--primary) !important;}
        .cta-btn {background-color: var(--accent);padding: 10px 20px;border-radius: 25px;color: var(--primary);font-weight: bold;text-decoration: none;transition: transform 0.3s;}
        .cta-btn:hover {transform: scale(1.05);}
        .profile-icon {width: 45px;height: 45px;border-radius: 50%;object-fit: cover;border: 2px solid #fff;box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);transition: transform 0.3s;}
        .profile-icon:hover {transform: scale(1.1);}
        .language-selector {position: relative;}
        .language-icon {color: #fff;font-size: 24px;cursor: pointer;transition: color 0.3s;}
        .language-icon:hover {color: var(--accent);}
        .language-dropdown {display: none;position: absolute;top: 100%;right: 0;background: var(--primary);border-radius: 5px;box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);z-index: 1000;min-width: 60px;padding: 5px 0;}
        .language-selector:hover .language-dropdown {display: block;}
        .language-dropdown a {display: block;padding: 5px 10px;text-decoration: none;text-align: center;}
        .flag-icon {width: 30px;height: 20px;object-fit: cover;border-radius: 2px;transition: transform 0.3s;}
        .language-dropdown a:hover .flag-icon {transform: scale(1.1);}
        /* Header Navbar */
        .navbar-nav {margin: 0 auto; /* Memusatkan dan memberi jarak */}
        .nav-item {margin: 0 15px; /* Jarak antar item navigasi */}
        .navbar-brand {margin-right: 20px; /* Jarak logo dari navigasi */}
        .btn-masuk {margin-left: 20px; /* Jarak tombol Masuk/Daftar */}
        /* Footer */
        footer {background: linear-gradient(90deg, var(--secondary), var(--primary));color: #fff;}
        footer a {color: var(--accent);text-decoration: none;}
        footer a:hover {text-decoration: underline;}
        /* Alert */
        .alert {background: #d4e9e2;border: 1px solid var(--secondary);color: var(--primary);box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);}
        .alert.success {background: #2ecc71;color: #fff;}
        /* Responsive */
        @media (max-width: 768px) {.navbar-nav {text-align: center;}.language-dropdown {right: auto;left: 50%;transform: translateX(-50%);}}
        @media (max-width: 576px) {.navbar-nav {text-align: center;}.language-dropdown {right: auto;left: 50%;transform: translateX(-50%);}}
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="img/logo.png" alt="IBSflow Logo" style="height: 40px; margin-right: 10px; vertical-align: middle;">IBSflow
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="direktori.php">Direktori</a></li>
                    <li class="nav-item"><a class="nav-link" href="kegiatan.php">Kegiatan</a></li>
                    <li class="nav-item"><a class="nav-link" href="forum.php">Forum</a></li>
                    <li class="nav-item"><a class="nav-link" href="promosi.php">Promosi</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if ($isLoggedIn): ?>
                        <?php if ($userRole === 'admin'): ?>
                            <a href="admin.php" class="btn btn-warning cta-btn">Dashboard Admin</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-warning cta-btn">Keluar</a>
                        <a href="profile.php">
                            <img src="<?php echo $userData['profile_picture'] ? 'img/' . htmlspecialchars($userData['profile_picture']) : 'img/default.jpeg'; ?>" alt="Profil" class="profile-icon">
                        </a>
                    <?php else: ?>
                        <a href="login-register.php" class="btn btn-warning cta-btn">Masuk/Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>