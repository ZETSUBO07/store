<?php
/**
 * Receipt View
 * หน้าแสดงใบเสร็จรับเงิน
 */

// ตรวจสอบว่ามีตัวแปร $sale ที่ถูกต้อง
if (!isset($sale) || !is_array($sale)) {
    echo showAlert('ไม่พบข้อมูลการขาย', 'danger');
    echo '<div class="text-center mt-3"><a href="index.php?module=sales&action=search" class="btn btn-primary">กลับไปหน้าค้นหา</a></div>';
    exit;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-receipt"></i> ใบเสร็จรับเงิน</h2>
        <div>
            <button class="btn btn-secondary me-2" onclick="window.history.back()">
                <i class="bi bi-arrow-left"></i> กลับ
            </button>
            <button class="btn btn-primary" id="print-receipt-btn">
                <i class="bi bi-printer"></i> พิมพ์ใบเสร็จ
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h4>ใบเสร็จรับเงิน</h4>
                    <h5>ร้านขายเครื่องเขียน</h5>
                    <p>เลขที่การขาย: <?php echo htmlspecialchars($sale['sale_id']); ?></p>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-6">
                    <strong>ข้อมูลลูกค้า:</strong><br>
                    ชื่อ: <?php echo htmlspecialchars($sale['cus_name']); ?><br>
                    <?php if ($sale['cus_id'] !== 'cus000'): ?>
                        ที่อยู่: 
                        <?php 
                            $address = array_filter([
                                $sale['cus_address01'],
                                $sale['cus_address02'],
                                $sale['cus_address03']
                            ]);
                            echo htmlspecialchars(implode(' ', $address));
                        ?><br>
                        เบอร์โทรศัพท์: <?php echo htmlspecialchars($sale['phone']); ?>
                    <?php endif; ?>
                </div>
                <div class="col-6 text-end">
                    <strong>วันที่:</strong> <?php echo formatDateThai($sale['sale_date']); ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="50%">รายการสินค้า</th>
                            <th class="text-center" width="10%">จำนวน</th>
                            <th class="text-end" width="15%">ราคาต่อหน่วย</th>
                            <th class="text-end" width="20%">รวมเงิน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sale['items'])): ?>
                            <?php foreach ($sale['items'] as $index => $item): ?>
                                <tr>
                                    <td><?php echo $item['items']; ?></td>
                                    <td><?php echo htmlspecialchars($item['prod_name']); ?></td>
                                    <td class="text-center"><?php echo $item['sale_amount']; ?> <?php echo htmlspecialchars($item['prod_unit']); ?></td>
                                    <td class="text-end"><?php echo number_format($item['sale_price'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($item['amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">ยอดรวมทั้งสิ้น:</th>
                            <th class="text-end"><?php echo number_format($sale['sale_total'], 2); ?> บาท</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row mt-5">
                <div class="col-6">
                    <p>ลงชื่อ................................................ผู้รับเงิน</p>
                </div>
                <div class="col-6 text-end">
                    <p>ลงชื่อ................................................ผู้รับสินค้า</p>
                </div>
            </div>