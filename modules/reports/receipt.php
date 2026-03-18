<?php
/**
 * Receipt Report Module
 * โมดูลสำหรับแสดงใบเสร็จรับเงิน
 */

// ตรวจสอบรหัสการขาย
if (!isset($_GET['id'])) {
    header('Location: index.php?module=reports&action=index');
    exit;
}

$saleId = sanitizeInput($_GET['id']);
$sale = getSaleDetails($conn, $saleId);

// ถ้าไม่พบข้อมูลการขาย
if (!$sale) {
    echo showAlert('ไม่พบข้อมูลการขายที่ระบุ', 'danger');
    echo '<div class="text-center mt-3"><a href="index.php?module=reports&action=index" class="btn btn-primary">กลับไปหน้ารายงาน</a></div>';
    exit;
}

// เรียกใช้ template ใบเสร็จ
include('receipt_template.php');
?>