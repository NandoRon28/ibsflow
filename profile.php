<?php
require_once 'config/config.php';
require_once 'psnetbot.php';
require_once 'header.php';

$isLoggedIn = isset($_SESSION['user_id']);
if (!$isLoggedIn) {
    header("Location: login-register.php");
    exit();
}

$pengguna_id = $_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? '';

// Fetch user data with a unique variable name to avoid conflict with header.php
$stmt = $conn->prepare("SELECT nama, email, profile_picture FROM pengguna WHERE id = ?");
$stmt->execute([$pengguna_id]);
$profileUserData = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user data was retrieved
if (!$profileUserData) {
    error_log("User data not found for ID: $pengguna_id");
    header("Location: logout.php?error=User not found");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';

    // Process profile picture upload
    $profile_picture = $profileUserData['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        if (in_array($file_ext, $allowed_ext)) {
            $new_profile_picture = uniqid() . '.' . $file_ext;
            $upload_path = 'img/' . $new_profile_picture;
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Delete old photo if it exists
                if ($profile_picture && file_exists('img/' . $profile_picture)) {
                    unlink('img/' . $profile_picture);
                }
                $profile_picture = $new_profile_picture;
            }
        }
    }

    try {
        $stmt = $conn->prepare("UPDATE pengguna SET nama = ?, email = ?, profile_picture = ? WHERE id = ?");
        $stmt->execute([$nama, $email, $profile_picture, $pengguna_id]);
        header("Location: profile.php?success=Profil berhasil diperbarui.");
        exit();
    } catch (PDOException $e) {
        die("Gagal memperbarui profil: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - PSNet</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <style>
    :root {
        --primary: #1E3A8A;
        --secondary: #3B82F6;
        --accent: #F9FAFB;
    }

    * { 
        margin: 0; 
        padding: 0; 
        box-sizing: border-box; 
        font-family: 'Poppins', sans-serif; 
    }
    body { 
        background: linear-gradient(135deg, #E0E7FF 0%, #F3F4F6 100%); 
        color: #1F2937; 
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
        background: url('https://www.transparenttextures.com/patterns/light-paper-fibers.png');
        opacity: 0.05;
        z-index: -1;
    }

    /* Header */
    header { 
        background: linear-gradient(90deg, var(--primary), darken(var(--primary), 10%)); 
        padding: 15px 30px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
        position: sticky; 
        top: 0; 
        z-index: 100; 
        animation: slideDown 0.5s ease-out; 
    }
    @keyframes slideDown {
        from { transform: translateY(-100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    header .logo { 
        font-size: 26px; 
        font-weight: 600; 
        color: var(--accent); 
        text-transform: uppercase; 
        letter-spacing: 1px; 
    }
    header nav ul { 
        list-style: none; 
        display: flex; 
        gap: 20px; 
    }
    header nav ul li a { 
        color: var(--accent); 
        text-decoration: none; 
        font-size: 15px; 
        padding: 6px 12px; 
        border-radius: 4px; 
        transition: color 0.3s ease; 
    }
    header nav ul li a:hover { 
        color: var(--secondary); 
    }

    /* CTA Button & Profile Icon */
    .cta-btn, .profile-icon { 
        background: var(--secondary); 
        padding: 8px 18px; 
        border-radius: 20px; 
        color: var(--accent); 
        font-weight: 500; 
        text-decoration: none; 
        transition: all 0.3s ease; 
    }
    .cta-btn:hover, .profile-icon:hover { 
        transform: scale(1.05); 
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); 
    }
    .profile-icon { 
        width: 40px; 
        height: 40px; 
        border-radius: 50%; 
        object-fit: cover; 
        margin-left: 10px; 
        border: 2px solid var(--accent); 
    }
    .header-right { 
        display: flex; 
        align-items: center; 
        gap: 10px; 
    }

    /* Profile Section */
    .profile-section { 
        padding: 50px 20px; 
        max-width: 750px; 
        margin: 0 auto; 
        background: rgba(255, 255, 255, 0.9); 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); 
    }
    .profile-container { 
        padding: 25px; 
        border-radius: 10px; 
        background: var(--accent); 
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); 
        animation: slideIn 0.7s ease-out; 
    }
    @keyframes slideIn {
        from { transform: translateX(-30px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .profile-header { 
        text-align: center; 
        margin-bottom: 25px; 
        animation: fadeIn 0.7s ease-out; 
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .profile-picture { 
        width: 120px; 
        height: 120px; 
        border-radius: 50%; 
        object-fit: cover; 
        border: 3px solid rgba(var(--secondary), 0.5); 
        margin-bottom: 10px; 
        transition: transform 0.3s ease; 
    }
    .profile-picture:hover { 
        transform: scale(1.05); 
    }
    h1 { 
        font-size: 32px; 
        color: var(--primary); 
        margin-bottom: 20px; 
        text-align: center; 
        font-weight: 600; 
        position: relative; 
    }
    h1::after { 
        content: ''; 
        width: 50px; 
        height: 3px; 
        background: var(--secondary); 
        position: absolute; 
        bottom: -6px; 
        left: 50%; 
        transform: translateX(-50%); 
    }
    h2 { 
        font-size: 20px; 
        color: var(--primary); 
        margin-bottom: 15px; 
        font-weight: 500; 
    }
    .profile-info { 
        margin-bottom: 20px; 
    }
    .profile-info p { 
        font-size: 14px; 
        margin-bottom: 8px; 
        color: #4B5563; 
    }
    .profile-info span { 
        font-weight: 500; 
        color: var(--primary); 
    }
    form { 
        padding: 15px; 
        border-radius: 10px; 
        background: var(--accent); 
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); 
        margin-top: 20px; 
        animation: slideIn 0.7s ease-out; 
    }
    form h3 { 
        font-size: 18px; 
        color: var(--primary); 
        margin-bottom: 12px; 
        font-weight: 500; 
        padding-left: 20px; 
        position: relative; 
    }
    form h3::before { 
        content: '\f044'; 
        font-family: 'Font Awesome 5 Free'; 
        font-weight: 900; 
        color: var(--secondary); 
        position: absolute; 
        left: 0; 
        top: 50%; 
        transform: translateY(-50%); 
    }
    form input, form textarea { 
        width: 100%; 
        padding: 10px; 
        margin-bottom: 12px; 
        border: 1px solid #D1D5DB; 
        border-radius: 6px; 
        background: #FFFFFF; 
        transition: border-color 0.3s ease, box-shadow 0.3s ease; 
    }
    form input[type="file"] { 
        padding: 5px; 
    }
    form input:focus, form textarea:focus { 
        border-color: var(--secondary); 
        box-shadow: 0 0 8px rgba(var(--secondary), 0.2); 
        outline: none; 
    }
    .submit-btn { 
        padding: 8px 18px; 
        background: var(--secondary); 
        border: none; 
        border-radius: 20px; 
        cursor: pointer; 
        color: var(--accent); 
        font-weight: 500; 
        transition: all 0.3s ease; 
    }
    .submit-btn:hover { 
        background: var(--primary); 
        transform: scale(1.05); 
    }
    .success { 
        color: var(--accent); 
        padding: 10px; 
        margin-bottom: 20px; 
        border-radius: 8px; 
        background: var(--primary); 
        text-align: center; 
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); 
        animation: slideIn 0.7s ease-out; 
        max-width: 500px; 
        margin-left: auto; 
        margin-right: auto; 
    }

    /* Footer */
    footer { 
        background: linear-gradient(90deg, var(--primary), darken(var(--primary), 10%)); 
        color: var(--accent); 
        padding: 20px; 
        text-align: center; 
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1); 
    }
    footer a { 
        color: var(--secondary); 
        text-decoration: none; 
        font-weight: 500; 
        transition: color 0.3s ease; 
    }
    footer a:hover { 
        color: var(--accent); 
    }

    /* Responsivitas */
    @media (max-width: 768px) {
        header { flex-direction: column; padding: 10px 15px; }
        header nav ul { margin-top: 15px; flex-wrap: wrap; justify-content: center; gap: 12px; }
        .header-right { flex-direction: column; gap: 10px; }
        .profile-picture { width: 100px; height: 100px; }
        .profile-section { padding: 30px 15px; }
        .profile-container { padding: 15px; }
    }
    @media (max-width: 480px) {
        .profile-section { max-width: 100%; }
        .profile-container form input, .profile-container form textarea { font-size: 13px; }
    }
</style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    
    <section class="profile-section">
        <h1>Profil Saya</h1>
        <div class="profile-container">
            <div class="profile-header">
                <img src="<?php echo $profileUserData['profile_picture'] ? 'img/default.jpeg' . htmlspecialchars($profileUserData['profile_picture']) : 'img/default.jpeg'; ?>" alt="Profile Picture" class="profile-picture">
                <h2><?php echo htmlspecialchars($profileUserData['nama']); ?></h2>
            </div>
            <div class="profile-info">
                <p><span>Nama:</span> <?php echo htmlspecialchars($profileUserData['nama']); ?></p>
                <p><span>Email:</span> <?php echo htmlspecialchars($profileUserData['email']); ?></p>
                <p><span>Role:</span> <?php echo htmlspecialchars($userRole); ?></p>
                <p><span>Tanggal Bergabung:</span> <?php echo date('d M Y', strtotime('2025-01-01')); // Placeholder, sesuaikan dengan data asli ?></p>
            </div>
            <?php if (isset($_GET['success'])): ?>
                <div class="success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>
            <form method="POST" action="profile.php" enctype="multipart/form-data">
                <h3>Edit Profil</h3>
                <input type="text" name="nama" value="<?php echo htmlspecialchars($profileUserData['nama']); ?>" placeholder="Nama" required>
                <input type="email" name="email" value="<?php echo htmlspecialchars($profileUserData['email']); ?>" placeholder="Email" required>
                <input type="file" name="profile_picture" accept="image/*">
                <button type="submit" class="submit-btn">Simpan Perubahan</button>
            </form>
        </div>
    </section>

    <?php require_once 'footer.php'; ?>
    
</body>
</html>