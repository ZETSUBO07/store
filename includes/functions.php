<?php
/**
 * Utility Functions
 * ไฟล์รวบรวมฟังก์ชั่นที่ใช้งานร่วมกันในระบบ
 */

/**
 * ฟังก์ชั่นสำหรับดึงข้อมูลลูกค้าทั้งหมด
 * 
 * @param mysqli $conn การเชื่อมต่อฐานข้อมูล
 * @return array ข้อมูลลูกค้าทั้งหมด
 */
function getAllCustomers($conn) {
    $customers = array();
    $sql = "SELECT * FROM customer ORDER BY cus_name ASC";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    
    return $customers;
}

/**
 * ฟังก์ชั่นสำหรับดึงข้อมูลสินค้าทั้งหมด
 * 
 * @param mysqli $conn การเชื่อมต่อฐานข้อมูล
 * @return array ข้อมูลสินค้าทั้งหมด
 */
function getAllProducts($conn) {
    $products = array();
    $sql = "SELECT p.*, c.cat_desc FROM product p 
            LEFT JOIN catagory c ON p.cat_code = c.cat_code 
            WHERE p.prod_amount > 0 
            ORDER BY p.prod_name ASC";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    return $products;
}

/**
 * ฟังก์ชั่นสำหรับดึงข้อมูลการขายตามเงื่อนไขการค้นหา
 * 
 * @param mysqli $conn การเชื่อมต่อฐานข้อมูล
 * @param string $searchTerm คำค้นหา (เลขที่ขาย, ชื่อลูกค้า, รหัสลูกค้า)
 * @return array ข้อมูลการขายที่ตรงกับเงื่อนไขการค้นหา
 */
function searchSales($conn, $searchTerm = '') {
    $sales = array();
    
    $sql = "SELECT s.*, c.cus_name 
            FROM sale s
            LEFT JOIN customer c ON s.cus_id = c.cus_id";
    
    if (!empty($searchTerm)) {
        $searchTerm = $conn->real_escape_string($searchTerm);
        $sql .= " WHERE s.sale_id LIKE '%$searchTerm%' 
                OR c.cus_name LIKE '%$searchTerm%'
                OR s.cus_id LIKE '%$searchTerm%'";
    }
    
    $sql .= " ORDER BY s.sale_date DESC";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // คำนวณยอดรวมของการขายแต่ละรายการ
            $saleId = $row['sale_id'];
            $totalSql = "SELECT SUM(sale_price * sale_amount) as total 
                         FROM sale_detail 
                         WHERE sale_id = '$saleId'";
            $totalResult = $conn->query($totalSql);
            $totalRow = $totalResult->fetch_assoc();
            
            $row['sale_total'] = $totalRow['total'];
            $sales[] = $row;
        }
    }
    
    return $sales;
}

/**
 * ฟังก์ชั่นสำหรับดึงข้อมูลรายละเอียดการขาย
 * 
 * @param mysqli $conn การเชื่อมต่อฐานข้อมูล
 * @param string $saleId เลขที่การขาย
 * @return array ข้อมูลรายละเอียดการขาย
 */
function getSaleDetails($conn, $saleId) {
    $saleId = $conn->real_escape_string($saleId);
    
    // ดึงข้อมูลการขาย
    $sql = "SELECT s.*, c.cus_name, c.cus_address01, c.cus_address02, c.cus_address03, c.phone
            FROM sale s
            LEFT JOIN customer c ON s.cus_id = c.cus_id
            WHERE s.sale_id = '$saleId'";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows == 0) {
        return null;
    }
    
    $sale = $result->fetch_assoc();
    
    // ดึงรายการสินค้าในการขาย
    $sql = "SELECT sd.*, p.prod_name, p.prod_unit
            FROM sale_detail sd
            LEFT JOIN product p ON sd.prod_id = p.prod_id
            WHERE sd.sale_id = '$saleId'";
    
    $result = $conn->query($sql);
    $items = array();
    $totalAmount = 0;
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $amount = $row['sale_price'] * $row['sale_amount'];
            $row['amount'] = $amount;
            $totalAmount += $amount;
            $items[] = $row;
        }
    }
    
    $sale['items'] = $items;
    $sale['sale_total'] = $totalAmount;
    
    return $sale;
}

/**
 * ฟังก์ชั่นสำหรับคำนวณยอดขายตามช่วงวันที่
 * 
 * @param mysqli $conn การเชื่อมต่อฐานข้อมูล
 * @param string $startDate วันที่เริ่มต้น (Y-m-d)
 * @param string $endDate วันที่สิ้นสุด (Y-m-d)
 * @return array ข้อมูลยอดขายรวมและรายการขาย
 */
function getSalesReport($conn, $startDate, $endDate) {
    $startDate = $conn->real_escape_string($startDate);
    $endDate = $conn->real_escape_string($endDate);
    
    $sql = "SELECT s.*, c.cus_name 
            FROM sale s
            LEFT JOIN customer c ON s.cus_id = c.cus_id
            WHERE s.sale_date BETWEEN '$startDate' AND '$endDate'
            ORDER BY s.sale_date ASC";
    
    $result = $conn->query($sql);
    $sales = array();
    $totalAmount = 0;
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $saleId = $row['sale_id'];
            
            // คำนวณยอดรวมของการขายแต่ละรายการ
            $totalSql = "SELECT SUM(sale_price * sale_amount) as total 
                         FROM sale_detail 
                         WHERE sale_id = '$saleId'";
            $totalResult = $conn->query($totalSql);
            $totalRow = $totalResult->fetch_assoc();
            
            $row['sale_total'] = $totalRow['total'];
            $sales[] = $row;
            $totalAmount += $totalRow['total'];
        }
    }
    
    return array(
        'sales' => $sales,
        'total_amount' => $totalAmount,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'total_transactions' => count($sales)
    );
}

/**
 * ฟังก์ชั่นสำหรับสร้างรหัสการขายใหม่
 * 
 * @param mysqli $conn การเชื่อมต่อฐานข้อมูล
 * @return string รหัสการขายใหม่
 */
function generateNewSaleId($conn) {
    $sql = "SELECT MAX(CAST(SUBSTRING(sale_id, 3) AS UNSIGNED)) as max_id FROM sale WHERE sale_id LIKE 'SL%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    $nextId = 1;
    if ($row['max_id']) {
        $nextId = $row['max_id'] + 1;
    }
    
    return 'SL' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
}

/**
 * ฟังก์ชั่นสำหรับตรวจสอบความถูกต้องของข้อมูล
 * 
 * @param string $data ข้อมูลที่ต้องการตรวจสอบ
 * @return string ข้อมูลที่ผ่านการตรวจสอบแล้ว
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * ฟังก์ชั่นสำหรับแปลงวันที่จากรูปแบบ Y-m-d เป็น d/m/Y
 * 
 * @param string $date วันที่ในรูปแบบ Y-m-d
 * @return string วันที่ในรูปแบบ d/m/Y
 */
function formatDateThai($date) {
    $dateObj = new DateTime($date);
    return $dateObj->format('d/m/Y');
}

/**
 * ฟังก์ชั่นสำหรับแสดงข้อความแจ้งเตือน
 * 
 * @param string $message ข้อความ
 * @param string $type ประเภทข้อความ (success, danger, warning, info)
 * @return string HTML สำหรับแสดงข้อความแจ้งเตือน
 */
function showAlert($message, $type = 'info') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * ฟังก์ชั่นสำหรับคำนวณยอดขายรวม
 * 
 * @param mysqli $conn การเชื่อมต่อฐานข้อมูล
 * @param string $saleId เลขที่การขาย
 * @return float ยอดขายรวม
 */
function calculateSaleTotal($conn, $saleId) {
    $saleId = $conn->real_escape_string($saleId);
    
    $sql = "SELECT SUM(sale_price * sale_amount) as total 
            FROM sale_detail 
            WHERE sale_id = '$saleId'";
    
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    return $row['total'] ?? 0;
}

/**
 * ฟังก์ชั่นสำหรับสร้างรหัสลูกค้าใหม่
 * 
 * @param mysqli $conn การเชื่อมต่อฐานข้อมูล
 * @return string รหัสลูกค้าใหม่
 */
function generateNewCustomerId($conn) {
    $sql = "SELECT MAX(CAST(SUBSTRING(cus_id, 4) AS UNSIGNED)) as max_id FROM customer WHERE cus_id LIKE 'cus%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    $nextId = 1;
    if ($row['max_id']) {
        $nextId = $row['max_id'] + 1;
    }
    
    return 'cus' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
}