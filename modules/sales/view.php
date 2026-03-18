<?php
/**
 * Sale Details View
 * หน้าแสดงรายละเอียดการขาย
 */

// ตรวจสอบรหัสการขาย
if (!isset($_GET['id'])) {
    // ถ้าไม่มีการระบุรหัสการขาย ให้กลับไปหน้าค้นหา
    header('Location: index.php?module=sales&action=search');
    exit;
}

$saleId = sanitizeInput($_GET['id']);
$sale = getSaleDetails($conn, $saleId);

// ถ้าไม่พบข้อมูลการขาย
if (!$sale) {
    echo showAlert('ไม่พบข้อมูลการขายที่ระบุ', 'danger');
    echo '<div class="text-center mt-3"><a href="index.php?module=sales&action=search" class="btn btn-primary">กลับไปหน้าค้นหา</a></div>';
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-text"></i> รายละเอียดการขาย</h2>
    <div>
        <a href="index.php?module=sales&action=search" class="btn btn-secondary me-2">
            <i class="bi bi-arrow-left"></i> กลับ
        </a>
        <a href="index.php?module=reports&action=receipt&id=<?php echo $saleId; ?>" class="btn btn-primary">
            <i class="bi bi-printer"></i> พิมพ์ใบเสร็จ
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h4 class="card-title mb-0">ข้อมูลการขาย</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th class="bg-light" width="40%">เลขที่การขาย</th>
                        <td><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light">วันที่</th>
                        <td><?php echo formatDateThai($sale['sale_date']); ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th class="bg-light" width="40%">รหัสลูกค้า</th>
                        <td><?php echo htmlspecialchars($sale['cus_id']); ?></td>
                    </tr>
                    <tr>
                        <th class="bg-light">ชื่อลูกค้า</th>
                        <td><?php echo htmlspecialchars($sale['cus_name']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php if ($sale['cus_id'] !== 'cus000'): // แสดงที่อยู่ลูกค้าถ้าไม่ใช่ลูกค้าทั่วไป ?>
        <div class="row mt-3">
            <div class="col-12">
                <table class="table table-bordered">
                    <tr>
                        <th class="bg-light" width="20%">ที่อยู่</th>
                        <td>
                            <?php 
                                $address = array_filter([
                                    $sale['cus_address01'],
                                    $sale['cus_address02'],
                                    $sale['cus_address03']
                                ]);
                                echo htmlspecialchars(implode(' ', $address));
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">เบอร์โทรศัพท์</th>
                        <td><?php echo htmlspecialchars($sale['phone']); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h4 class="card-title mb-0">รายการสินค้า</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="15%">รหัสสินค้า</th>
                        <th>ชื่อสินค้า</th>
                        <th class="text-center" width="10%">จำนวน</th>
                        <th class="text-end" width="15%">ราคาต่อหน่วย</th>
                        <th class="text-end" width="15%">รวมเงิน</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sale['items'])): ?>
                        <?php foreach ($sale['items'] as $index => $item): ?>
                            <tr>
                                <td><?php echo $item['items']; ?></td>
                                <td><?php echo htmlspecialchars($item['prod_id']); ?></td>
                                <td><?php echo htmlspecialchars($item['prod_name']); ?></td>
                                <td class="text-center"><?php echo $item['sale_amount']; ?> <?php echo htmlspecialchars($item['prod_unit']); ?></td>
                                <td class="text-end"><?php echo number_format($item['sale_price'], 2); ?> บาท</td>
                                <td class="text-end"><?php echo number_format($item['amount'], 2); ?> บาท</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">ไม่พบรายการสินค้า</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="5" class="text-end">ยอดรวมทั้งสิ้น:</th>
                        <th class="text-end"><?php echo number_format($sale['sale_total'], 2); ?> บาท</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>