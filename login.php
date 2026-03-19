<?php
/**
 * Login Page
 * หน้าเข้าสู่ระบบ
 */

// เริ่ม Session
session_start();

// ตรวจสอบว่าล็อกอินอยู่แล้วหรือไม่
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// เรียกใช้งานไฟล์การเชื่อมต่อฐานข้อมูล
require_once('config/db_connect.php');

// เรียกใช้งานไฟล์ฟังก์ชั่น
require_once('includes/functions.php');

// ตรวจสอบการส่งฟอร์ม
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password']; // ไม่ต้อง sanitize เพราะจะไปทำใน hash

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // ล็อกอินสำเร็จ
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = isset($user['role']) ? $user['role'] : 'user';
            header('Location: index.php');
            exit;
        } else {
            $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
    } elseif ($username === 'admin' && $password === 'admin123') {
        // ให้บริการ admin แบบลัดเผื่อกรณีเริ่มต้นระบบครั้งแรก
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'ผู้ดูแลระบบ';
        $_SESSION['role'] = 'admin';
        header('Location: index.php');
        exit;
    } else {
        $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบสารสนเทศเพื่อการจัดการการขายเครื่องเขียน</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
        }
        .login-form {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-form mx-auto">
                    <div class="text-center mb-4">
                        <h2><i class="bi bi-pencil-square"></i> ระบบจัดการการขายเครื่องเขียน</h2>
                        <p class="text-muted">กรุณาเข้าสู่ระบบเพื่อใช้งาน</p>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">ชื่อผู้ใช้</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">รหัสผ่าน</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p>ยังไม่มีบัญชีผู้ใช้งานใช่หรือไม่? <a href="register.php">สมัครสมาชิก</a></p>
                        <p class="text-muted small">หมายเหตุ: ใช้ชื่อผู้ใช้ "admin" และรหัสผ่าน "admin123" เพื่อเข้าสู่ระบบได้เลยเช่นกัน</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>