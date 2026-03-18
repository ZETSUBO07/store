<?php
/**
 * Product Delete Module
 * โมดูลสำหรับลบข้อมูลสินค้า
 */

// ตรวจสอบรหัสสินค้า
if (!isset($_GET['id'])) {
    header('Location: index.php?module=products&action=search');
    exit;
}

$productId = sanitizeInput($_GET['id']);

// ตรวจสอบว่ามีการยืนยันการลบหรือไม่
$confirmed = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

// ดึงข้อมูลสินค้า
$productSql = "SELECT p.*, c.cat_desc FROM product p 
               LEFT JOIN catagory c ON p.cat_code = c.cat_code 
               WHERE p.prod_id = '$productId'";
$productResult = $conn->query($productSql);

if ($productResult->num_rows == 0) {
    $_SESSION['error'] = 'ไม่พบข้อมูลสินค้าที่ต้องการลบ';
    header('Location: index.php?module=products&action=search');
    exit;
}

$product = $productResult->fetch_assoc();

// ตรวจสอบว่าสินค้านี้มีข้อมูลการขายหรือไม่
$checkSaleSql = "SELECT COUNT(*) as count FROM sale_detail WHERE prod_id = '$productId'";
$checkSaleResult = $conn->query($checkSaleSql);
$saleCount = $checkSaleResult->fetch_assoc()['count'];

$hasTransactions = ($saleCount > 0);

// ถ้ามีการยืนยันการลบ
if ($confirmed && !$hasTransactions) {
    $deleteSql = "DELETE FROM product WHERE prod_id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param('s', $productId);
    
    if ($stmt->execute()) {
        // ลบสำเร็จ
        $_SESSION['success'] = 'ลบข้อมูลสินค้าเรียบร้อยแล้ว';
        header('Location: index.php?module=products&action=search');
        exit;
    } else {
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . $conn->error;
        header('Location: index.php?module=products&action=search');
        exit;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-trash"></i> ลบข้อมูลสินค้า</h2>
    <a href="index.php?module=products&action=search" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> กลับไปหน้าค้นหา
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h4>ข้อมูลสินค้า</h4>
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 150px;">รหัสสินค้า</th>
                        <td><?php echo htmlspecialchars($product['prod_id']); ?></td>
                    </tr>
                    <tr>
                        <th>ชื่อสินค้า</th>
                        <td><?php echo htmlspecialchars($product['prod_name']); ?></td>
                    </tr>
                    <tr>
                        <th>หมวดหมู่</th>
                        <td><?php echo htmlspecialchars($product['cat_desc']); ?></td>
                    </tr>
                    <tr>
                        <th>ราคาทุน</th>
                        <td><?php echo number_format($product['prod_cost'], 2); ?> บาท</td>
                    </tr>
                    <tr>
                        <th>ราคาขาย</th>
                        <td><?php echo number_format($product['prod_price'], 2); ?> บาท</td>
                    </tr>
                    <tr>
                        <th>จำนวนคงเหลือ</th>
                        <td><?php echo number_format($product['prod_amount']); ?> <?php echo htmlspecialchars($product['prod_unit']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php if ($hasTransactions): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i> ไม่สามารถลบสินค้านี้ได้ เนื่องจากมีประวัติการขายในระบบ
                <p class="mt-2">หากต้องการยกเลิกสินค้า แนะนำให้ปรับจำนวนสินค้าเป็น 0 แทนการลบข้อมูล</p>
            </div>
            <div class="text-center mt-4">
                <a href="index.php?module=products&action=edit&id=<?php echo $productId; ?>" class="btn btn-warning btn-lg">
                    <i class="bi bi-pencil"></i> แก้ไขข้อมูลสินค้า
                </a>
                <a href="index.php?module=products&action=search" class="btn btn-secondary btn-lg ms-2">
                    <i class="bi bi-arrow-left"></i> กลับไปหน้าค้นหา
                </a>
            </div>
        <?php else: ?>
            <?php if (!$confirmed): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i> คำเตือน: การลบข้อมูลสินค้าไม่สามารถเรียกคืนได้
                </div>
                <div class="text-center mt-4">
                    <a href="index.php?module=products&action=delete&id=<?php echo $productId; ?>&confirm=yes" class="btn btn-danger btn-lg" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบสินค้านี้?');">
                        <i class="bi bi-trash"></i> ยืนยันการลบข้อมูล
                    </a>
                    <a href="index.php?module=products&action=search" class="btn btn-secondary btn-lg ms-2">
                        <i class="bi bi-x-circle"></i> ยกเลิก
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>