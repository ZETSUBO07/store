<?php
// กำหนด active tab ถ้ายังไม่มีการกำหนด
if (!isset($activeTab)) {
    $activeTab = '';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบสารสนเทศเพื่อการจัดการการขายเครื่องเขียน</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-fluid p-0">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="index.php">ระบบจัดการการขายเครื่องเขียน</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($activeTab == 'home') ? 'active' : ''; ?>" href="index.php">
                                <i class="bi bi-house-door"></i> หน้าหลัก
                            </a>
                        </li>
                        <!-- เมนูจัดการข้อมูลสินค้า -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($activeTab == 'products') ? 'active' : ''; ?>" href="index.php?module=products&action=search">
                                <i class="bi bi-box-seam"></i> จัดการสินค้า
                            </a>
                        </li>
                        <!-- เมนูจัดการการขาย -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?php echo (in_array($activeTab, ['search', 'add'])) ? 'active' : ''; ?>" href="#" id="salesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-cart"></i> การขาย
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="salesDropdown">
                                <li>
                                    <a class="dropdown-item" href="index.php?module=sales&action=search">
                                        <i class="bi bi-search"></i> ค้นหาข้อมูลการขาย
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="index.php?module=sales&action=add">
                                        <i class="bi bi-plus-circle"></i> เพิ่มข้อมูลการขาย
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- เมนูรายงาน -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?php echo ($activeTab == 'report') ? 'active' : ''; ?>" href="#" id="reportDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-file-earmark-text"></i> รายงาน
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="reportDropdown">
                                <li>
                                    <a class="dropdown-item" href="index.php?module=reports&action=index">
                                        <i class="bi bi-house-door"></i> หน้าหลักรายงาน
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="index.php?module=reports&action=sales_summary">
                                        <i class="bi bi-bar-chart"></i> สรุปยอดขาย
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="index.php?module=reports&action=inventory">
                                        <i class="bi bi-box-seam"></i> สินค้าคงเหลือ
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    
                    <!-- ส่วนแสดงข้อมูลผู้ใช้งาน -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> ผู้ดูแลระบบ
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="logout.php">
                                        <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
        <div class="container mt-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">