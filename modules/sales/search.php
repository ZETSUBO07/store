<?php
/**
 * Sales Search Module
 * โมดูลสำหรับการค้นหาข้อมูลการขาย
 */

// ตรวจสอบคำค้นหา
$searchTerm = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// ค้นหาข้อมูลการขาย
$sales = searchSales($conn, $searchTerm);
?>

<h2 class="mb-4">
    <i class="bi bi-search"></i> ค้นหาข้อมูลการขาย
</h2>

<!-- ส่วนค้นหา -->
<div class="card mb-4">
    <div class="card-body">
        <form action="index.php" method="get" id="search-form">
            <input type="hidden" name="module" value="sales">
            <input type="hidden" name="action" value="search">
            
            <div class="input-group">
                <input type="text" class="form-control" name="search" id="search-input" 
                    placeholder="ค้นหาตามเลขที่ขาย ชื่อลูกค้า และรหัสลูกค้า" 
                    value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> ค้นหา
                </button>
            </div>
        </form>
    </div>
</div>

<!-- แสดงผลการค้นหา -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>เลขที่ขาย</th>
                        <th>วันที่</th>
                        <th>รหัสลูกค้า</th>
                        <th>ชื่อลูกค้า</th>
                        <th class="text-end">จำนวนเงิน</th>
                        <th class="text-center">ดูรายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($sales) > 0): ?>
                        <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                                <td><?php echo formatDateThai($sale['sale_date']); ?></td>
                                <td><?php echo htmlspecialchars($sale['cus_id']); ?></td>
                                <td><?php echo htmlspecialchars($sale['cus_name']); ?></td>
                                <td class="text-end"><?php echo number_format($sale['sale_total'], 2); ?> บาท</td>
                                <td class="text-center">
                                    <a href="index.php?module=sales&action=view&id=<?php echo $sale['sale_id']; ?>" 
                                       class="btn btn-sm btn-success">
                                        <i class="bi bi-eye"></i> รายละเอียด
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">ไม่พบข้อมูลการขาย</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- สรุปผลการค้นหา -->
        <div class="mt-3">
            <p>พบข้อมูลทั้งหมด <?php echo count($sales); ?> รายการ</p>
        </div>
    </div>
</div>