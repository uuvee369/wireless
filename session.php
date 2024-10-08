<?php
session_start();

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือยัง
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit;
}
?>
