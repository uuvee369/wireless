<?php
session_start(); // เริ่มต้นเซสชัน

// ล้างข้อมูลเซสชันทั้งหมด
session_unset();

// ทำลายเซสชัน
session_destroy();

// เปลี่ยนเส้นทางไปยังหน้า login.php หรือหน้าอื่นๆ
header("Location: admin_login.php");
exit;
?>
