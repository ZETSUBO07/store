<?php
/**
 * Product Add Module
 * โมดูลสำหรับเพิ่มสินค้าใหม่
 */

// ดึงข้อมูลหมวดหมู่
$categories = array();
$catSql = "SELECT * FROM catagory ORDER BY cat_desc ASC";
$catResult = $conn->query($catSql);
if ($catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// ค้นหารหัสสินค้าล่าสุดและสร้างรหัสใหม่
$sql = "SELECT MAX(CAST(SUBSTRING(prod_id, 4) AS UNSIGNED)) as max_id FROM product WHERE prod_id LIKE 'PRD%'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$nextId = 1;
if ($row['max_id']) {
    $nextId = $row['max_id'] + 1;
}
$newProductId = 'PRD' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = sanitizeInput($_POST['prod_id']);
    $productName = sanitizeInput($_POST['prod_name']);
    $categoryInput = sanitizeInput($_POST['category_input']);
    $productCost = floatval($_POST['prod_cost']);
    $productPrice = floatval($_POST['prod_price']);
    $productAmount = intval($_POST['prod_amount']);
    $productUnit = sanitizeInput($_POST['prod_unit']);
    
    // ตรวจสอบข้อมูล
    $errors = array();
    
    if (empty($productName)) {
        $errors[] = 'กรุณาระบุชื่อสินค้า';
    }
    
    if (empty($categoryInput)) {
        $errors[] = 'กรุณาระบุหมวดหมู่';
    }
    
    if ($productCost <= 0) {
        $errors[] = 'กรุณาระบุราคาทุนให้ถูกต้อง';
    }
    
    if ($productPrice <= 0) {
        $errors[] = 'กรุณาระบุราคาขายให้ถูกต้อง';
    }
    
    if ($productAmount < 0) {
        $errors[] = 'กรุณาระบุจำนวนสินค้าให้ถูกต้อง';
    }
    
    if (empty($productUnit)) {
        $errors[] = 'กรุณาระบุหน่วยนับ';
    }
    
    // ถ้าไม่มีข้อผิดพลาด
    if (empty($errors)) {
        // หาหรือสร้างหมวดหมู่ใหม่
        $catCheckSql = "SELECT cat_code FROM catagory WHERE cat_desc = ?";
        $stmtCat = $conn->prepare($catCheckSql);
        $stmtCat->bind_param('s', $categoryInput);
        $stmtCat->execute();
        $catCheckResult = $stmtCat->get_result();
        
        if ($catCheckResult->num_rows > 0) {
            $catRow = $catCheckResult->fetch_assoc();
            $categoryCode = $catRow['cat_code'];
        } else {
            $catMaxSql = "SELECT MAX(CAST(SUBSTRING(cat_code, 4) AS UNSIGNED)) as max_id FROM catagory WHERE cat_code LIKE 'CAT%'";
            $catMaxResult = $conn->query($catMaxSql);
            $catMaxRow = $catMaxResult->fetch_assoc();
            $nextCatId = 1;
            if ($catMaxRow['max_id']) {
                $nextCatId = $catMaxRow['max_id'] + 1;
            }
            $categoryCode = 'CAT' . str_pad($nextCatId, 3, '0', STR_PAD_LEFT);
            
            $catInsertSql = "INSERT INTO catagory (cat_code, cat_desc) VALUES (?, ?)";
            $stmtNewCat = $conn->prepare($catInsertSql);
            $stmtNewCat->bind_param('ss', $categoryCode, $categoryInput);
            if (!$stmtNewCat->execute()) {
                $errors[] = 'ไม่สามารถสร้างหมวดหมู่ใหม่ได้: ' . $conn->error;
            }
        }
        
        if (empty($errors)) {
            // ตรวจสอบว่ามีรหัสสินค้านี้อยู่แล้วหรือไม่
            $checkSql = "SELECT * FROM product WHERE prod_id = '$productId'";
            $checkResult = $conn->query($checkSql);
            
            if ($checkResult->num_rows > 0) {
                $errors[] = 'รหัสสินค้านี้มีอยู่ในระบบแล้ว';
            } else {
                // เพิ่มสินค้าใหม่
                $insertSql = "INSERT INTO product (prod_id, cat_code, prod_name, prod_cost, prod_price, prod_amount, prod_unit) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insertSql);
                $stmt->bind_param('sssddis', $productId, $categoryCode, $productName, $productCost, $productPrice, $productAmount, $productUnit);
                
                if ($stmt->execute()) {
                    // บันทึกสำเร็จ
                    $_SESSION['success'] = 'เพิ่มสินค้าใหม่เรียบร้อยแล้ว';
                    header('Location: index.php?module=products&action=search');
                    exit;
                } else {
                    $errors[] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $conn->error;
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> เพิ่มสินค้าใหม่</h2>
    <a href="index.php?module=products&action=search" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> กลับไปหน้าค้นหา
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="index.php?module=products&action=add" method="post">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="prod_id" class="form-label">รหัสสินค้า</label>
                    <input type="text" class="form-control" id="prod_id" name="prod_id" value="<?php echo $newProductId; ?>" readonly>
                </div>
                <div class="col-md-8">
                    <label for="category_input" class="form-label">หมวดหมู่ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="category_input" name="category_input" list="category_list" placeholder="เลือกหรือพิมพ์หมวดหมู่ใหม่" required>
                    <datalist id="category_list">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['cat_desc']); ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="prod_name" class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="prod_name" name="prod_name" required>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="prod_cost" class="form-label">ราคาทุน <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" id="prod_cost" name="prod_cost" required>
                </div>
                <div class="col-md-6">
                    <label for="prod_price" class="form-label">ราคาขาย <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" id="prod_price" name="prod_price" required>
                </div>
            </div>
            
            <!-- ย้ายฟิลด์จำนวนคงเหลือและหน่วยนับมาไว้ด้านล่าง -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="prod_unit" class="form-label">หน่วยนับ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="prod_unit" name="prod_unit" required>
                </div>
                <div class="col-md-6">
                    <label for="prod_amount" class="form-label">จำนวนคงเหลือ</label>
                    <input type="number" min="0" class="form-control" id="prod_amount" name="prod_amount" value="0">
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> บันทึกข้อมูล
                </button>
            </div>
        </form>
    </div>
</div>