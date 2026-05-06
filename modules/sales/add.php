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
                        <option value="new_customer" class="text-primary fw-bold">+ เพิ่มลูกค้าใหม่</option>
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

<!-- Modal เพิ่มลูกค้าใหม่ -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCustomerModalLabel"><i class="bi bi-person-plus"></i> เพิ่มลูกค้าใหม่</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="new-customer-form">
          <div class="mb-3">
            <label for="new-cus-name" class="form-label">ชื่อลูกค้า <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="new-cus-name" required>
          </div>
          <div class="mb-3">
            <label for="new-cus-phone" class="form-label">เบอร์โทรศัพท์</label>
            <input type="text" class="form-control" id="new-cus-phone">
          </div>
        </form>
        <div id="new-customer-alert" class="d-none alert alert-danger"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-primary" id="save-customer-btn">บันทึก</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const customerSelect = document.getElementById('customer-id');
    const addCustomerModal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
    
    // ตรวจจับการเปลี่ยนค่าใน dropdown
    customerSelect.addEventListener('change', function() {
        if (this.value === 'new_customer') {
            document.getElementById('new-customer-form').reset();
            document.getElementById('new-customer-alert').classList.add('d-none');
            addCustomerModal.show();
        }
    });

    // เมื่อปิด modal ให้กลับไปที่ค่าว่างถ้าไม่ได้เพิ่ม
    document.getElementById('addCustomerModal').addEventListener('hidden.bs.modal', function () {
        if (customerSelect.value === 'new_customer') {
            customerSelect.value = '';
        }
    });

    // กดบันทึกลูกค้า
    document.getElementById('save-customer-btn').addEventListener('click', function() {
        const cusName = document.getElementById('new-cus-name').value.trim();
        const cusPhone = document.getElementById('new-cus-phone').value.trim();
        const alertBox = document.getElementById('new-customer-alert');
        
        if (!cusName) {
            alertBox.textContent = 'กรุณากรอกชื่อลูกค้า';
            alertBox.classList.remove('d-none');
            return;
        }

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังบันทึก...';

        const formData = new URLSearchParams();
        formData.append('cus_name', cusName);
        formData.append('phone', cusPhone);

        fetch('modules/sales/ajax_add_customer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // สร้าง option ใหม่
                const newOption = new Option(data.customer.cus_name, data.customer.cus_id);
                // แทรกให้เป็นลูกค้าใหม่ที่เลือกล่าสุด
                customerSelect.add(newOption, customerSelect.options[2]);
                customerSelect.value = data.customer.cus_id;
                addCustomerModal.hide();
            } else {
                alertBox.textContent = data.message || 'เกิดข้อผิดพลาด';
                alertBox.classList.remove('d-none');
            }
        })
        .catch(err => {
            alertBox.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
            alertBox.classList.remove('d-none');
        })
        .finally(() => {
            this.disabled = false;
            this.textContent = 'บันทึก';
        });
    });
});
</script>
