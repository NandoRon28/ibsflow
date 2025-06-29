<?php
session_start();

// Konfigurasi waktu timeout (2 jam)
$session_timeout = 7200;

// Periksa sesi pengguna
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
        session_unset();
        session_destroy();
        header("Location: login-register.php?message=Sesi Anda telah berakhir. Silakan login kembali.");
        exit();
    }
    $_SESSION['last_activity'] = time();
}

// Konfigurasi database
$host = 'localhost';
$dbname = 'ibsflow';
$username = 'root'; // Ganti dengan pengguna non-root
$password = ''; // Ganti dengan password kuat

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Log aktivitas admin
function logAdminActivity($conn, $user_id, $action) {
    try {
        $stmt = $conn->prepare("INSERT INTO admin_log (user_id, action, timestamp) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $action]);
    } catch (PDOException $e) {
        error_log("Gagal mencatat log admin: " . $e->getMessage());
    }
}

// Periksa pesantren_id untuk pengelola
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'pengelola') {
    $stmt = $conn->prepare("SELECT pesantren_id FROM pengelola_pesantren WHERE pengguna_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $_SESSION['pesantren_id'] = $result['pesantren_id'];
    } else {
        $_SESSION['pesantren_id'] = null;
    }
}
?>