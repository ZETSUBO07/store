<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once('../../config/db_connect.php');
require_once('../../includes/functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cus_name = sanitizeInput($_POST['cus_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    if (empty($cus_name)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อลูกค้า']);
        exit;
    }
    
    $cus_id = generateNewCustomerId($conn);
    
    $sql = "INSERT INTO customer (cus_id, cus_name, phone) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $cus_id, $cus_name, $phone);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'customer' => [
                'cus_id' => $cus_id,
                'cus_name' => htmlspecialchars($cus_name)
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $conn->error]);
    }
}
?>
