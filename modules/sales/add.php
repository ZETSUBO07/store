<?php
/**
 * Add Sale Module
 * โมดูลสำหรับเพิ่มข้อมูลการขาย
 */

// ดึงข้อมูลลูกค้าทั้งหมด
$customers = getAllCustomers($conn);

// ดึงข้อมูลสินค้าทั้งหมด
$products = getAllProducts($conn);

// สร้างรหัสการขายใหม่
$newSaleId = generateNewSaleId($conn);

// กำหนดวันที่ขายเป็นวันที่ปัจจุบัน
$saleDate = date('Y-m-d');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle"></i> เพิ่มข้อมูลการขาย</h2>
    <a href="index.php?module=sales&action=search" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> กลับไปหน้าค้นหา
    </a>
</div>

<form action="index.php?module=sales&action=process_sale" method="post" id="add-sale-form">
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h4 class="card-title mb-0">ข้อมูลการขาย</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="sale-id" class="form-label">เลขที่การขาย</label>
                    <input type="text" class="form-control" id="sale-id" name="sale_id" value="<?php echo $newSaleId; ?>" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="sale-date" class="form-label">วันที่</label>
                    <input type="date" class="form-control" id="sale-date" name="sale_date" value="<?php echo $saleDate; ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="customer-id" class="form-label">ลูกค้า</label>
                    <select class="form-select" id="customer-id" name="customer_id" required>
                        <option value="">-- เลือกลูกค้า --</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['cus_id']; ?>">
                                <?php echo htmlspecialchars($customer['cus_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-light">
            <h4 class="card-title mb-0">รายการสินค้า</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-5">
                    <select class="form-select" id="product-select">
                        <option value="">-- เลือกสินค้า --</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['prod_id']; ?>" 
                                data-price="<?php echo $product['prod_price']; ?>"
                                data-unit="<?php echo $product['prod_unit']; ?>"
                                data-stock="<?php echo $product['prod_amount']; ?>">
                                <?php echo htmlspecialchars($product['prod_name']); ?> 
                                (ราคา: <?php echo number_format($product['prod_price'], 2); ?> บาท, 
                                คงเหลือ: <?php echo $product['prod_amount']; ?> <?php echo $product['prod_unit']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" id="product-qty" placeholder="จำนวน" min="1" value="1">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" id="product-price" placeholder="ราคา" readonly>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-success w-100" id="add-item-btn">
                        <i class="bi bi-plus-circle"></i> เพิ่มสินค้า
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover" id="sale-items-table">
                    <thead class="table-light">
                        <tr>
                            <th>รหัสสินค้า</th>
                            <th>ชื่อสินค้า</th>
                            <th class="text-center">จำนวน</th>
                            <th class="text-end">ราคาต่อหน่วย</th>
                            <th class="text-end">รวมเงิน</th>
                            <th class="text-center">ลบ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- รายการสินค้าจะถูกเพิ่มที่นี่ด้วย JavaScript -->
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <th colspan="4" class="text-end">ยอดรวมทั้งสิ้น:</th>
                            <th class="text-end">
                                <span id="sale-total">0.00</span> บาท
                                <input type="hidden" name="sale_total" id="sale-total-input" value="0">
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle"></i> เพิ่มรายการสินค้าอย่างน้อย 1 รายการ
            </div>
        </div>
    </div>

    <div class="text-center mb-4">
        <button type="submit" class="btn btn-primary btn-lg" id="save-sale-btn">
            <i class="bi bi-save"></i> บันทึกการขาย
        </button>
    </div>
</form>
