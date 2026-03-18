<?php
/**
 * Reports Index Module
 * โมดูลสำหรับเข้าถึงรายงานต่างๆ ของระบบ
 */

// กำหนดค่าเริ่มต้นสำหรับวันที่
$startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : date('Y-m-d');
$endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : date('Y-m-d');
$reportGenerated = false;

// ตรวจสอบการคำนวณยอดขายตามช่วงวันที่
if (isset($_GET['generate_report']) && $_GET['generate_report'] == '1') {
    $reportGenerated = true;
    $salesReport = getSalesReport($conn, $startDate, $endDate);
}
?>

<h2 class="mb-4">
    <i class="bi bi-file-earmark-text"></i> รายงาน
</h2>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">ใบเสร็จรับเงิน</h4>
            </div>
            <div class="card-body">
                <p>พิมพ์ใบเสร็จรับเงินตามเลขที่การขาย</p>
                
                <form action="index.php" method="get" class="mb-3">
                    <input type="hidden" name="module" value="reports">
                    <input type="hidden" name="action" value="receipt">
                    
                    <div class="input-group">
                        <input type="text" class="form-control" name="id" placeholder="ระบุเลขที่การขาย เช่น SL0001" required>
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-printer"></i> พิมพ์ใบเสร็จ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">รายงานยอดขายตามช่วงเวลา</h4>
            </div>
            <div class="card-body">
                <p>คำนวณยอดขายรวมตามช่วงวันที่</p>
                
                <form action="index.php" method="get" class="row g-3 mb-3">
                    <input type="hidden" name="module" value="reports">
                    <input type="hidden" name="action" value="index">
                    <input type="hidden" name="generate_report" value="1">
                    
                    <div class="col-md-5">
                        <label for="start-date" class="form-label">วันที่เริ่มต้น</label>
                        <input type="date" class="form-control" id="start-date" name="start_date" value="<?php echo $startDate; ?>" required>
                    </div>
                    
                    <div class="col-md-5">
                        <label for="end-date" class="form-label">วันที่สิ้นสุด</label>
                        <input type="date" class="form-control" id="end-date" name="end_date" value="<?php echo $endDate; ?>" required>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="bi bi-calculator"></i> คำนวณ
                        </button>
                    </div>
                </form>
                
                <?php if ($reportGenerated): ?>
                    <div class="alert alert-info mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">ผลการคำนวณยอดขาย</h5>
                                <p class="mb-1">ช่วงวันที่: <?php echo formatDateThai($salesReport['start_date']); ?> ถึง <?php echo formatDateThai($salesReport['end_date']); ?></p>
                                <p class="mb-1">จำนวนรายการ: <?php echo $salesReport['total_transactions']; ?> รายการ</p>
                            </div>
                            <div>
                                <h4 class="text-primary mb-0"><?php echo number_format($salesReport['total_amount'], 2); ?> บาท</h4>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (count($salesReport['sales']) > 0): ?>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-striped table-hover">
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
                                            <td><?php echo $sale['sale_id']; ?></td>
                                            <td><?php echo formatDateThai($sale['sale_date']); ?></td>
                                            <td><?php echo $sale['cus_name']; ?></td>
                                            <td class="text-end"><?php echo number_format($sale['sale_total'], 2); ?> บาท</td>
                                            <td class="text-center">
                                                <a href="index.php?module=sales&action=view&id=<?php echo $sale['sale_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">รายงานเพิ่มเติม</h4>
            </div>
            <div class="card-body">
                <p>รายงานวิเคราะห์เพิ่มเติม</p>
                
                <div class="d-grid gap-2">
                    <a href="index.php?module=reports&action=sales_summary" class="btn btn-outline-primary">
                        <i class="bi bi-bar-chart"></i> รายงานสรุปยอดขาย
                    </a>
                    <a href="index.php?module=reports&action=inventory" class="btn btn-outline-success">
                        <i class="bi bi-box-seam"></i> รายงานสินค้าคงเหลือ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($reportGenerated && count($salesReport['sales']) > 0): ?>
    <div class="mt-4 text-center">
        <a href="index.php?module=reports&action=sales_summary&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" class="btn btn-primary btn-lg">
            <i class="bi bi-graph-up"></i> ดูรายงานสรุปยอดขายแบบละเอียด
        </a>
    </div>
<?php endif; ?>