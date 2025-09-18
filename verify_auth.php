<?php
session_start();
header('Content-Type: application/json');

// Cek apakah admin sudah login
if (!isset($_SESSION['admin'])) {
    echo json_encode(['success' => false]);
    exit;
}

// 🔐 Hash dari PIN asli (hasil dari password_hash('889900'))
// Ganti dengan hash milikmu. Untuk sekarang, contoh PIN: "889900"
$hashed_pin = '$2y$10$1VWViYEoobns7DhsAHXADepNnFWpGVFHP6oEEYTgqnUjbQLlQwjvy'; // <-- Ganti dengan hashmu

// Ambil input PIN dari POST
$pin = $_POST['pin'] ?? '';

if (empty($pin)) {
    echo json_encode(['success' => false]);
    exit;
}

// Verifikasi PIN menggunakan hash
if (password_verify(trim($pin), $hashed_pin)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>