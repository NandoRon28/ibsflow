<?php
require_once 'config/config.php';
require_once 'psnetbot.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$action = $_POST['action'] ?? '';

if ($action === 'register') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);
    $role = $_POST['role'] ?? '';
    $pesantren_id = $_POST['pesantren_id'] ?? null;
    $security_question = $_POST['security_question'] ?? '';
    $security_answer = $_POST['security_answer'] ?? '';
    $verification_doc = null;

    if ($role === 'santri' && !$pesantren_id) {
        header("Location: login-register.php?error=" . urlencode($lang['select_pesantren_error']));
        exit();
    }

    if (empty($security_question) || empty($security_answer)) {
        header("Location: login-register.php?error=" . urlencode($lang['security_question_error']));
        exit();
    }

    if ($role === 'pengelola' && isset($_FILES['verification_doc']) && $_FILES['verification_doc']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['verification_doc']['tmp_name'];
        $file_name = $_FILES['verification_doc']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_size = $_FILES['verification_doc']['size'];
        $max_size = 5 * 1024 * 1024; // 5MB dalam bytes
        $allowed_ext = ['pdf'];

        if (!in_array($file_ext, $allowed_ext)) {
            header("Location: login-register.php?error=" . urlencode($lang['invalid_file_type'] ?? "Format dokumen harus PDF."));
            exit();
        }

        if ($file_size > $max_size) {
            header("Location: login-register.php?error=" . urlencode($lang['file_too_large'] ?? "Mohon maaf, file yang Anda kirim terlalu besar. Maksimal 5MB."));
            exit();
        }

        $new_file_name = uniqid() . '.' . $file_ext;
        $upload_path = 'uploads/verification/' . $new_file_name;
        if (!file_exists('uploads/verification')) {
            mkdir('uploads/verification', 0777, true);
        }
        if (!move_uploaded_file($file_tmp, $upload_path)) {
            header("Location: login-register.php?error=" . urlencode("Gagal mengunggah dokumen verifikasi."));
            exit();
        }
        $verification_doc = $new_file_name;
    } elseif ($role === 'pengelola' && (!isset($_FILES['verification_doc']) || $_FILES['verification_doc']['error'] !== UPLOAD_ERR_OK)) {
        header("Location: login-register.php?error=" . urlencode("Dokumen verifikasi wajib diunggah untuk peran pengelola."));
        exit();
    }

    $status = $role === 'santri' ? 'verified' : 'pending';

    try {
        $stmt = $conn->prepare("INSERT INTO pengguna (nama, email, password, role, pesantren_id, security_question, security_answer, status, verification_doc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $email, $password, $role, $role === 'santri' ? $pesantren_id : null, $security_question, $security_answer, $status, $verification_doc]);
        header("Location: login-register.php?success=" . urlencode($lang['register_success']));
    } catch (PDOException $e) {
        header("Location: login-register.php?error=" . urlencode($lang['register_error'] . ": {$e->getMessage()}"));
    }
    exit();
} elseif ($action === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $conn->prepare("SELECT * FROM pengguna WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === 'admin' || $user['status'] === 'verified') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                if ($user['role'] === 'pengelola') {
                    $stmt = $conn->prepare("SELECT pesantren_id FROM pengelola_pesantren WHERE pengguna_id = ?");
                    $stmt->execute([$user['id']]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['pesantren_id'] = $result['pesantren_id'] ?? null;
                } else {
                    $_SESSION['pesantren_id'] = $user['pesantren_id'];
                }
                header("Location: index.php");
            } else {
                header("Location: login-register.php?error=" . urlencode("Akun Anda belum diverifikasi oleh admin."));
            }
        } else {
            header("Location: login-register.php?error=" . urlencode($lang['login_error']));
        }
    } catch (PDOException $e) {
        header("Location: login-register.php?error=" . urlencode($lang['register_error'] . ": {$e->getMessage()}"));
    }
    exit();
}

try {
    $stmt = $conn->query("SELECT id, nama FROM pesantren");
    $pesantrens = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die($lang['fetch_pesantren_error'] . ": " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - IBSflow</title>
    <link rel="icon" type="image/png" href="img/logo_ibsflow.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body{
            display: flex;
            flex-direction: column;
        }
        footer {
        background: #2e856e;
        color: #fff;
        text-align: center;
        padding: 20px;
        font-size: 14px;
        font-family: "Poppins", sans-serif;
        position: relative;
        bottom: 0;
        width: 100%;
    }

    footer p {
        margin: 0;
    }
    </style>
    <link rel="stylesheet" href="css/SignUp_LogIn_Form.css">
</head>
<body>
    <div class="container">
        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1>Selamat Datang</h1>
                <h1>di IBSFlow</h1>
                <p style="color: snow;" >Belum Punya Akun ?</p>
                <button class="btn register-btn">Daftar</button>
                <button  class="btn home-btn "><a href="index.php" style="text-decoration: none;color:snow">Kembali</a></button>
            </div>
            <div class="toggle-panel toggle-right">
                <h1>Selamat Kembali</h1>
                <p style="color: snow;" >Sudah punya akun?</p>
                <button class="btn login-btn">Masuk</button>
                <button  class="btn home-btn "><a href="index.php" style="text-decoration: none;color:snow">Kembali</a></button>
            </div>
        </div>
        <form class="form-box login" action="login-register.php" method="POST">
            <input type="hidden" name="action" value="login">
            <h1>Masuk</h1>
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
                <i class="fas fa-envelope"></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" id="password" placeholder="Kata Sandi" required>
                <i class="fas fa-lock"></i>
            </div>
            <button type="submit" class="btn"><a href="forgot-password.php" style="text-decoration: none;color:snow">Lupa Kata Sandi</a></button>
            <button type="submit" class="btn">Masuk</button>
            <!-- <div class="social-icons">
                <a href="social-login.php?platform=google"><i class="fab fa-google"></i></a>
                <a href="social-login.php?platform=facebook"><i class="fab fa-facebook"></i></a>
                <a href="social-login.php?platform=twitter"><i class="fab fa-twitter"></i></a>
                <a href="social-login.php?platform=linkedin"><i class="fab fa-linkedin"></i></a>
            </div> -->
        </form>
        <form class="form-box register" action="login-register.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="register">
            <h1>Daftar</h1>
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <div class="input-box">
                <input type="text" name="nama" placeholder="Nama" required>
                <i class="fas fa-user"></i>
            </div>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
                <i class="fas fa-envelope"></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" id="password-register" placeholder="Kata Sandi" required>
                <i class="fas fa-lock"></i>
            </div>
            <div class="input-box">
                <select name="role" id="role" onchange="togglePesantrenField()" required>
                    <option value="" disabled selected>Daftar sebagai</option>
                    <option value="santri">Santri</option>
                    <option value="pengelola">Pengelola</option>
                </select>
                <i class="fas fa-users"></i>
            </div>
            <div class="input-box" id="pesantren-field" style="display: none;">
                <select name="pesantren_id" id="pesantren">
                    <option value="" disabled selected>Pilih pesantren</option>
                    <?php foreach ($pesantrens as $pesantren): ?>
                        <option value="<?php echo $pesantren['id']; ?>"><?php echo htmlspecialchars($pesantren['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="fas fa-school"></i>
            </div>
            <div class="input-box">
                <select name="security_question" id="security_question" required>
                    <option value="" disabled selected>Pilih pertanyaan</option>
                    <option value="What is the name of your first pet?">Apa nama hewan peliharaan pertama Anda?</option>
                    <option value="In which city were you born?">Di kota mana Anda lahir?</option>
                    <option value="Who was your first teacher?">Siapa guru pertama Anda?</option>
                </select>
                <i class="fas fa-question"></i>
            </div>
            <div class="input-box">
                <input type="text" name="security_answer" placeholder="Jawaban" required>
                <i class="fas fa-reply"></i>
            </div>
            <div class="input-box" id="verification-field" style="display: none;">
                <p class="file-warning">Hanya file PDF yang diterima dengan ukuran maksimal 5MB.</p>
                <input type="file" name="verification_doc" id="verification_doc" accept=".pdf">
                <i class="fas fa-file-pdf"></i>
            </div>
            <button type="submit" class="btn">Daftar</button>
            
            <!-- <div class="social-icons">
                <a href="#"><i class="fab fa-google"></i></a>
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
            </div> -->
        </form>
    </div>
    
    <script src="js/SignUp_LogIn_Form.js"></script>
    <script>
        function togglePesantrenField() {
            const role = document.getElementById('role').value;
            const pesantrenField = document.getElementById('pesantren-field');
            const pesantrenSelect = document.getElementById('pesantren');
            const verificationField = document.getElementById('verification-field');
            const verificationDocInput = document.getElementById('verification_doc');

            pesantrenField.style.display = role === 'santri' ? 'block' : 'none';
            pesantrenSelect.required = role === 'santri';
            verificationField.style.display = role === 'pengelola' ? 'block' : 'none';
            verificationDocInput.required = role === 'pengelola';
        }

        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = passwordField.nextElementSibling;
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-lock');
                toggleIcon.classList.add('fa-unlock');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-unlock');
                toggleIcon.classList.add('fa-lock');
            }
        }

        document.getElementById('verification_doc').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            const allowedExt = ['pdf'];

            if (file) {
                const fileExt = file.name.split('.').pop().toLowerCase();
                if (!allowedExt.includes(fileExt)) {
                    alert('Hanya file PDF yang diterima');
                    e.target.value = '';
                    return;
                }
                if (file.size > maxSize) {
                    alert('Mohon maaf, file yang Anda kirim terlalu besar. Maksimal 5MB');
                    e.target.value = '';
                    return;
                }
            }
        });
    </script>
</body>
</html>