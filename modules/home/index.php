<?php
/**
 * Home Page Module
 * โมดูลสำหรับแสดงหน้าหลักของระบบ
 */

// กำหนด active tab
$activeTab = 'home';

// ดึงข้อมูลสรุปจากระบบ
// 1. จำนวนสินค้าทั้งหมด
$productCountSql = "SELECT COUNT(*) as total FROM product";
$productCountResult = $conn->query($productCountSql);
$productCount = $productCountResult->fetch_assoc()['total'];

// 2. มูลค่าสินค้าคงเหลือทั้งหมด
$stockValueSql = "SELECT SUM(prod_cost * prod_amount) as total_value FROM product";
$stockValueResult = $conn->query($stockValueSql);
$stockValue = $stockValueResult->fetch_assoc()['total_value'] ?? 0;

// 3. สินค้าใกล้หมด (น้อยกว่า 10 ชิ้น)
$lowStockSql = "SELECT COUNT(*) as total FROM product WHERE prod_amount < 10";
$lowStockResult = $conn->query($lowStockSql);
$lowStockCount = $lowStockResult->fetch_assoc()['total'];

// 4. ยอดขายวันนี้
$today = date('Y-m-d');
$todaySalesSql = "SELECT SUM(sd.sale_price * sd.sale_amount) as total 
                  FROM sale s 
                  JOIN sale_detail sd ON s.sale_id = sd.sale_id 
                  WHERE s.sale_date = '$today'";
$todaySalesResult = $conn->query($todaySalesSql);
$todaySales = $todaySalesResult->fetch_assoc()['total'] ?? 0;

// 5. จำนวนการขายวันนี้
$todayTransactionsSql = "SELECT COUNT(DISTINCT sale_id) as total FROM sale WHERE sale_date = '$today'";
$todayTransactionsResult = $conn->query($todayTransactionsSql);
$todayTransactions = $todayTransactionsResult->fetch_assoc()['total'];

// 6. สินค้าขายดี 5 อันดับ
$topProductsSql = "SELECT p.prod_id, p.prod_name, SUM(sd.sale_amount) as total_qty 
                   FROM sale_detail sd 
                   JOIN product p ON sd.prod_id = p.prod_id 
                   JOIN sale s ON sd.sale_id = s.sale_id 
                   GROUP BY p.prod_id, p.prod_name 
                   ORDER BY total_qty DESC 
                   LIMIT 5";
$topProductsResult = $conn->query($topProductsSql);
$topProducts = [];
while ($row = $topProductsResult->fetch_assoc()) {
    $topProducts[] = $row;
}

// 7. การขายล่าสุด
$recentSalesSql = "SELECT s.sale_id, s.sale_date, c.cus_name, 
                    SUM(sd.sale_price * sd.sale_amount) as total_amount 
                   FROM sale s 
                   JOIN customer c ON s.cus_id = c.cus_id 
                   JOIN sale_detail sd ON s.sale_id = sd.sale_id 
                   GROUP BY s.sale_id, s.sale_date, c.cus_name 
                   ORDER BY s.sale_date DESC, s.sale_id DESC 
                   LIMIT 5";
$recentSalesResult = $conn->query($recentSalesSql);
$recentSales = [];
while ($row = $recentSalesResult->fetch_assoc()) {
    $recentSales[] = $row;
}
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h1>
                    <i class="bi bi-house-door"></i> ยินดีต้อนรับเข้าสู่ระบบจัดการร้านเครื่องเขียน
                </h1>
                <p class="lead">ระบบสารสนเทศเพื่อการจัดการการขายเครื่องเขียนแบบครบวงจร</p>
            </div>
        </div>
    </div>
</div>

<!-- แดชบอร์ดสรุปข้อมูล -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card h-100 border-primary">
            <div class="card-body text-center">
                <i class="bi bi-box-seam text-primary" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?php echo number_format($productCount); ?></h3>
                <h5>จำนวนสินค้าทั้งหมด</h5>
            </div>
            <div class="card-footer">
                <a href="index.php?module=products&action=search" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> ดูสินค้าทั้งหมด
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card h-100 border-success">
            <div class="card-body text-center">
                <i class="bi bi-currency-exchange text-success" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?php echo number_format($stockValue, 2); ?></h3>
                <h5>มูลค่าสินค้าคงเหลือ (บาท)</h5>
            </div>
            <div class="card-footer">
                <a href="index.php?module=reports&action=inventory" class="btn btn-success btn-sm w-100">
                    <i class="bi bi-list-check"></i> รายงานสินค้าคงเหลือ
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card h-100 border-danger">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?php echo number_format($lowStockCount); ?></h3>
                <h5>สินค้าใกล้หมด</h5>
            </div>
            <div class="card-footer">
                <a href="index.php?module=reports&action=inventory&low_stock=1" class="btn btn-danger btn-sm w-100">
                    <i class="bi bi-arrow-down-square"></i> ดูสินค้าใกล้หมด
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card h-100 border-info">
            <div class="card-body text-center">
                <i class="bi bi-cash-coin text-info" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?php echo number_format($todaySales, 2); ?></h3>
                <h5>ยอดขายวันนี้ (บาท)</h5>
            </div>
            <div class="card-footer">
                <a href="index.php?module=reports&action=sales_summary&start_date=<?php echo $today; ?>&end_date=<?php echo $today; ?>" class="btn btn-info btn-sm w-100 text-white">
                    <i class="bi bi-graph-up"></i> ดูรายงานยอดขาย
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- ทางลัดเมนูหลัก -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0"><i class="bi bi-lightning"></i> เมนูลัด</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="index.php?module=sales&action=add" class="btn btn-primary d-flex align-items-center py-3">
                        <i class="bi bi-cart-plus me-3" style="font-size: 1.5rem;"></i>
                        <div class="text-start">
                            <strong>เพิ่มการขายใหม่</strong>
                            <div class="small">บันทึกการขายสินค้าให้กับลูกค้า</div>
                        </div>
                    </a>
                    
                    <a href="index.php?module=products&action=add" class="btn btn-success d-flex align-items-center py-3">
                        <i class="bi bi-box-seam me-3" style="font-size: 1.5rem;"></i>
                        <div class="text-start">
                            <strong>เพิ่มสินค้าใหม่</strong>
                            <div class="small">เพิ่มรายการสินค้าใหม่เข้าสู่ระบบ</div>
                        </div>
                    </a>
                    
                    <a href="index.php?module=reports&action=index" class="btn btn-info d-flex align-items-center py-3 text-white">
                        <i class="bi bi-file-earmark-text me-3" style="font-size: 1.5rem;"></i>
                        <div class="text-start">
                            <strong>รายงาน</strong>
                            <div class="small">ดูรายงานสรุป ยอดขาย และสินค้าคงเหลือ</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- สินค้าขายดี -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0"><i class="bi bi-star"></i> สินค้าขายดี <?php echo (count($topProducts) > 0) ? "(" . count($topProducts) . " อันดับ)" : ""; ?></h5>
            </div>
            <div class="card-body">
                <?php if (count($topProducts) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ชื่อสินค้า</th>
                                    <th class="text-end">จำนวนขาย</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $index => $product): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($product['prod_name']); ?></td>
                                        <td class="text-end"><?php echo number_format($product['total_qty']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> ยังไม่มีข้อมูลการขาย
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- การขายล่าสุด -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0"><i class="bi bi-clock-history"></i> การขายล่าสุด</h5>
            </div>
            <div class="card-body">
                <?php if (count($recentSales) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>เลขที่</th>
                                    <th>วันที่</th>
                                    <th class="text-end">จำนวนเงิน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSales as $sale): ?>
                                    <tr>
                                        <td>
                                            <a href="index.php?module=sales&action=view&id=<?php echo $sale['sale_id']; ?>">
                                                <?php echo htmlspecialchars($sale['sale_id']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo formatDateThai($sale['sale_date']); ?></td>
                                        <td class="text-end"><?php echo number_format($sale['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> ยังไม่มีข้อมูลการขาย
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- แบ่งส่วนการทำงานหลัก -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0"><i class="bi bi-grid-3x3-gap"></i> การทำงานหลักของระบบ</h5>
            </div>
            <div class="card-body">
                <div class="row text-center justify-content-center">
                    <div class="col-md-4 mb-3">
                        <div class="p-3 rounded shadow-sm bg-light">
                            <i class="bi bi-cart-check text-primary" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">การขาย</h5>
                            <div class="btn-group w-100">
                                <a href="index.php?module=sales&action=search" class="btn btn-outline-primary btn-sm">ค้นหา</a>
                                <a href="index.php?module=sales&action=add" class="btn btn-outline-primary btn-sm">เพิ่ม</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="p-3 rounded shadow-sm bg-light">
                            <i class="bi bi-box-seam text-success" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">สินค้า</h5>
                            <div class="btn-group w-100">
                                <a href="index.php?module=products&action=search" class="btn btn-outline-success btn-sm">ค้นหา</a>
                                <a href="index.php?module=products&action=add" class="btn btn-outline-success btn-sm">เพิ่ม</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="p-3 rounded shadow-sm bg-light">
                            <i class="bi bi-file-earmark-bar-graph text-warning" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">รายงาน</h5>
                            <div class="w-100">
                                <a href="index.php?module=reports&action=index" class="btn btn-outline-warning btn-sm w-100">ดูรายงาน</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>