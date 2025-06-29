<?php
require_once 'config/config.php';
require_once 'psnetbot.php';

// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
$step = $_GET['step'] ?? 'email';
$reset_email = $_SESSION['reset_email'] ?? '';
$security_question = '';

// Debug: Log status session
error_log("Session reset_email: " . ($reset_email ?: 'tidak ada'));

if ($step === 'email' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    error_log("Received email: '$email'");
    if (empty($email)) {
        header("Location: forgot-password.php?step=email&error=" . urlencode($lang['email_empty_error']));
        exit();
    }

    $email = strtolower($email);
    try {
        $stmt = $conn->prepare("SELECT * FROM pengguna WHERE LOWER(email) = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            error_log("Email tidak ditemukan: $email");
            header("Location: forgot-password.php?step=email&error=" . urlencode($lang['email_not_found']));
        } else {
            $_SESSION['reset_email'] = $email;
            error_log("Session reset_email diset ke: $email");
            header("Location: forgot-password.php?step=answer_question");
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header("Location: forgot-password.php?step=email&error=" . urlencode($lang['process_failed'] . ": {$e->getMessage()}"));
    }
    exit();
}

if ($step === 'answer_question' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($reset_email)) {
        error_log("Session reset_email hilang di langkah answer_question");
        header("Location: forgot-password.php?step=email&error=" . urlencode($lang['session_expired']));
        exit();
    }

    $answer = trim($_POST['answer'] ?? '');
    error_log("Jawaban yang diterima: '$answer'");
    try {
        $stmt = $conn->prepare("SELECT * FROM pengguna WHERE email = ?");
        $stmt->execute([$reset_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $stored_answer = trim($user['security_answer']);
            error_log("Jawaban tersimpan: '$stored_answer'");
            if (strtolower($stored_answer) === strtolower($answer)) {
                error_log("Jawaban cocok, redirect ke reset_password");
                header("Location: forgot-password.php?step=reset_password");
            } else {
                error_log("Jawaban tidak cocok");
                header("Location: forgot-password.php?step=answer_question&error=" . urlencode($lang['wrong_answer']));
            }
        } else {
            error_log("Pengguna tidak ditemukan untuk email: $reset_email");
            header("Location: forgot-password.php?step=email&error=" . urlencode($lang['email_not_found']));
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header("Location: forgot-password.php?step=answer_question&error=" . urlencode($lang['process_failed'] . ": {$e->getMessage()}"));
    }
    exit();
}

if ($step === 'reset_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($reset_email)) {
        error_log("Session reset_email hilang di langkah reset_password");
        header("Location: forgot-password.php?step=email&error=" . urlencode($lang['session_expired']));
        exit();
    }

    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if ($new_password !== $confirm_password) {
        header("Location: forgot-password.php?step=reset_password&error=" . urlencode($lang['password_mismatch']));
        exit();
    }
    try {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE pengguna SET password = ? WHERE email = ?");
        $stmt->execute([$hashed_password, $reset_email]);
        unset($_SESSION['reset_email']);
        header("Location: forgot-password.php?success=" . urlencode($lang['password_reset_success']));
    } catch (PDOException $e) {
        header("Location: forgot-password.php?step=reset_password&error=" . urlencode($lang['process_failed'] . ": {$e->getMessage()}"));
    }
    exit();
}

if ($step === 'answer_question' && $reset_email) {
    try {
        $stmt = $conn->prepare("SELECT security_question FROM pengguna WHERE email = ?");
        $stmt->execute([$reset_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $security_question = $user['security_question'] ?? '';
        if (empty($security_question)) {
            header("Location: forgot-password.php?step=email&error=" . urlencode($lang['security_question_not_found']));
            exit();
        }
    } catch (PDOException $e) {
        header("Location: forgot-password.php?step=email&error=" . urlencode($lang['process_failed'] . ": {$e->getMessage()}"));
    }
} elseif ($step === 'answer_question' && !$reset_email) {
    error_log("Session reset_email tidak ada saat masuk ke langkah answer_question");
    header("Location: forgot-password.php?step=email&error=" . urlencode($lang['session_expired']));
    exit();
}

?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($selectedLang); ?>" dir="<?php echo $selectedLang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - PSNet</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        html, body { height: 100%; margin: 0; }
        body { background-color: #f5f6f0; color: #333; line-height: 1.6; display: flex; flex-direction: column; min-height: 100vh; }
        [dir="rtl"] { text-align: right; }
        [dir="rtl"] .header-right { flex-direction: row-reverse; }
        [dir="rtl"] .language-dropdown { right: auto; left: 0; }
        [dir="rtl"] .auth { text-align: right; }
        header { background: linear-gradient(90deg, #1a3c34 0%, #2e856e 100%); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); position: sticky; top: 0; z-index: 100; }
        header .logo { font-size: 28px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 1px; }
        header nav ul { list-style: none; display: flex; gap: 25px; }
        header nav ul li a { color: #fff; text-decoration: none; font-size: 16px; padding: 8px 15px; border-radius: 5px; transition: background 0.3s, color 0.3s; }
        header nav ul li a:hover { background: #f4c430; color: #1a3c34; }
        .cta-btn { background-color: #f4c430; padding: 10px 20px; border-radius: 25px; color: #1a3c34; font-weight: bold; text-decoration: none; transition: transform 0.3s; }
        .cta-btn:hover { transform: scale(1.05); }
        .auth { padding: 60px 40px; text-align: center; background: #fff; flex: 1 0 auto; }
        .auth h1 { font-size: 36px; color: #1a3c34; margin-bottom: 40px; }
        .auth form { max-width: 400px; margin: 0 auto; background: #d4e9e2; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
        .auth label { display: block; font-size: 14px; color: #555; margin-bottom: 5px; }
        .auth input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #d4e9e2; border-radius: 5px; background: #f5f6f0; }
        .password-container { position: relative; }
        .password-container input { padding-right: 40px; }
        .password-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #2e856e; }
        .auth button { padding: 12px 30px; background-color: #f4c430; color: #1a3c34; border: none; border-radius: 25px; cursor: pointer; }
        .error, .success { color: #fff; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .error { background: #e74c3c; }
        .success { background: #2ecc71; }
        .switch { margin-top: 20px; }
        .switch a { color: #2e856e; text-decoration: none; }
        footer { background: linear-gradient(90deg, #1a3c34 0%, #2e856e 100%); color: #fff; padding: 30px; text-align: center; flex-shrink: 0; width: 100%; }
        footer a { color: #f4c430; text-decoration: none; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="auth">
        <?php include 'forgot.php'; ?>
        <div class="switch">
            <p><a href="login-register.php">Back to Login</a></p>
        </div>
    </section>
    <?php require_once 'footer.php'; ?>
    
    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = passwordField.nextElementSibling;
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.textContent = '👁️‍🗨️';
            } else {
                passwordField.type = 'password';
                toggleIcon.textContent = '👁️';
            }
        }
    </script>
</body>
</html>