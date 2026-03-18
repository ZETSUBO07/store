<?php
/**
 * Inventory Report Module
 * โมดูลสำหรับแสดงรายงานสินค้าคงเหลือ
 */

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// กำหนดตัวแปรสำหรับการค้นหา
$searchTerm = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';

// ดึงข้อมูลหมวดหมู่ทั้งหมด
$categories = array();
$categoriesSql = "SELECT * FROM catagory ORDER BY cat_desc ASC";
$categoriesResult = $conn->query($categoriesSql);

if ($categoriesResult->num_rows > 0) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// ดึงข้อมูลสินค้าคงเหลือ
$inventorySql = "SELECT p.*, c.cat_desc, 
                    (p.prod_price - p.prod_cost) AS profit_margin,
                    ((p.prod_price - p.prod_cost) / p.prod_cost * 100) AS profit_percentage,
                    (p.prod_cost * p.prod_amount) AS stock_value
                FROM product p
                LEFT JOIN catagory c ON p.cat_code = c.cat_code
                WHERE 1=1";

// เพิ่มเงื่อนไขการค้นหา
if (!empty($searchTerm)) {
    $searchTerm = $conn->real_escape_string($searchTerm);
    $inventorySql .= " AND (p.prod_id LIKE '%$searchTerm%' OR p.prod_name LIKE '%$searchTerm%')";
}

// เพิ่มเงื่อนไขการกรองตามหมวดหมู่
if (!empty($categoryFilter)) {
    $categoryFilter = $conn->real_escape_string($categoryFilter);
    $inventorySql .= " AND p.cat_code = '$categoryFilter'";
}

$inventorySql .= " ORDER BY p.prod_amount ASC, p.prod_name ASC";
$inventoryResult = $conn->query($inventorySql);

$inventory = array();
$totalItems = 0;
$totalValue = 0;
$lowStockCount = 0;

if ($inventoryResult->num_rows > 0) {
    while ($row = $inventoryResult->fetch_assoc()) {
        $inventory[] = $row;
        $totalItems += $row['prod_amount'];
        $totalValue += $row['stock_value'];
        if ($row['prod_amount'] < 10) {
            $lowStockCount++;
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> รายงานสินค้าคงเหลือ</h2>
    <a href="index.php?module=reports&action=index" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> กลับไปหน้ารายงาน
    </a>
</div>

<!-- ฟอร์มค้นหาและกรองข้อมูล -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title m-0">ค้นหาและกรองข้อมูล</h5>
    </div>
    <div class="card-body">
        <form action="index.php" method="get" class="row g-3">
            <input type="hidden" name="module" value="reports">
            <input type="hidden" name="action" value="inventory">
            
            <div class="col-md-5">
                <label for="search-input" class="form-label">ค้นหา</label>
                <input type="text" class="form-control" id="search-input" name="search" 
                       placeholder="ค้นหาตามรหัสหรือชื่อสินค้า" value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>
            
            <div class="col-md-5">
                <label for="category-filter" class="form-label">หมวดหมู่</label>
                <select class="form-select" id="category-filter" name="category">
                    <option value="">-- ทั้งหมด --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['cat_code']; ?>" <?php echo ($categoryFilter == $category['cat_code']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['cat_desc']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-search"></i> ค้นหา
                </button>
            </div>
        </form>
    </div>
</div>

<!-- สรุปข้อมูลสินค้าคงเหลือ -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card h-100 border-primary">
            <div class="card-header text-white bg-primary">
                <h5 class="card-title m-0">จำนวนสินค้าคงเหลือทั้งหมด</h5>
            </div>
            <div class="card-body">
                <h3 class="text-center text-primary"><?php echo number_format($totalItems); ?> ชิ้น</h3>
                <p class="text-center mb-0">จำนวนรายการสินค้า: <?php echo count($inventory); ?> รายการ</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-success">
            <div class="card-header text-white bg-success">
                <h5 class="card-title m-0">มูลค่าสินค้าคงเหลือ</h5>
            </div>
            <div class="card-body">
                <h3 class="text-center text-success"><?php echo number_format($totalValue, 2); ?> บาท</h3>
                <p class="text-center mb-0">คำนวณจากราคาทุน</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-danger">
            <div class="card-header text-white bg-danger">
                <h5 class="card-title m-0">สินค้าใกล้หมด</h5>
            </div>
            <div class="card-body">
                <h3 class="text-center text-danger"><?php echo number_format($lowStockCount); ?> รายการ</h3>
                <p class="text-center mb-0">จำนวนน้อยกว่า 10 ชิ้น</p>
            </div>
        </div>
    </div>
</div>

<!-- รายการสินค้าคงเหลือ -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title m-0">รายการสินค้าคงเหลือ</h5>
    </div>
    <div class="card-body">
        <?php if (count($inventory) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>รหัสสินค้า</th>
                            <th>ชื่อสินค้า</th>
                            <th>หมวดหมู่</th>
                            <th class="text-center">คงเหลือ</th>
                            <th class="text-center">หน่วย</th>
                            <th class="text-end">ราคาทุน</th>
                            <th class="text-end">ราคาขาย</th>
                            <th class="text-end">กำไร (%)</th>
                            <th class="text-end">มูลค่าคงเหลือ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $item): ?>
                            <tr <?php echo ($item['prod_amount'] < 10) ? 'class="table-warning"' : ''; ?>>
                                <td><?php echo htmlspecialchars($item['prod_id']); ?></td>
                                <td><?php echo htmlspecialchars($item['prod_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['cat_desc']); ?></td>
                                <td class="text-center <?php echo ($item['prod_amount'] < 10) ? 'text-danger fw-bold' : ''; ?>">
                                    <?php echo number_format($item['prod_amount']); ?>
                                </td>
                                <td class="text-center"><?php echo htmlspecialchars($item['prod_unit']); ?></td>
                                <td class="text-end"><?php echo number_format($item['prod_cost'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($item['prod_price'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($item['profit_percentage'], 2); ?>%</td>
                                <td class="text-end"><?php echo number_format($item['stock_value'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <th colspan="3" class="text-end">รวม:</th>
                            <th class="text-center"><?php echo number_format($totalItems); ?></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th class="text-end"><?php echo number_format($totalValue, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">ไม่พบข้อมูลสินค้าตามเงื่อนไขที่กำหนด</div>
        <?php endif; ?>
    </div>
</div>