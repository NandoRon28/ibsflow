<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Allura&family=Nunito:wght@200;400;500;600;800&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap');
    *{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body{
       font-family: 'Poppins', sans-serif;
        background-color: hsl(0, 0%, 0%);
        font-size: 16px;
    }
    .container{
        max-width: 1170px;
        padding: 0 15px;
        margin: auto;
    }
    .section{
        padding: 80px 0;
        min-height: 100vh;
        display: flex;
    }
    .section-cards{
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }
    .section-card{
        background-color: hsl(220, 6%, 10%);
        padding: 120px 30px 30px;
        position: relative;
        z-index: 1;
    }
    .section-card:nth-child(1){
        --color: #AA96DA;
    }
    .section-card:nth-child(2){
        --color: #C5FAD5;
    }
    .section-card:nth-child(3){
        --color: #FFBF69;
    }
    .section-card::before{
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 100%;
        background-color: var(--color);
        z-index: -1;
        clip-path: circle(40px at 70px 70px);
        transition: clip-path 1s ease;
    }
    .section-card:hover::before{
        clip-path: circle(100%);
    }
    .section-card span{
        position: absolute;
        left: 0;
        top: 0;
        height: 80px;
        width: 80px;
        font-size: 50px;
        font-weight: bold;
        transform: translate(30px, 30px);
        display: flex;
        align-items: center;
        justify-content: center;
        color: hsl(0, 0%, 0%);
        transition: transform 1s ease;
    }
    .section-card:hover span{
        transform: translate(0, 30px);
    }
    
    .section-card h2{
        font-size: 26px;
        color: hsl(0, 0%, 100%);
        font-weight: 600;
        text-transform: capitalize;
        margin-bottom: 10px;
        line-height: 1.3;
    }
    .section-card p{
        color: hsl(0, 0%, 85%);
        line-height: 1.5;
    }
    .section-card a{
        display: inline-block;
        text-transform: capitalize;
        color: hsl(0, 0%, 100%);
        margin-top: 20px;
        font-weight: 500;
    }
    .section-card a,
    .section-card h2,
    .section-card p{
        transition: color 1s ease;
    }
    .section-card:hover a,
    .section-card:hover h2,
    .section-card:hover p{
        color: hsl(0, 0%, 0%);
    }
    @media(max-width:991px){
        .section-cards{
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media(max-width:575px){
        .section-cards{
            grid-template-columns: repeat(1, 1fr);
        }
    }
    </style>
</head>
<body>
    <div class="section-card">
                    <span>
                        <div class="meta">Submitted by: <?php echo htmlspecialchars($item['pesantren_nama'] ?? 'Admin'); ?> | <?php echo date('d M Y', strtotime($item['created_at'])); ?> | Tipe: <?php echo htmlspecialchars($item['tipe'] ?? 'kolaborasi'); ?></div>
                    </span>
                    <h2><?php echo htmlspecialchars($item['judul']); ?></h2>
                    <h2>card heading</h2>
                    <p><?php echo htmlspecialchars($item['deskripsi']); ?></p>
                    <a href="#">read more</a>
                    <div class="actions">
                        <form method="POST" action="promosi.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this proposal? This action cannot be undone');">
                            <input type="hidden" name="action" value="delete_proposal">
                            <input type="hidden" name="kolaborasi_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="delete-icon" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        <button onclick="toggleEditForm(this, <?php echo $item['id']; ?>)" class="edit-icon" title="Edit proposal">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <a href="detailpromosi.php?id=<?php echo $item['id']; ?>" class="detail-btn">View Details</a>
                    <?php if ($isLoggedIn && $userRole === 'pengelola' && $item['pengguna_id'] == $pengguna_id && $item['tipe'] === 'kolaborasi'): ?>
                    <form method="POST" action="promosi.php" class="edit-form" id="edit-form-<?php echo $item['id']; ?>">
                        <input type="hidden" name="action" value="edit_proposal">
                        <input type="hidden" name="kolaborasi_id" value="<?php echo $item['id']; ?>">
                        <h3>Edit Proposal</h3>
                        <input type="text" name="judul" value="<?php echo htmlspecialchars($item['judul']); ?>" required>
                        <textarea name="deskripsi" required><?php echo htmlspecialchars($item['deskripsi']); ?></textarea>
                        <button type="submit" class="upload-btn">Upload</button>
                    </form>
                    <?php endif; ?>
                </div>
</body>
</html>