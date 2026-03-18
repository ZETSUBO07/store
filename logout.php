<?php
/**
 * Logout Page
 * หน้าออกจากระบบ
 */

// เริ่ม Session
session_start();

// ลบข้อมูล Session ทั้งหมด
session_unset();
session_destroy();

// Redirect ไปยังหน้าล็อกอิน
header('Location: login.php');
exit;
?>