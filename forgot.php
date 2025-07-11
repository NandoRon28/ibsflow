<?php
require_once 'config/config.php';
require_once 'psnetbot.php';

// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tentukan bahasa default
$defaultLang = 'id';
$selectedLang = $_GET['lang'] ?? $_SESSION['lang'] ?? $defaultLang;
$validLangs = ['id', 'en', 'ar', 'ms'];
if (!in_array($selectedLang, $validLangs)) {
    $selectedLang = $defaultLang;
}
$_SESSION['lang'] = $selectedLang;

// Load file bahasa
$langFile = __DIR__ . "/lang/lang_$selectedLang.php";
if (file_exists($langFile)) {
    require_once $langFile;
    error_log("File bahasa dimuat: $langFile");
} else {
    $langFileFallback = __DIR__ . "/lang/lang_id.php";
    if (file_exists($langFileFallback)) {
        require_once $langFileFallback;
        error_log("Fallback file bahasa dimuat: $langFileFallback");
    } else {
        error_log("Fallback file bahasa tidak ditemukan: $langFileFallback");
    }
}

// Pastikan $lang terdefinisi
if (!isset($lang) || !is_array($lang)) {
    $lang = [];
    error_log("Variabel \$lang tidak terdefinisi atau bukan array. Menggunakan array kosong sebagai fallback.");
}

// Kunci terjemahan default
$requiredKeys = [
    'email_empty_error', 'email_not_found', 'process_failed', 'session_expired',
    'wrong_answer', 'password_mismatch', 'password_reset_success', 'security_question_not_found',
    'name_empty_error', 'name_not_found', 'login_register', 'submit', 'name_placeholder',
    'email_placeholder', 'security_answer_placeholder', 'new_password_placeholder', 'confirm_password_placeholder'
];
foreach ($requiredKeys as $key) {
    if (!isset($lang[$key])) {
        $lang[$key] = "[$key]";
        error_log("Kunci terjemahan tidak ditemukan: $key");
    }
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
$step = $_GET['step'] ?? 'email';
$reset_email = $_SESSION['reset_email'] ?? '';
$reset_name = $_SESSION['reset_name'] ?? '';
$security_question = '';

if ($step === 'email' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    error_log("Received email: '$email', name: '$name'");
    
    if (empty($email)) {
        header("Location: forgot.php?step=email&error=" . urlencode($lang['email_empty_error']));
        exit();
    }
    if (empty($name)) {
        header("Location: forgot.php?step=email&error=" . urlencode($lang['name_empty_error']));
        exit();
    }

    $email = strtolower($email);
    try {
        $stmt = $conn->prepare("SELECT * FROM pengguna WHERE LOWER(email) = ? AND nama = ?");
        $stmt->execute([$email, $name]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            error_log("Email atau nama tidak ditemukan: email=$email, name=$name");
            header("Location: forgot.php?step=email&error=" . urlencode($lang['email_not_found']));
        } else {
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_name'] = $name;
            error_log("Session reset_email diset ke: $email, reset_name diset ke: $name");
            header("Location: forgot.php?step=answer_question");
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header("Location: forgot.php?step=email&error=" . urlencode($lang['process_failed'] . ": {$e->getMessage()}"));
    }
    exit();
}

if ($step === 'answer_question' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($reset_email) || empty($reset_name)) {
        error_log("Session reset_email atau reset_name hilang di langkah answer_question");
        header("Location: forgot.php?step=email&error=" . urlencode($lang['session_expired']));
        exit();
    }

    $answer = trim($_POST['answer'] ?? '');
    error_log("Jawaban yang diterima: '$answer'");
    try {
        $stmt = $conn->prepare("SELECT * FROM pengguna WHERE email = ? AND nama = ?");
        $stmt->execute([$reset_email, $reset_name]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $stored_answer = trim($user['security_answer']);
            error_log("Jawaban tersimpan: '$stored_answer'");
            if (strtolower($stored_answer) === strtolower($answer)) {
                error_log("Jawaban cocok, redirect ke reset_password");
                header("Location: forgot.php?step=reset_password");
            } else {
                error_log("Jawaban tidak cocok");
                header("Location: forgot.php?step=answer_question&error=" . urlencode($lang['wrong_answer']));
            }
        } else {
            error_log("Pengguna tidak ditemukan untuk email: $reset_email, name: $reset_name");
            header("Location: forgot.php?step=email&error=" . urlencode($lang['email_not_found']));
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header("Location: forgot.php?step=answer_question&error=" . urlencode($lang['process_failed'] . ": {$e->getMessage()}"));
    }
    exit();
}

if ($step === 'reset_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($reset_email) || empty($reset_name)) {
        error_log("Session reset_email atau reset_name hilang di langkah reset_password");
        header("Location: forgot.php?step=email&error=" . urlencode($lang['session_expired']));
        exit();
    }

    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if ($new_password !== $confirm_password) {
        header("Location: forgot.php?step=reset_password&error=" . urlencode($lang['password_mismatch']));
        exit();
    }
    try {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE pengguna SET password = ? WHERE email = ? AND nama = ?");
        $stmt->execute([$hashed_password, $reset_email, $reset_name]);
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_name']);
        header("Location: forgot.php?success=" . urlencode($lang['password_reset_success']));
    } catch (PDOException $e) {
        header("Location: forgot.php?step=reset_password&error=" . urlencode($lang['process_failed'] . ": {$e->getMessage()}"));
    }
    exit();
}

if ($step === 'answer_question' && $reset_email && $reset_name) {
    try {
        $stmt = $conn->prepare("SELECT security_question FROM pengguna WHERE email = ? AND nama = ?");
        $stmt->execute([$reset_email, $reset_name]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $security_question = $user['security_question'] ?? '';
        if (empty($security_question)) {
            header("Location: forgot.php?step=email&error=" . urlencode($lang['security_question_not_found']));
            exit();
        }
    } catch (PDOException $e) {
        header("Location: forgot.php?step=email&error=" . urlencode($lang['process_failed'] . ": {$e->getMessage()}"));
    }
} elseif ($step === 'answer_question' && (!$reset_email || !$reset_name)) {
    error_log("Session reset_email atau reset_name tidak ada saat masuk ke langkah answer_question");
    header("Location: forgot.php?step=email&error=" . urlencode($lang['session_expired']));
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
    <style>
        html,
body {
    padding: 0;
    margin: 0;
    font-family: sans-serif;
    background-color: #FAF9F6;
}

.container {
    position: fixed;
    top: 38%;
    left: 50%;
    transform: translate(-50%, -50%);
}

svg,
.form-container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.form-container {
    width: 270px;
}

svg {
    width: 1000px;
    pointer-events: none;
}

.form-container .form-row {
    width: 100%;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
}

.form-container .form-row input {
    width: 100%;
    height: 30px;
    margin: 0;
    padding: 5px;
    outline: none;
    border: 2px solid #dddddd;
}

.form-container .form-row input.valid {
    border-color: #000000;
}

.form-container .form-row input[type="checkbox"] {
    width: 20px;
    height: 20px;
    margin: 0 10px 0 0;
    padding: 0;
    border: 2px solid #dddddd;
}

.form-container .form-row input[type="submit"] {
    height: 40px;
    cursor: pointer;
    border: none;
    background-color: #eeeeee;
    /*pointer-events: none;*/
}

/*.form-container .form-row input[type="submit"].valid {*/
/*    pointer-events: auto;*/
/*}*/

.form-container input[type="submit"]:hover {
    background-color: #dddddd;
}

.form-container label,
.form-container input,
.form-container input::placeholder {
    font-size: 15px;
    font-family: inherit;
}

svg {
    stroke-width: 1.2px;
    /*stroke: #222245;*/
    stroke: #000000;
    fill: none
}
.error, .success { 
    color: #fff; 
    padding: 10px; 
    margin-bottom: 20px; 
    border-radius: 5px; 
    text-align: center; 
}
.error { 
    background: #e74c3c; 
}
.success { 
    background: #2ecc71; 
}
.password-container { 
    position: relative; 
}
.password-container input { 
    padding-right: 40px; 
}
.password-toggle { 
    position: absolute; 
    right: 10px; 
    top: 50%; 
    transform: translateY(-50%); 
    cursor: pointer; 
    color: #2e856e; 
}
.form-container h1 { 
    font-size: 24px; 
    color: #1a3c34; 
    margin-bottom: 20px; 
    text-align: center; 
}
.switch { 
    margin-top: 20px; 
    text-align: center; 
}
.switch a { 
    color: #2e856e; 
    text-decoration: none; 
}
.switch a:hover { 
    text-decoration: underline; 
}
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($step === 'email'): ?>
                <h1>Forgot Password</h1>
                <form method="POST" action="forgot.php?step=email">
                    <label class="form-row">
                        <input type="text" id="name" name="name" placeholder="Enter Your Name" required>
                    </label>
                    <label class="form-row">
                        <input type="email" id="email" name="email" placeholder="Enter Your Email" required>
                    </label>
                    <label class="form-row">
                        <input type="checkbox" id="subscribe" name="subscribe" required> Agree to terms
                    </label>
                    <div class="form-row">
                        <input type="submit" value="Submit">
                    </div>
                </form>

            <?php elseif ($step === 'answer_question'): ?>
                <h1>Answer Security Question</h1>
                <form method="POST" action="forgot.php?step=answer_question">
                    <label class="form-row">
                        <span><?php echo htmlspecialchars($security_question); ?></span>
                        <input type="text" id="answer" name="answer" placeholder="Your Answer" required>
                    </label>
                    <div class="form-row">
                        <input type="submit" value="Submit">
                    </div>
                </form>

            <?php elseif ($step === 'reset_password'): ?>
                <h1>Reset Password</h1>
                <form method="POST" action="forgot.php?step=reset_password">
                    <label class="form-row password-container">
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                        <span class="password-toggle" onclick="togglePassword('new_password')">👁️</span>
                    </label>
                    <label class="form-row password-container">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Are you sure for using this password ?Okay don't forget to remember it" required>
                        <span class="password-toggle" onclick="togglePassword('confirm_password')">👁️</span>
                    </label>
                    <div class="form-row">
                        <input type="submit" value="Submit">
                    </div>
                </form>
            <?php endif; ?>
            <div class="switch">
                <p><a href="login-register.php">Login/Register</a></p>
            </div>
        </div>
    
        <svg viewBox="0 0 1000 1000" stroke-linecap="round" stroke-linejoin="round">
    
            <rect x="710" y="527" width="16" height="47" rx="10" ry="10"></rect>
    
            <g class="grabbing-hand">
                <path d="M48.89,54.39c-3.51.76-15.72,3-22.83-.68a14,14,0,0,0-6.41-1.52h0A3.79,3.79,0,0,1,17,51.09a3.7,3.7,0,0,1-1.1-2.64V27.75A3.75,3.75,0,0,1,19.63,24H24.1"/>
                <path class="grabbing-hand-finger-open" d="M57.05,29.76l24.82,0a4.07,4.07,0,0,0,4.11-4h0a4.07,4.07,0,0,0-4-4.11L48.69,21.3"/>
                <path class="grabbing-hand-finger-open" d="M59.34,37.74l28.81.61a4.06,4.06,0,0,0,4.14-4h0a4.06,4.06,0,0,0-4-4.15L57,29.64"/>
                <path class="grabbing-hand-finger-open" d="M57.13,45.9l26.94.78a4.07,4.07,0,0,0,4.15-4h0a4.07,4.07,0,0,0-4-4.14l-24.84-.8"/>
                <path class="grabbing-hand-finger-open" d="M48.89,54.39l27.82.36a4.06,4.06,0,0,0,4.2-3.93h0A4.06,4.06,0,0,0,77,46.62l-19.88-.78"/>
                <path class="grabbing-hand-finger-open" d="M40.78,28c5.75-5.85,12.66-22,10.5-25.88-2.25-4.09-6,.1-14.73,8.66C30.84,16.36,30.91,17.1,24.32,24"/>
            </g>
    
            <g class="pull-system">
                <line class="checkbox-pull-line" x1="0" y1="0" x2="0" y2="0"/>
                <g class="checkbox-pull-circle">
                    <circle cx="0" cy="0" r="10"/>
                    <circle cx="0" cy="0" r="4" fill="#000000"/>
                </g>
                <circle class="submit-btn-circle" cx="0" cy="0" r="3" stroke="none" fill="#000" />
                <path class="submit-btn-connector" d=""></path>
            </g>
    
            <g class="spray-hand-container">
                <g class="pushing-hand">
                    <circle cx="18" cy="0" r="5" fill="#000000"/>
                    <circle cx="18" cy="-70" r="5" fill="#000000"/>
                    <path d="M18,-70 v70" stroke-width="4"/>
                    <g>
                        <path d="M25.3,32.9V60.2a4.2,4.2,0,0,0,4.2,4.2h0a4.2,4.2,0,0,0,4.2-4.2V26.7"/>
                        <rect x="3.9" y="18.4" width="8.4" height="21.47" rx="3.7" transform="translate(10.2 -1) rotate(19.4)"/>
                        <path d="M20.9,24a3.4,3.4,0,0,0-1.7-1.1h0a4.2,4.2,0,0,0-5.4,2.5L9.1,38.8a4.3,4.3,0,0,0,2.6,5.4h0a4.3,4.3,0,0,0,5.4-2.6l1.8-5.1"/>
                        <path d="M18.4,37.9,17.3,43a4.2,4.2,0,0,0,3.4,4.9h0a4.3,4.3,0,0,0,4.5-2.3"/>
                        <path fill="white" d="M29,16.8c-6.4,5-15,13.2-12.8,17.8s6,.7,15.8-6.7c6.4-4.8,7.4-12.6.5-19.2V4.2A3.8,3.8,0,0,0,28.7.5H8A3.5,3.5,0,0,0,5.4,1.6,3.7,3.7,0,0,0,4.3,4.2V8.7"/>
                        <path d="M4.3,8.7c-5.8,6.4-3.6,20-2.2,24.8"/>
                    </g>
                </g>
                <g class="sprayer">
                    <g class="sprayer-head">
                        <defs>
                            <radialGradient id="grad1" cx="50%" cy="50%" r="50%" fx="100%" fy="50%">
                                <stop offset="0%" stop-color="#777777" stop-opacity="0"/>
                                <stop offset="100%" stop-color="#777777" stop-opacity="1"/>
                            </radialGradient>
                        </defs>
                        <rect x="82.39" y="19.85" width="13.06" height="16.79" rx="1.46"/>
                        <rect x="74.55" y="22.56" width="7.84" height="6.1" rx="1.13"/>
    
                        <line class="spray-line" stroke="#777777" stroke-dasharray="8 5" x1="22.4" y1="14.76" x2="74.27" y2="25.2" />
                        <line class="spray-line" stroke="#777777" stroke-dasharray="8 5" x1="21.51" y1="21.12" x2="74.27" y2="25.2" />
                        <line class="spray-line" stroke="#777777" stroke-dasharray="8 5" x1="21.44" y1="28.26" x2="74.27" y2="25.2" />
                        <line class="spray-line" stroke="#777777" stroke-dasharray="8 5" x1="22.37" y1="35.54" x2="74.27" y2="25.2" />
                        <line class="spray-line" stroke="#777777" stroke-dasharray="8 5" x1="24.21" y1="42.36" x2="74.27" y2="25.2" />
                        <line class="spray-line" stroke="#777777" stroke-dasharray="8 5" x1="24.31" y1="7.78" x2="74.27" y2="25.2" />
    
                        <circle fill="url(#grad1)" stroke="none" class="spray-bubble" cx="25.43" cy="12.97" r="12.47" />
                        <circle fill="url(#grad1)" stroke="none" class="spray-bubble" cx="15.6" cy="25.43" r="15.1" />
                        <circle fill="url(#grad1)" stroke="none" class="spray-bubble" cx="33.24" cy="37.13" r="9.21" />
                        <circle fill="url(#grad1)" stroke="none" class="spray-bubble" cx="35.92" cy="19.5" r="11.89" />
                        <circle fill="url(#grad1)" stroke="none" class="spray-bubble" cx="18.82" cy="34.45" r="11.89" />
                    </g>
                    <path d="M89,42h0a21.3,21.3,0,0,1,21.3,21.3v56.48a5.06,5.06,0,0,1-5.06,5.06H72.6a5.06,5.06,0,0,1-5.06-5.06V63.4A21.45,21.45,0,0,1,89,42Z" fill="#fff"/>
                    <rect x="78.3" y="36.64" width="21.24" height="6.15" rx="1.93" fill="#fff"/>
                    <rect x="76.33" y="71.46" width="33.96" height="23.23" fill="#cccccc"/>
                </g>
            </g>
    
            <g>
                <line class="gear-connector" x1="0" x2="0" y1="0" y2="0"/>
                <g class="gears">
                </g>
            </g>
    
            <g class="grabbing-hand">
                <g fill="#ffffff">
                    <rect class="grabbing-hand-finger-closed" x="44.79" y="13.38" width="8.42" height="22.15" rx="3.67" transform="translate(20.57 71.26) rotate(-85.25)"/>
                    <rect class="grabbing-hand-finger-closed" x="44.08" y="39.17" width="8.42" height="21.47" rx="3.67" transform="translate(-5.44 93.9) rotate(-85.25)"/>
                    <rect class="grabbing-hand-finger-closed" x="45.68" y="30.71" width="8.42" height="22.57" rx="3.67" transform="matrix(0.08, -1, 1, 0.08, 3.91, 88.24)"/>
                    <rect class="grabbing-hand-finger-closed" x="44.98" y="22.21" width="8.42" height="22.57" rx="3.67" transform="matrix(0.08, -1, 1, 0.08, 11.74, 79.74)"/>
                    <path class="grabbing-hand-finger-closed" d="M32.18,27.42c5,6.46,13.22,15.06,17.76,12.81,4.18-2.07.69-6-6.66-15.74C38.46,18.1,30.69,17.1,24.1,24"/>
                </g>
            </g>
    
            <g class="spiral-container">
                <path stroke-width=".8" class="spiral-path" d=""/>
            </g>
    
            <g class="weight-big-container">
                <line x1="14" x2="60" y1="14" y2="14"></line>
                <line x1="14" x2="60" y1="14" y2="55"></line>
                <circle cx="14" cy="14" r="5" fill="#000000" stroke="none"/>
    
                <g class="weight-big" stroke="none">
                    <path d="M25.5,16.7c.2-.6.5-1.3.7-2C31.1,3.1,23.2,0,14.3,0S-1.6,4.2,2.4,14.7a22.5,22.5,0,0,1,.8,2.4A14.4,14.4,0,0,0,0,26.2c0,8,6.5,11.6,14.5,11.6S29,34.2,29,26.2A14.6,14.6,0,0,0,25.5,16.7ZM14.4,5c5.6,0,9.3,1.9,7.1,8.5a13.5,13.5,0,0,0-7-1.8,14.6,14.6,0,0,0-7.2,1.9C5.5,7.5,8.8,5,14.4,5Z" fill="#231f20"/>
                    <path d="M15.1,15.6l-1.8-.2a9.2,9.2,0,0,0-9.1,9.2,6.2,6.2,0,0,0,.2,1.9A13.3,13.3,0,0,1,15.1,15.6Z" fill="#fff"/>
                </g>
            </g>
    
    
            <g class="scales-container">
    
                <defs>
                    <marker
                            id="ball"
                            viewBox="0 0 10 10"
                            refX="5"
                            refY="5"
                            markerUnits="strokeWidth"
                            markerWidth="5"
                            markerHeight="5"
                            orient="auto">
                        <circle cx="5" cy="5" r="3" fill="#000"/>
                    </marker>
                </defs>
    
    
                <rect x="10" y="-19" width="30" height="90" rx="15" ry="15" stroke-width="10" stroke="#ccc" />
                <rect class="timing-chain" x="10" y="-19" width="30" height="90" rx="15" ry="15" stroke="#fff" />
    
                <rect x="-31" y="-19" width="30" height="144" rx="15" ry="15" stroke-width="10" stroke="#ccc"/>
                <rect class="timing-chain" x="-31" y="-19" width="30" height="144" rx="15" ry="15" stroke="#fff"/>
    
                <g class="reels-connector">
                    <rect x="-8" y="3.2" width="25" height="10" rx="5" ry="5" />
                    <circle cx="-1" cy="8.5" r="3" fill="#000" stroke="none"/>
                    <circle cx="9.9" cy="8.5" r="3" fill="#000" stroke="none"/>
                </g>
    
                <g class="car-weight-connector">
                    <rect x="-36" y="97" width="10" height="95" rx="5" ry="5" />
                    <circle cx="-31" cy="103" r="3" fill="#000" stroke="none"/>
                    <circle cx="-31" cy="186" r="3" fill="#000" stroke="none"/>
                </g>
    
    
                <line class="scales-moving-line" x1="147.6" y1="30.52" x2="40" y2="12" stroke-width="2" marker-start="url(#ball)" marker-end="url(#ball)"/>
                <path fill="#000000" d="M102.45,30.68,92,20.26c-9.89,9.9-9.89,10.47-9.89,10.47Z" />
    
            </g>
    
            <g class="car-container">
                <g>
                    <g class="car">
                        <circle cx="17" cy="88" r="5" />
                        <circle cx="17" cy="88" r="2" fill="#000" />
                        <circle cx="32" cy="88" r="5" />
                        <circle cx="32" cy="88" r="2" fill="#000" />
    
                        <path d="M10,65 h30 l-5,15 h-20 l-5,-15 " fill="#000" />
                    </g>
    
                    <line x1="-51" y1="95" x2="145" y2="95"/>
                </g>
            </g>
    
        </svg>
    </div>
    <script src="https://unpkg.com/gsap@3/dist/gsap.min.js"></script>
    <script>
const containerEl = document.querySelector('.container');
    const checkboxEl = document.querySelector('.form-container .form-row input[type="checkbox"]');
    const nameEl = document.querySelector('.form-container .form-row input[name="name"]');
    const emailEl = document.querySelector('.form-container .form-row input[name="email"]');
    const answerEl = document.querySelector('.form-container .form-row input[name="answer"]');
    const newPasswordEl = document.querySelector('.form-container .form-row input[name="new_password"]');
    const confirmPasswordEl = document.querySelector('.form-container .form-row input[name="confirm_password"]');
    const submitBtn = document.querySelector('.form-container .form-row input[type="submit"]');

    const sprayer = document.querySelector('.sprayer');
    const sprayHandContainer = document.querySelector('.spray-hand-container');
    const sprayLines = Array.from(document.querySelectorAll('.spray-line'));
    const sprayBubbles = Array.from(document.querySelectorAll('.spray-bubble'));

    const pushingHand = document.querySelector('.pushing-hand');
    const sprayerHead = document.querySelector('.sprayer-head');
    const gearsContainer = document.querySelector('svg .gears');
    const gearConnector = document.querySelector('.gear-connector');

    const pullSystemContainer = document.querySelector('.pull-system');
    const checkboxPullLine = document.querySelector('.checkbox-pull-line');
    const checkboxPullCircle = document.querySelector('.checkbox-pull-circle');
    const btnPullLine = document.querySelector('.submit-btn-connector');
    const btnHandlerCircle = document.querySelector('.submit-btn-circle');

    const spiralContainer = document.querySelector('.spiral-container');
    const weightBigContainer = document.querySelector('.weight-big-container');
    const scalesContainer = document.querySelector('.scales-container');
    const scalesLine = document.querySelector('.scales-moving-line');
    const weightBig = document.querySelector('.weight-big');
    const spiralPath = document.querySelector('.spiral-path');
    const carContainer = document.querySelector('.car-container');
    const car = document.querySelector('.car');
    const carInclineWrapper = document.querySelector('.car-container g');
    const timingChains = Array.from(document.querySelectorAll('.timing-chain'));
    const reelsConnector = document.querySelector('.reels-connector');
    const carWeightConnector = document.querySelector('.car-weight-connector');
    const grabbingHand = document.querySelectorAll('.grabbing-hand');
    const grabbingHandOpenFingers = Array.from(document.querySelectorAll('.grabbing-hand-finger-open'));
    const grabbingHandClosedFingers = Array.from(document.querySelectorAll('.grabbing-hand-finger-closed'));

    layoutPreparation();
    scaleToFit();
    window.onresize = scaleToFit;

    function scaleToFit() {
        const h = 800;
        if (window.innerHeight < h) {
            gsap.set(containerEl, {
                scale: window.innerHeight / h,
                transformOrigin: "50% 75%"
            });
        }
    }

    let sprayRepeatCounter = 0;
    const state = {
        handClosed: false,
        sumbitBtnOnPlace: false,
        sumbitBtnTextOpacity: 0,
        pullProgress: 0
    };
    let nameValid = false;
    let emailValid = false;
    let answerValid = false;
    let passwordValid = false;

    const emailTl = createEmailTl();
    const gearsTls = createGearsTimelines();
    createPullingTimeline(state.handClosed, checkboxEl?.checked || false);

    if (checkboxEl) {
        checkboxEl.addEventListener('change', () => {
            createPullingTimeline(state.handClosed, checkboxEl.checked);
        });
    }

    if (nameEl) {
        nameEl.addEventListener('input', () => {
            nameValid = nameEl.value.length > 3;
            if (nameValid) {
                nameEl.classList.add("valid");
                gearsTls.forEach(tl => {
                    if (tl.paused()) {
                        tl.play();
                        gsap.fromTo(tl, { timeScale: 0 }, { timeScale: 1 });
                    }
                });
            } else {
                nameEl.classList.remove("valid");
                gearsTls.forEach(tl => {
                    if (!tl.paused()) {
                        gsap.to(tl, {
                            timeScale: 0,
                            onComplete: () => tl.pause()
                        });
                    }
                });
                sprayRepeatCounter = 0;
                gsap.to(submitBtn, { duration: .3, color: "rgba(0, 0, 0, 0)" });
            }
        });
    }

    if (emailEl) {
        emailEl.addEventListener('input', () => {
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            emailValid = emailRegex.test(emailEl.value);
            if (emailValid) {
                emailTl.play();
                emailEl.classList.add("valid");
            } else {
                emailTl.reverse();
                emailEl.classList.remove("valid");
            }
        });
    }

    if (answerEl) {
        answerEl.addEventListener('input', () => {
            answerValid = answerEl.value.length > 0;
            if (answerValid) {
                answerEl.classList.add("valid");
                gearsTls.forEach(tl => {
                    if (tl.paused()) {
                        tl.play();
                        gsap.fromTo(tl, { timeScale: 0 }, { timeScale: 1 });
                    }
                });
            } else {
                answerEl.classList.remove("valid");
                gearsTls.forEach(tl => {
                    if (!tl.paused()) {
                        gsap.to(tl, {
                            timeScale: 0,
                            onComplete: () => tl.pause()
                        });
                    }
                });
            }
        });
    }

    if (newPasswordEl && confirmPasswordEl) {
        const validatePassword = () => {
            passwordValid = newPasswordEl.value.length >= 6 && newPasswordEl.value === confirmPasswordEl.value;
            if (passwordValid) {
                newPasswordEl.classList.add("valid");
                confirmPasswordEl.classList.add("valid");
                gearsTls.forEach(tl => {
                    if (tl.paused()) {
                        tl.play();
                        gsap.fromTo(tl, { timeScale: 0 }, { timeScale: 1 });
                    }
                });
            } else {
                newPasswordEl.classList.remove("valid");
                confirmPasswordEl.classList.remove("valid");
                gearsTls.forEach(tl => {
                    if (!tl.paused()) {
                        gsap.to(tl, {
                            timeScale: 0,
                            onComplete: () => tl.pause()
                        });
                    }
                });
            }
        };
        newPasswordEl.addEventListener('input', validatePassword);
        confirmPasswordEl.addEventListener('input', validatePassword);
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', (e) => {
            let isValid = false;
            if (nameEl && emailEl && checkboxEl) {
                isValid = nameValid && emailValid && checkboxEl.checked && sprayRepeatCounter > 1;
            } else if (answerEl) {
                isValid = answerValid;
            } else if (newPasswordEl && confirmPasswordEl) {
                isValid = passwordValid;
            }
            if (isValid) {
                gsap.to("svg > *", {
                    duration: .1,
                    opacity: 0,
                    stagger: { each: 0.03, from: 'random', ease: 'none' }
                });
                gsap.to(".form-row", {
                    delay: .4,
                    duration: .1,
                    opacity: 0,
                    stagger: .1
                });
            } else {
                e.preventDefault();
                alert("Please fill all fields correctly.");
            }
        });
    }

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

function layoutPreparation() {
    gsap.set(pullSystemContainer, {
        x: 375,
        y: 646
    })
    gsap.set(sprayHandContainer, {
        x: 700,
        y: 621
    })
    gsap.set(sprayer, {
        x: -59.5,
        y: 53
    })
    gsap.set(carContainer, {
        x: 190,
        y: 802,
    })
    gsap.set(scalesContainer, {
        x: 170,
        y: 710,
    })
    gsap.set(grabbingHand, {
        x: 297,
        y: 830
    })
    gsap.set(grabbingHandClosedFingers, {
        opacity: 0
    })
    gsap.set(spiralContainer, {
        x: 305,
        y: 435,
        svgOrigin: "14 14",
        scaleX: -1,
    })
    gsap.set(weightBigContainer, {
        x: 305,
        y: 435,
    })
    gsap.set(submitBtn, {
        color: "rgba(0, 0, 0, " + 0 + ")"
    })
    gsap.set([sprayLines, sprayBubbles], {
        opacity: 0
    })
    gsap.set(timingChains[0], {
        attr: {
            "stroke-width": "5",
            "stroke-dasharray": "0 12",
        }
    })
    gsap.set(timingChains[1], {
        attr: {
            "stroke-width": "5",
            "stroke-dasharray": "0 12",
        }
    })
    gsap.set(checkboxPullLine, {
        attr: {
            y1: -105,
            y2: 44
        }
    });
    gsap.set(submitBtn, {
        transformOrigin: "100% 0%",
        rotation: -90
    })
    gsap.set(checkboxPullCircle, {
        y: 44
    });
}

function updateSpiralPath(centerX, centerY, radius, coils, points, offset) {
    let path = "";
    let thetaMax = coils * 2 * Math.PI;
    const awayStep = radius / thetaMax;
    const chord = 2 * Math.PI / points;
    thetaMax -= offset * points * chord;

    for (let theta = 0; theta <= thetaMax; theta += chord) {
        const away = awayStep * theta;
        const x = centerX + Math.cos(theta) * away;
        const y = centerY + Math.sin(theta) * away;

        if (theta === 0) {
            path += `M${x},${y}`;
        } else {
            const prevAway = awayStep * (theta - chord);
            const arcRadius = (away + prevAway) / 2;
            path += ` A${arcRadius},${arcRadius} 0 0,1 ${x},${y}`;
        }
    }

    const outerAngle = thetaMax + .5 * Math.PI;
    const outerLength = 50 + 25 * offset
    const endPoint = [
        Math.cos(outerAngle) * outerLength,
        Math.sin(outerAngle) * outerLength,
    ]
    path += (' l' + endPoint[0] + ',' + endPoint[1]);

    gsap.set(spiralPath, {
        attr: {
            d: path
        },
    })
    gsap.set(weightBig, {
        x: -47 + 3 * offset,
        y: 12 + outerLength
    })
}

function createEmailTl() {
    const spiralTurnsNumber = 8;
    const spiralProgress = {v: 0}
    const hammerTimeStart = 1.85;
    const fingersDelay = .5;
    const fingersTimeDelta = .03;
    const tl = gsap.timeline({
        paused: true,
        defaults: {
            ease: "none",
            duration: 2
        },
        onUpdate: () => {
            updateSpiralPath(14, 14, 45, 17, 200, spiralTurnsNumber * spiralProgress.v);
        },
    })
        .to(spiralProgress, {
            v: 1
        }, 0)
        .to(spiralContainer, {
            rotation: -spiralTurnsNumber * 360,
        }, 0)

        .fromTo(scalesLine, {
            rotation: -20,
            svgOrigin: "92 20"
        }, {
            duration: .15,
            rotation: -1,
            svgOrigin: "92 20"
        }, hammerTimeStart)

        .fromTo(timingChains[0], {
            attr: {
                "stroke-dashoffset": 2
            }
        }, {
            duration: .15,
            attr: {
                "stroke-dashoffset": 20
            }
        }, hammerTimeStart)
        .fromTo(timingChains[1], {
            attr: {
                "stroke-dashoffset": 24
            }
        }, {
            duration: .15,
            attr: {
                "stroke-dashoffset": 6
            }
        }, hammerTimeStart)
        .to(reelsConnector, {
            duration: .15,
            y: 18
        }, hammerTimeStart)
        .to(carWeightConnector, {
            duration: .15,
            y: -18
        }, hammerTimeStart)
        .to(carInclineWrapper, {
            duration: .15,
            rotation: 6,
            svgOrigin: "120 93"
        }, hammerTimeStart)
        .fromTo(car, {
            x: -50,
        }, {
            duration: .6,
            x: 95,
            ease: "power2.in",
        }, hammerTimeStart)
    for (let i = 0; i < 5; i++) {
        tl
            .set(grabbingHandOpenFingers[i], {
                opacity: 0
            }, hammerTimeStart + fingersDelay + fingersTimeDelta * (i + 1))
            .set(grabbingHandClosedFingers[i], {
                opacity: 1
            }, hammerTimeStart + fingersDelay + fingersTimeDelta * (i + 1))
    }
    tl
        .fromTo(state, {
            handClosed: false
        }, {
            duration: .01,
            handClosed: true
        }, ">")
        .to(grabbingHand, {
            duration: fingersTimeDelta * 5,
            x: "+=20"
        }, hammerTimeStart + fingersDelay)

    tl.progress(0.001);

    return tl;
}

function createGearsTimelines() {
    const tls = []

    const params = {
        baseSize: 15,
        pitch: 11,
        teethCurve: .6,
        startPos: {x: 634, y: 389},
        speed: .2
    }
    const data = [{
        angle: 0, teethNumber: 10, hasHole: true
    }, {
        angle: -.5, teethNumber: 32, hasHole: true
    }, {
        angle: 1.65, teethNumber: 12, hasHole: false
    }];

    const handleRadius = 14;

    const gears = [];
    data.forEach((d, dIdx) => {

        const radius = (d.teethNumber * params.baseSize) / (2 * Math.PI);
        let x, y, startAngle;

        if (dIdx === 0) {
            startAngle = 0;
            x = params.startPos.x;
            y = params.startPos.y;
        } else {
            const parent = gears[dIdx - 1];

            const size = parent.teethNumber / d.teethNumber;

            x = parent.center.x + Math.cos(d.angle) * (parent.radius + radius);
            y = parent.center.y + Math.sin(d.angle) * (parent.radius + radius);

            startAngle = (1 + size) * d.angle - size * parent.angle;
        }


        const group = document.createElementNS("http://www.w3.org/2000/svg", "g");
        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
        gearsContainer.appendChild(group);
        group.appendChild(path);

        const gear = {
            idx: dIdx,
            center: {x, y},
            radius,
            angle: startAngle,
            teethNumber: d.teethNumber,
            hasHole: d.hasHole,
            toothAngle: 2 * Math.PI / d.teethNumber,
            toothCurveAngle: params.teethCurve / d.teethNumber,
            group
        }


        const rOut = gear.radius + .25 * params.pitch;
        const rIn = rOut - .75 * params.pitch;
        let pathD = "M" + (gear.center.x + Math.cos(gear.angle - gear.toothAngle + gear.toothCurveAngle) * rOut) + ", " + (gear.center.y + Math.sin(gear.angle - gear.toothAngle + gear.toothCurveAngle) * rOut) + " ";
        for (let a = gear.angle; a < (gear.angle + 2 * Math.PI - .5 * gear.toothAngle); a += gear.toothAngle) {
            const pa = (a - .5 * gear.toothAngle);
            pathD += ("L" + (gear.center.x + Math.cos(pa - gear.toothCurveAngle) * rOut) + ", " + (gear.center.y + Math.sin(pa - gear.toothCurveAngle) * rOut) + " ");
            pathD += ("L" + (gear.center.x + Math.cos(pa) * rIn) + ", " + (gear.center.y + Math.sin(pa) * rIn) + " ");
            pathD += ("L" + (gear.center.x + Math.cos(a) * rIn) + ", " + (gear.center.y + Math.sin(a) * rIn) + " ");
            pathD += ("L" + (gear.center.x + Math.cos(a + gear.toothCurveAngle) * rOut) + ", " + (gear.center.y + Math.sin(a + gear.toothCurveAngle) * rOut) + " ");
        }

        if (gear.hasHole) {
            const holeRadius = .5 * rIn;
            pathD += ("M" + (gear.center.x - holeRadius) + ", " + (gear.center.y) + " ");
            pathD += `A ${holeRadius} ${holeRadius} 1 1 0 ${gear.center.x + holeRadius} ${gear.center.y}`;
            pathD += `A ${holeRadius} ${holeRadius} 1 1 0 ${gear.center.x - holeRadius} ${gear.center.y}`;
        }

        if (dIdx === 0) {
            const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
            gsap.set(circle, {
                attr: {
                    cx: gear.center.x,
                    cy: gear.center.y,
                    r: 5,
                    fill: "#000000"
                }
            })
            gearsContainer.appendChild(circle);
            gsap.set(path, {
                attr: {
                    fill: "#000000",
                    "fill-opacity": .25
                }
            })

        } else if (dIdx === (data.length - 1)) {
            gsap.set(path, {
                attr: {
                    fill: "#000000",
                    "fill-opacity": .25
                }
            })
            const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
            gsap.set(circle, {
                attr: {
                    cx: gear.center.x + handleRadius,
                    cy: gear.center.y,
                    r: 5,
                    fill: "#000000"
                }
            })
            gear.group.appendChild(circle);
        }

        path.setAttribute("d", pathD);


        const tl = gsap.timeline({
            repeat: -1,
            paused: true,
        })
            .to(group, {
                duration: params.speed * gear.teethNumber,
                rotation: 360 * (gear.idx % 2 ? -1 : 1),
                svgOrigin: gear.center.x + " " + gear.center.y,
                ease: "none",
            });

        if (dIdx === (data.length - 1)) {
            tl.eventCallback("onUpdate", () => {
                const angle = tl.progress() * 2 * Math.PI;
                const deltaY = Math.sin(angle) * handleRadius;
                gsap.set(pushingHand, {
                    y: deltaY,
                })
                if (deltaY > 8) {
                    const d = Math.max(0, deltaY - 8);
                    gsap.set(sprayerHead, {
                        y: d
                    })

                    let sprayProgress = Math.max(0, tl.progress() - .1);
                    sprayProgress *= (1 / .2);

                    let bubblesOpacity = (sprayProgress > 1) ? 0 : sprayProgress;
                    bubblesOpacity *= (1 - Math.pow(bubblesOpacity, 8));

                    const textProgress = Math.pow(sprayProgress / 1.5, 6);

                    if (!state.sumbitBtnOnPlace) {
                        state.sumbitBtnTextOpacity = (sprayRepeatCounter - 1) * .3 + .3 * textProgress;
                        state.sumbitBtnTextOpacity = Math.pow(state.sumbitBtnTextOpacity, 2);
                    }

                    gsap.set(submitBtn, {
                        color: "rgba(0, 0, 0, " + state.sumbitBtnTextOpacity + ")"
                    })
                    gsap.set(sprayLines, {
                        attr: {
                            "stroke-dashoffset": 70 * sprayProgress
                        },
                        opacity: Math.pow(bubblesOpacity, 2)
                    })
                    sprayBubbles.forEach((b, bIdx) => {
                        gsap.set(b, {
                            x: 25 * (1 - sprayProgress) * (1 + .1 * bIdx),
                            scale: .5 + 1.4 * Math.pow(sprayProgress, 2),
                            transformOrigin: "center center",
                            opacity: bubblesOpacity
                        })
                    })
                }

                gsap.set(gearConnector, {
                    attr: {
                        x1: gear.center.x + handleRadius * Math.cos(angle),
                        y1: gear.center.y + handleRadius * Math.sin(angle),
                        x2: 700 + 18,
                        y2: 646 - 100 + deltaY
                    }
                })
            });

            tl.eventCallback("onRepeat", () => {
                if (!state.sumbitBtnOnPlace) {
                    sprayRepeatCounter++;
                }
            });
        }

        tl.progress(0.6)
        tls.push(tl);
        gears.push(gear);
    })

    return tls;
}


function createPullingTimeline(isFixed, BtnPulled) {
    let tl = gsap.timeline({
        // paused: true,
        defaults: {
            ease: "power1.inOut",
            duration: 1
        },
        onUpdate: animatePullingLine
    });

    if (isFixed && BtnPulled) {
        tl
            .to(state, {
                pullProgress: 1
            }, 0)
            .to(submitBtn, {
                rotation: 0
            }, 0)
            .to(state, {
                duration: .1,
                sumbitBtnOnPlace: 1
            }, .9)
            .to(checkboxPullLine, {
                attr: {y2: 44 - 130}
            }, 0)
            .to(checkboxPullCircle, {
                y: 44 - 130
            }, 0)
    } else if (!isFixed && BtnPulled) {
        tl
            .to(state, {
                pullProgress: 1
            }, 0)
            .to(checkboxPullLine, {
                attr: {y2: 44 - 130}
            }, 0)
            .to(checkboxPullCircle, {
                y: 44 - 130
            }, 0)
    } else if (isFixed && !BtnPulled) {
        tl
            .to(state, {
                pullProgress: 0
            }, 0)
            .to(submitBtn, {
                rotation: -90
            }, 0)
            .to(state, {
                duration: .1,
                sumbitBtnOnPlace: 0
            }, 0)
            .to(checkboxPullLine, {
                attr: {y2: 44}
            }, 0)
            .to(checkboxPullCircle, {
                y: 44
            }, 0)
    } else if (!isFixed && !BtnPulled) {
        tl
            .to(state, {
                pullProgress: 0
            }, 0)
            .to(checkboxPullLine, {
                attr: {y2: 44}
            }, 0)
            .to(checkboxPullCircle, {
                y: 44
            }, 0)
    }


    function animatePullingLine() {
        const buttonOriginPoint = [260, -76];
        const btnWidth = 270;

        const deg = (gsap.getProperty(submitBtn, "rotation") - 4) * Math.PI / 180;

        const btnEnd = [
            buttonOriginPoint[0] - (btnWidth - 20) * Math.cos(deg),
            buttonOriginPoint[1] - (btnWidth - 20) * Math.sin(deg),
        ]
        gsap.set(btnHandlerCircle, {
            attr: {
                cx: btnEnd[0],
                cy: btnEnd[1]
            }
        })

        const handle = 7;
        const r = 10;

        let btnPullLinePath = "M" + (-r - handle) + "," + (250 - (isFixed ? 0 : state.pullProgress * 300));
        btnPullLinePath += "h" + (2 * handle);
        btnPullLinePath += "h" + (-handle);
        btnPullLinePath += " V" + (44 - state.pullProgress * 130);
        const slideAngle = .3 * Math.PI * (1 - (isFixed ? 1 : .5) * state.pullProgress);
        const dx = r * Math.cos(slideAngle);
        const dy = -r * Math.sin(slideAngle);
        btnPullLinePath += "a" + r + ', ' + r + " 0 0 1 " + (r + dx) + " " + dy;
        btnPullLinePath += " L" + btnEnd[0] + "," + btnEnd[1];

        gsap.set(btnPullLine, {
            attr: {
                d: btnPullLinePath
            },
            strokeWidth: 3
        })
    }

    return tl;
}
    </script>
</body>
</html>