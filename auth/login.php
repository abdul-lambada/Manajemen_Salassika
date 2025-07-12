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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            min-height: 100vh;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .form-control {
            border-radius: 10px;
            padding: 1.2rem;
            font-size: 0.9rem;
        }

        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 0 2px rgba(106, 17, 203, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: transform 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .loader-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
        }

        .loader {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
        }

        .loader div {
            position: absolute;
            border: 4px solid #6a11cb;
            border-radius: 50%;
            animation: loader 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }

        .loader div:nth-child(1) {
            width: 60px;
            height: 60px;
            animation-delay: -0.45s;
        }

        .loader div:nth-child(2) {
            width: 50px;
            height: 50px;
            animation-delay: -0.3s;
        }

        .loader div:nth-child(3) {
            width: 40px;
            height: 40px;
            animation-delay: -0.15s;
        }

        @keyframes loader {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 1.5rem;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 576px) {
            .card-body {
                padding: 2rem;
            }

            h1 {
                font-size: 1.5rem;
            }
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
                    <h1 class="text-primary fw-bold">SISTEM INFORMASI MANAGEMENT</h1>
                    <h5 class="text-primary fw-bold">SALASSIKA</h5>
                </div>

                <?php if (!empty($error)): ?>
                    <?= $error ?>
                <?php endif; ?>

                <form method="POST" action="" onsubmit="showLoader()">
                    <div class="mb-3">
                        <input type="text" name="nip" class="form-control <?= $nipError ?>" placeholder="NIP" required>
                        <?php if (!empty($nipError)): ?>
                            <div class="invalid-feedback">NIP tidak valid.</div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control <?= $passwordError ?>" placeholder="Password" required>
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

        // Sembunyikan loader jika ada error
        if (document.querySelector('.alert')) {
            document.getElementById('loaderOverlay').style.display = 'none';
        }
    </script>
</body>

</html>