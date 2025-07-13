<?php
session_start();
ob_start();
include '../includes/db.php';

$error = "";
$nipError = "";
$passwordError = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nip = $_POST['nip'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM Guru WHERE nip = :nip");
    $stmt->bindParam(':nip', $nip);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id_guru'],
            'nama' => $user['nama_guru'],
            'role' => $user['role']
        ];

        if ($user['role'] === 'admin') {
            header("Location: ../admin/index.php");
        } else {
            header("Location: ../guru/index.php");
        }
        exit;
    } else {
        $error = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <strong>Error!</strong> NIP atau password salah.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
        $nipError = "is-invalid";
        $passwordError = "is-invalid";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Management Salassika</title>
    <link rel="icon" type="image/jpeg" href="../assets/img/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Tambahkan Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.8s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: none; }
        }
        .form-control {
            border-radius: 10px;
            padding: 1.2rem 2.5rem 1.2rem 2.5rem;
            font-size: 0.95rem;
            transition: box-shadow 0.2s;
        }
        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 0 2px rgba(106, 17, 203, 0.2);
        }
        .input-group-text {
            background: transparent;
            border: none;
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6a11cb;
            font-size: 1.1rem;
            z-index: 2;
        }
        .input-group {
            position: relative;
        }
        .show-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6a11cb;
            font-size: 1.1rem;
            cursor: pointer;
            z-index: 2;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(106, 17, 203, 0.08);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(106, 17, 203, 0.15);
        }
        .logo {
            width: 60px;
            height: 60px;
            margin-bottom: 1rem;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 1.5rem;
            animation: fadeIn 0.5s ease-in;
        }
        @media (max-width: 576px) {
            .card-body { padding: 2rem; }
            h1 { font-size: 1.5rem; }
        }
    </style>
</head>

<body>
    <div class="loader-overlay" id="loaderOverlay">
        <div class="loader">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <div class="container vh-100 d-flex align-items-center justify-content-center">
        <div class="card col-lg-6 col-md-8 col-sm-10">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="../assets/img/logo.jpg" alt="Logo" class="logo mb-2">
                    <h1 class="text-primary fw-bold">SISTEM INFORMASI MANAGEMENT</h1>
                    <h5 class="text-primary fw-bold">SALASSIKA</h5>
                </div>

                <?php if (!empty($error)): ?>
                    <?= $error ?>
                <?php endif; ?>

                <form method="POST" action="" onsubmit="showLoader()">
                    <div class="mb-3 input-group">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        <input type="text" name="nip" class="form-control <?= $nipError ?>" placeholder="NIP" required style="padding-left:2.5rem;">
                        <?php if (!empty($nipError)): ?>
                            <div class="invalid-feedback">NIP tidak valid.</div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3 input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="passwordInput" class="form-control <?= $passwordError ?>" placeholder="Password" required style="padding-left:2.5rem;">
                        <button type="button" class="show-password" onclick="togglePassword()"><i class="fas fa-eye" id="eyeIcon"></i></button>
                        <?php if (!empty($passwordError)): ?>
                            <div class="invalid-feedback">Password tidak valid.</div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3">Login</button>
                </form>
            </div>
        </div>
    </div>
    <!-- <span class="text-white text-center pb-2">&copy; <?php echo date('Y'); ?> Sistem Informasi Management Salassika. All Rights Reserved.</span> -->
    <!-- filepath: c:\xampp\htdocs\absensi_sekolah\auth\login.php -->


    <script>
        function showLoader() {
            document.getElementById('loaderOverlay').style.display = 'flex';
        }

        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Sembunyikan loader jika ada error
        if (document.querySelector('.alert')) {
            document.getElementById('loaderOverlay').style.display = 'none';
        }
    </script>
</body>

</html>