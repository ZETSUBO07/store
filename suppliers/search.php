<?php
/**
 * Suppliers Search Module
 * โมดูลสำหรับการค้นหาข้อมูลผู้จัดจำหน่าย
 */

// ตรวจสอบคำค้นหา
$searchTerm = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// ค้นหาข้อมูลผู้จัดจำหน่าย
$suppliers = array();
$sql = "SELECT * FROM supplier";

if (!empty($searchTerm)) {
    $searchTerm = $conn->real_escape_string($searchTerm);
    $sql .= " WHERE sup_id LIKE '%$searchTerm%' 
            OR sup_desc LIKE '%$searchTerm%' 
            OR contact_person LIKE '%$searchTerm%'";
}

$sql .= " ORDER BY sup_id ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}
?>

<h2 class="mb-4">
    <i class="bi bi-truck"></i> จัดการข้อมูลผู้จัดจำหน่าย
</h2>

<!-- ส่วนค้นหา -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <form action="index.php" method="get" id="search-form" class="d-flex">
                    <input type="hidden" name="module" value="suppliers">
                    <input type="hidden" name="action" value="search">
                    
                    <input type="text" class="form-control me-2" name="search" id="search-input" 
                        placeholder="ค้นหาตามรหัส ชื่อผู้จัดจำหน่าย หรือชื่อผู้ติดต่อ" 
                        value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> ค้นหา
                    </button>
                </form>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="index.php?module=suppliers&action=add" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> เพิ่มผู้จัดจำหน่ายใหม่
                </a>
            </div>
        </div>
    </div>
</div>

<!-- แสดงผลการค้นหา -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>รหัสผู้จัดจำหน่าย</th>
                        <th>ชื่อผู้จัดจำหน่าย</th>
                        <th>ที่อยู่</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>ผู้ติดต่อ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($suppliers) > 0): ?>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($supplier['sup_id']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['sup_desc']); ?></td>
                                <td>
                                    <?php 
                                        $address = array_filter([
                                            $supplier['sup_address01'],
                                            $supplier['sup_address02'],
                                            $supplier['sup_address03']
                                        ]);
                                        echo htmlspecialchars(implode(' ', $address));
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                                <td class="text-center">
                                    <a href="index.php?module=suppliers&action=edit&id=<?php echo $supplier['sup_id']; ?>" 
                                       class="btn btn-sm btn-warning me-1">
                                        <i class="bi bi-pencil"></i> แก้ไข
                                    </a>
                                    <a href="index.php?module=suppliers&action=delete&id=<?php echo $supplier['sup_id']; ?>" 
                                       class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> ลบ
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">ไม่พบข้อมูลผู้จัดจำหน่าย</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- สรุปผลการค้นหา -->
        <div class="mt-3">
            <p>พบข้อมูลทั้งหมด <?php echo count($suppliers); ?> รายการ</p>
        </div>
    </div>
</div>