<?php
session_start();
include 'db.php';

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Semua kolom harus diisi.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit;
        } else {
            $error = "Username atau password salah!";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Halaman Utama | SIPADU - SOSIAL</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #f5f7fa;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #444;
      line-height: 1.6;
      position: relative;
    }

    /* Tombol Panduan & Login Admin di pojok kanan atas */
    .top-right-links {
      position: absolute;
      top: 20px;
      right: 20px;
      display: flex;
      gap: 12px;
      z-index: 100;
    }

    .btn-panduan {
      background: #2c5f2d;
      color: white;
      padding: 10px 15px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 6px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }

    .btn-panduan:hover {
      background: #1f4420;
      transform: translateY(-2px);
    }

    .admin-login-link {
      background: #3a7ca5;
      color: white;
      padding: 10px 15px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }

    .admin-login-link:hover {
      background: #2f6b8f;
      transform: translateY(-2px);
    }

    .login-box {
      background: white;
      width: 100%;
      max-width: 480px;
      padding: 50px 40px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      text-align: center;
      z-index: 10;
    }

    .header {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 20px;
      justify-content: center;
    }

    .logo {
      width: 90px;
      height: 90px;
      object-fit: contain;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .instansi {
      text-align: left;
    }

    .instansi .kota {
      font-size: 19px;
      font-weight: 700;
      color: #3a7ca5;
    }

    .instansi .dinas {
      font-size: 16px;
      font-weight: 500;
      color: #555;
    }

    .app-title {
      font-size: 22px;
      font-weight: 600;
      color: #333;
      margin: 15px 0 30px;
      letter-spacing: 0.3px;
    }

    .form-group {
      position: relative;
      margin-bottom: 24px;
      text-align: left;
    }

    .form-group input {
      width: 100%;
      padding: 14px 0 12px 0;
      border: none;
      border-bottom: 2px solid #e0e0e0;
      font-size: 16px;
      outline: none;
      background: transparent;
      transition: all 0.3s ease;
      color: #333;
    }

    .form-group input:focus {
      border-color: #3a7ca5;
    }

    .form-group label {
      position: absolute;
      left: 0;
      top: 14px;
      font-size: 16px;
      color: #777;
      pointer-events: none;
      transition: all 0.3s ease;
    }

    .form-group input:focus ~ label,
    .form-group input:not(:placeholder-shown) ~ label {
      top: -10px;
      font-size: 12px;
      color: #3a7ca5;
      font-weight: 500;
    }

    .password-toggle {
      position: relative;
    }

    .password-toggle i {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: #999;
      cursor: pointer;
      transition: color 0.3s;
    }

    .password-toggle i:hover {
      color: #3a7ca5;
    }

    .error {
      background: #f9eaea;
      color: #c44545;
      padding: 14px;
      border-radius: 10px;
      font-size: 14px;
      margin-bottom: 25px;
      border: 1px solid #eed1d1;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      text-align: center;
    }

    .login-box button {
      width: 100%;
      padding: 14px;
      background: #3a7ca5;
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
      box-shadow: 0 4px 12px rgba(58, 124, 165, 0.2);
    }

    .login-box button:hover {
      background: #2f6b8f;
      transform: translateY(-2px);
    }

    .footer {
      margin-top: 35px;
      font-size: 13px;
      color: #888;
    }

    .footer a {
      color: #3a7ca5;
      text-decoration: none;
      font-weight: 500;
    }

    .footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <!-- Dua tombol di pojok kanan atas: Panduan & Login Admin -->
  <div class="top-right-links">
    <!-- Tombol Buka Panduan PDF -->
    <a href="pdf/SIPADU-SOSIAL.pdf" target="_blank" class="btn-panduan">
      <i class="fas fa-book"></i> Panduan
    </a>

    <!-- Tombol Login Admin -->
    <a href="login_admin.php" class="admin-login-link">
      <i class="fas fa-user-shield"></i> 
    </a>
  </div>

  <!-- Form Login Pengguna -->
  <div class="login-box">
    <div class="header">
      <img src="images/kissme.png" alt="Logo Dinas Sosial Kota Tanjungpinang" class="logo" />
      <div class="instansi">
        <div class="kota">DINAS SOSIAL</div>
        <div class="dinas">Kota Tanjungpinang</div>
      </div>
    </div>

    <div class="app-title">Sentra Pelayanan dan Pengaduan Publik</div>

    <?php if (!empty($error)): ?>
      <div class="error">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <input type="text" name="username" id="username" placeholder=" " required autocomplete="off">
        <label for="username">Username</label>
      </div>

      <div class="form-group password-toggle">
        <input type="password" name="password" id="password" placeholder=" " required>
        <label for="password">Password</label>
        <i class="far fa-eye" id="togglePassword"></i>
      </div>

      <button type="submit" name="login">Masuk</button>
    </form>

    <div class="footer">
      &copy; <?= date('Y') ?> Dinas Sosial Kota Tanjungpinang.<br>
      Seluruh Hak Cipta Dilindungi.
    </div>
  </div>

  <script>
    // Toggle password
    const togglePassword = document.querySelector("#togglePassword");
    const password = document.querySelector("#password");

    togglePassword.addEventListener("click", function () {
      const type = password.getAttribute("type") === "password" ? "text" : "password";
      password.setAttribute("type", type);
      this.classList.toggle("fa-eye");
      this.classList.toggle("fa-eye-slash");
    });

    // Animasi label
    document.querySelectorAll('.form-group input').forEach(input => {
      input.addEventListener('input', function () {
        if (this.value) {
          this.nextElementSibling.style.top = '-10px';
          this.nextElementSibling.style.fontSize = '12px';
          this.nextElementSibling.style.color = '#3a7ca5';
        } else if (!this.matches(':focus')) {
          this.nextElementSibling.style.top = '14px';
          this.nextElementSibling.style.fontSize = '16px';
          this.nextElementSibling.style.color = '#777';
        }
      });

      input.addEventListener('focus', function () {
        this.nextElementSibling.style.color = '#3a7ca5';
      });

      input.addEventListener('blur', function () {
        if (!this.value) {
          this.nextElementSibling.style.color = '#777';
        }
      });
    });
  </script>
</body>
</html>