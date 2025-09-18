<?php
session_start();

// Ambil pesan dari session (jika ada)
$success_message = $_SESSION['success'] ?? null;

// Hapus pesan agar tidak muncul lagi setelah refresh
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sukses</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h2 {
            color: #3a7ca5;
            margin-bottom: 15px;
        }
        p {
            font-size: 1.1rem;
            color: #333;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            background: #3a7ca5;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
        .btn:hover {
            background: #2f6b8f;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>✅ Proses Berhasil</h2>
    <?php if ($success_message): ?>
        <p><?= htmlspecialchars($success_message) ?></p>
    <?php else: ?>
        <p>Data telah berhasil diproses.</p>
    <?php endif; ?>
    <a href="index.php" class="btn">Kembali ke Beranda</a>
</div>
</body>
</html>
