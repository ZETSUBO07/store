<?php
/**
 * Process Sale Module
 * โมดูลสำหรับประมวลผลการบันทึกข้อมูลการขาย
 */

// ตรวจสอบว่าเป็นการ submit ฟอร์มการขาย
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?module=sales&action=add');
    exit;
}

// ตรวจสอบข้อมูลการขาย
$saleId = sanitizeInput($_POST['sale_id']);
$customerId = sanitizeInput($_POST['customer_id']);
$saleDate = sanitizeInput($_POST['sale_date']);

// ตรวจสอบรายการสินค้า
if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    $_SESSION['error'] = 'กรุณาเพิ่มรายการสินค้าอย่างน้อย 1 รายการ';
    header('Location: index.php?module=sales&action=add');
    exit;
}

// เริ่มการทำงานแบบ Transaction
$conn->begin_transaction();

try {
    // บันทึกข้อมูลการขาย
    $sql = "INSERT INTO sale (sale_id, cus_id, sale_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $saleId, $customerId, $saleDate);
    
    if (!$stmt->execute()) {
        throw new Exception('ไม่สามารถบันทึกข้อมูลการขายได้: ' . $conn->error);
    }
    
    // บันทึกรายละเอียดการขาย
    $productIds = $_POST['product_id'];
    $quantities = $_POST['product_qty'];
    $prices = $_POST['product_price'];
    
    for ($i = 0; $i < count($productIds); $i++) {
        $productId = sanitizeInput($productIds[$i]);
        $quantity = sanitizeInput($quantities[$i]);
        $price = sanitizeInput($prices[$i]);
        $itemNumber = $i + 1;
        
        // ดึงต้นทุนสินค้า
        $costSql = "SELECT prod_cost FROM product WHERE prod_id = ?";
        $costStmt = $conn->prepare($costSql);
        $costStmt->bind_param('s', $productId);
        $costStmt->execute();
        $costResult = $costStmt->get_result();
        $costRow = $costResult->fetch_assoc();
        $cost = $costRow['prod_cost'];
        
        // บันทึกรายละเอียดการขาย
        $detailSql = "INSERT INTO sale_detail (sale_id, items, prod_id, sale_cost, sale_price, sale_amount) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $detailStmt = $conn->prepare($detailSql);
        $detailStmt->bind_param('sissdi', $saleId, $itemNumber, $productId, $cost, $price, $quantity);
        
        if (!$detailStmt->execute()) {
            throw new Exception('ไม่สามารถบันทึกรายละเอียดการขายได้: ' . $conn->error);
        }
        
        // ปรับปรุงจำนวนสินค้าคงเหลือ
        $updateStockSql = "UPDATE product SET prod_amount = prod_amount - ? WHERE prod_id = ?";
        $updateStockStmt = $conn->prepare($updateStockSql);
        $updateStockStmt->bind_param('is', $quantity, $productId);
        
        if (!$updateStockStmt->execute()) {
            throw new Exception('ไม่สามารถปรับปรุงจำนวนสินค้าคงเหลือได้: ' . $conn->error);
        }
    }
    
    // ยืนยันการทำงาน Transaction
    $conn->commit();
    
    // บันทึกข้อความแจ้งเตือนความสำเร็จ
    $_SESSION['success'] = 'บันทึกข้อมูลการขายเรียบร้อยแล้ว';
    
    // Redirect ไปยังหน้ารายละเอียดการขาย
    header('Location: index.php?module=sales&action=view&id=' . $saleId);
    exit;
    
} catch (Exception $e) {
    // ยกเลิกการทำงาน Transaction
    $conn->rollback();
    
    // บันทึกข้อความแจ้งเตือนข้อผิดพลาด
    $_SESSION['error'] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage();
    
    // Redirect กลับไปยังหน้าเพิ่มข้อมูลการขาย
    header('Location: index.php?module=sales&action=add');
    exit;
}