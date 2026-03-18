<?php
/**
 * Products Search Module
 * โมดูลสำหรับการค้นหาข้อมูลสินค้า
 */

// ตรวจสอบคำค้นหา
$searchTerm = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// ดึงข้อมูลหมวดหมู่
$categories = array();
$catSql = "SELECT * FROM catagory ORDER BY cat_desc ASC";
$catResult = $conn->query($catSql);
if ($catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[$row['cat_code']] = $row['cat_desc'];
    }
}

// ค้นหาข้อมูลสินค้า
$products = array();
$sql = "SELECT p.*, c.cat_desc FROM product p 
        LEFT JOIN catagory c ON p.cat_code = c.cat_code";

if (!empty($searchTerm)) {
    $searchTerm = $conn->real_escape_string($searchTerm);
    $sql .= " WHERE p.prod_id LIKE '%$searchTerm%' 
            OR p.prod_name LIKE '%$searchTerm%' 
            OR c.cat_desc LIKE '%$searchTerm%'";
}

$sql .= " ORDER BY p.prod_id ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<h2 class="mb-4">
    <i class="bi bi-box-seam"></i> จัดการข้อมูลสินค้า
</h2>

<!-- ส่วนค้นหา -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <form action="index.php" method="get" id="search-form" class="d-flex">
                    <input type="hidden" name="module" value="products">
                    <input type="hidden" name="action" value="search">
                    
                    <input type="text" class="form-control me-2" name="search" id="search-input" 
                        placeholder="ค้นหาตามรหัส ชื่อสินค้า หรือหมวดหมู่" 
                        value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> ค้นหา
                    </button>
                </form>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="index.php?module=products&action=add" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> เพิ่มสินค้าใหม่
                </a>
            </div>
        </div>
    </div>
</div>

<!-- แสดงผลการค้นหา -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>รหัสสินค้า</th>
                        <th>ชื่อสินค้า</th>
                        <th>หมวดหมู่</th>
                        <th class="text-center">คงเหลือ</th>
                        <th class="text-end">ราคาทุน</th>
                        <th class="text-end">ราคาขาย</th>
                        <th class="text-center">หน่วย</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['prod_id']); ?></td>
                                <td><?php echo htmlspecialchars($product['prod_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['cat_desc']); ?></td>
                                <td class="text-center <?php echo ($product['prod_amount'] < 10) ? 'text-danger fw-bold' : ''; ?>">
                                    <?php echo number_format($product['prod_amount']); ?>
                                </td>
                                <td class="text-end"><?php echo number_format($product['prod_cost'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($product['prod_price'], 2); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($product['prod_unit']); ?></td>
                                <td class="text-center">
                                    <a href="index.php?module=products&action=edit&id=<?php echo $product['prod_id']; ?>" 
                                       class="btn btn-sm btn-warning me-1">
                                        <i class="bi bi-pencil"></i> แก้ไข
                                    </a>
                                    <a href="index.php?module=products&action=delete&id=<?php echo $product['prod_id']; ?>" 
                                       class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> ลบ
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">ไม่พบข้อมูลสินค้า</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- สรุปผลการค้นหา -->
        <div class="mt-3">
            <p>พบข้อมูลทั้งหมด <?php echo count($products); ?> รายการ</p>
        </div>
    </div>
</div>