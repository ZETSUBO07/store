<?php
/**
 * Database Connection
 * ไฟล์สำหรับเชื่อมต่อกับฐานข้อมูล MySQL
 */
$dbhost = "127.0.0.1";
$dbuser = "root";
$dbpass = "";
$dbname = "stationary";

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8"); // ใช้ mysqli_set_charset แทน mysqli_query
?>