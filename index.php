<?php
/**
 * Main Entry Point
 * จุดเข้าถึงหลักของระบบ
 */

// เริ่ม Output Buffering เพื่อแก้ปัญหา headers already sent
ob_start();

// เริ่ม Session
session_start();

// เรียกใช้งานไฟล์การเชื่อมต่อฐานข้อมูล
require_once('config/db_connect.php');

// เรียกใช้งานไฟล์ฟังก์ชั่น
require_once('includes/functions.php');

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// กำหนด module และ action เริ่มต้น
$module = isset($_GET['module']) ? sanitizeInput($_GET['module']) : 'home';
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'index';

// กำหนด active tab
if ($module == 'home') {
    $activeTab = 'home';
} elseif ($module == 'sales') {
    if ($action == 'add') {
        $activeTab = 'add';
    } else {
        $activeTab = 'search';
    }
} elseif ($module == 'products') {
    $activeTab = 'products';
} elseif ($module == 'reports') {
    $activeTab = 'report';
} else {
    $activeTab = 'home';
}

// เรียกใช้งาน header
include('includes/header.php');

// ตรวจสอบและเรียกใช้งานไฟล์ตาม module และ action
$filePath = "modules/{$module}/{$action}.php";

if (file_exists($filePath)) {
    include($filePath);
} else {
    // หากไม่พบไฟล์ให้แสดงหน้าหลัก
    echo "<div class='alert alert-danger'>ไม่พบโมดูลที่ต้องการ กำลังกลับไปหน้าหลัก...</div>";
    
    // ลองเรียกหน้าหลัก
    if (file_exists("modules/home/index.php")) {
        include("modules/home/index.php");
    } else {
        echo "<div class='alert alert-danger'>ไม่พบโมดูลหน้าหลัก กรุณาตรวจสอบการติดตั้งระบบ</div>";
    }
}

// เรียกใช้งาน footer
include('includes/footer.php');

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();

// ปิด Output Buffering และส่งข้อมูลทั้งหมด
ob_end_flush();
?>