<?php
/**
 * Sales Summary Report Module
 * โมดูลสำหรับแสดงรายงานสรุปยอดขาย
 */

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// กำหนดค่าเริ่มต้นสำหรับวันที่
$currentDate = date('Y-m-d');
$firstDayOfMonth = date('Y-m-01');
$lastDayOfMonth = date('Y-m-t');

$startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : $firstDayOfMonth;
$endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : $lastDayOfMonth;

// ดึงข้อมูลรายงานยอดขาย
$salesReport = getSalesReport($conn, $startDate, $endDate);

// ดึงข้อมูลสินค้าขายดี
$topProducts = array();
$topProductsSql = "SELECT p.prod_id, p.prod_name, SUM(sd.sale_amount) as total_qty, 
                        SUM(sd.sale_price * sd.sale_amount) as total_sales
                    FROM sale_detail sd
                    LEFT JOIN product p ON sd.prod_id = p.prod_id
                    LEFT JOIN sale s ON sd.sale_id = s.sale_id
                    WHERE s.sale_date BETWEEN '$startDate' AND '$endDate'
                    GROUP BY p.prod_id, p.prod_name
                    ORDER BY total_sales DESC
                    LIMIT 5";
$topProductsResult = $conn->query($topProductsSql);

if ($topProductsResult->num_rows > 0) {
    while ($row = $topProductsResult->fetch_assoc()) {
        $topProducts[] = $row;
    }
}

// ดึงข้อมูลลูกค้าที่ซื้อมากที่สุด
$topCustomers = array();
$topCustomersSql = "SELECT c.cus_id, c.cus_name, COUNT(s.sale_id) as transaction_count, 
                        SUM(sd.sale_price * sd.sale_amount) as total_amount
                    FROM sale s
                    LEFT JOIN customer c ON s.cus_id = c.cus_id
                    LEFT JOIN sale_detail sd ON s.sale_id = sd.sale_id
                    WHERE s.sale_date BETWEEN '$startDate' AND '$endDate'
                    GROUP BY c.cus_id, c.cus_name
                    ORDER BY total_amount DESC
                    LIMIT 5";
$topCustomersResult = $conn->query($topCustomersSql);

if ($topCustomersResult->num_rows > 0) {
    while ($row = $topCustomersResult->fetch_assoc()) {
        $topCustomers[] = $row;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-bar-chart"></i> รายงานสรุปยอดขาย</h2>
    <a href="index.php?module=reports&action=index" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> กลับไปหน้ารายงาน
    </a>
</div>

<!-- ฟอร์มกำหนดช่วงวันที่ -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title m-0">กำหนดช่วงวันที่</h5>
    </div>
    <div class="card-body">
        <form action="index.php" method="get" class="row g-3">
            <input type="hidden" name="module" value="reports">
            <input type="hidden" name="action" value="sales_summary">
            
            <div class="col-md-4">
                <label for="start-date" class="form-label">วันที่เริ่มต้น</label>
                <input type="date" class="form-control" id="start-date" name="start_date" value="<?php echo $startDate; ?>" required>
            </div>
            
            <div class="col-md-4">
                <label for="end-date" class="form-label">วันที่สิ้นสุด</label>
                <input type="date" class="form-control" id="end-date" name="end_date" value="<?php echo $endDate; ?>" required>
            </div>
            
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-search"></i> แสดงรายงาน
                </button>
            </div>
        </form>
    </div>
</div>

<!-- สรุปยอดขาย - แก้ไขให้มีเพียง 2 คอลัมน์ -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100 border-primary">
            <div class="card-header text-white bg-primary">
                <h5 class="card-title m-0">ยอดขายรวม</h5>
            </div>
            <div class="card-body">
                <h3 class="text-center text-primary"><?php echo number_format($salesReport['total_amount'], 2); ?> บาท</h3>
                <p class="text-center mb-0">จำนวนรายการขาย: <?php echo number_format($salesReport['total_transactions']); ?> รายการ</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 border-success">
            <div class="card-header text-white bg-success">
                <h5 class="card-title m-0">ยอดขายเฉลี่ยต่อวัน</h5>
            </div>
            <div class="card-body">
                <?php
                // คำนวณจำนวนวัน
                $startDateTime = new DateTime($startDate);
                $endDateTime = new DateTime($endDate);
                $interval = $startDateTime->diff($endDateTime);
                $days = $interval->days + 1; // บวก 1 เพื่อนับวันสุดท้ายด้วย
                
                // คำนวณยอดขายเฉลี่ยต่อวัน
                $avgDaily = ($days > 0) ? $salesReport['total_amount'] / $days : 0;
                ?>
                <h3 class="text-center text-success"><?php echo number_format($avgDaily, 2); ?> บาท</h3>
                <p class="text-center mb-0">ระยะเวลา: <?php echo $days; ?> วัน</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- สินค้าขายดี -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="card-title m-0">สินค้าขายดี (Top 5)</h5>
            </div>
            <div class="card-body">
                <?php if (count($topProducts) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>รหัสสินค้า</th>
                                    <th>ชื่อสินค้า</th>
                                    <th class="text-center">จำนวนขาย</th>
                                    <th class="text-end">ยอดขาย</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['prod_id']); ?></td>
                                        <td><?php echo htmlspecialchars($product['prod_name']); ?></td>
                                        <td class="text-center"><?php echo number_format($product['total_qty']); ?></td>
                                        <td class="text-end"><?php echo number_format($product['total_sales'], 2); ?> บาท</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">ไม่พบข้อมูลสินค้าในช่วงเวลาที่กำหนด</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- ลูกค้าที่ซื้อมากที่สุด -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="card-title m-0">ลูกค้าที่ซื้อมากที่สุด (Top 5)</h5>
            </div>
            <div class="card-body">
                <?php if (count($topCustomers) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>รหัสลูกค้า</th>
                                    <th>ชื่อลูกค้า</th>
                                    <th class="text-center">จำนวนรายการ</th>
                                    <th class="text-end">ยอดซื้อรวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topCustomers as $customer): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($customer['cus_id']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['cus_name']); ?></td>
                                        <td class="text-center"><?php echo number_format($customer['transaction_count']); ?></td>
                                        <td class="text-end"><?php echo number_format($customer['total_amount'], 2); ?> บาท</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">ไม่พบข้อมูลลูกค้าในช่วงเวลาที่กำหนด</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- รายการขายในช่วงเวลา -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title m-0">รายการขายในช่วงเวลา</h5>
    </div>
    <div class="card-body">
        <?php if (count($salesReport['sales']) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>เลขที่ขาย</th>
                            <th>วันที่</th>
                            <th>ลูกค้า</th>
                            <th class="text-end">ยอดขาย</th>
                            <th class="text-center">ดูรายละเอียด</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salesReport['sales'] as $sale): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                                <td><?php echo formatDateThai($sale['sale_date']); ?></td>
                                <td><?php echo htmlspecialchars($sale['cus_name']); ?></td>
                                <td class="text-end"><?php echo number_format($sale['sale_total'], 2); ?> บาท</td>
                                <td class="text-center">
                                    <a href="index.php?module=sales&action=view&id=<?php echo $sale['sale_id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> รายละเอียด
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <th colspan="3" class="text-end">ยอดรวมทั้งสิ้น:</th>
                            <th class="text-end"><?php echo number_format($salesReport['total_amount'], 2); ?> บาท</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">ไม่พบข้อมูลการขายในช่วงเวลาที่กำหนด</div>
        <?php endif; ?>
    </div>
</div>