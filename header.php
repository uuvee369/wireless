<?php
include 'connect_db/connect.php';
include 'session.php';

$currentPage = basename($_SERVER['PHP_SELF']); // ดึงชื่อไฟล์ของหน้าปัจจุบัน
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=no" />
    <title>RADIUS Server</title>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background-color: #ffffff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dddddd;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            position: fixed;
            top: 0;
            z-index: 1000;
            box-sizing: border-box;
            overflow: hidden;
        }

        .navbar .brand-img {
            height: 40px;
            margin-right: 20px;
        }

        .navbar .logout-btn {
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            white-space: nowrap;
            box-sizing: border-box;
            transition: background-color 0.5s ease;
        }

        .navbar .logout-btn:hover {
            background-color: #0056b3;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #ffffff;
            height: 100vh;
            box-shadow: 2px 0 6px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 60px;
            left: 0;
            padding-top: 20px;
            border-right: 1px solid #dddddd;
            z-index: 999;
        }

        .sidebar a {
            display: block;
            color: #333;
            padding: 15px 20px;
            text-decoration: none;
            font-size: 18px;
            transition: background-color 0.5s ease, color 0.5s ease;
        }

        .sidebar a.active {
            background-color: gray;
            color: white;
            transition: background-color 0.5s ease, color 0.5s ease;
        }

        .sidebar a:hover {
            background-color: #e0ecff;
            color: #007bff;
            transition: 0.5s;
        }

        /* Main content styles */
        .container {
            margin-left: 270px;
            margin-top: 80px;
            max-width: calc(100% - 290px);
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #ffffff;
            color: #555;
            border-top: 1px solid #dddddd;
            box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            margin-left: 270px;
            margin-top: 20px;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
        }

        .footer a:hover {
            color: #0056b3;
        }

        /* Media queries for responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .container {
                margin-left: 0;
                margin-top: 120px; /* Adjust for the navbar height on small screens */
                max-width: 100%;
            }

            .footer {
                margin-left: 0;
            }

            .navbar .logout-btn {
                padding: 6px 12px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="hk-wrapper hk-vertical-nav">
        <!-- Navbar -->
        <nav class="navbar">
            <a class="navbar-brand">
                <img class="brand-img" src="img/logo.png" alt="brand" />
            </a>
            <button class="logout-btn" onclick="window.location.href='logout.php'">ออกจากระบบ</button>
        </nav>

        <!-- Sidebar -->
        <div class="sidebar">
            <a href="index.php" class="<?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">แดชบอร์ด</a>
            <a href="add_nas.php" class="<?php echo $currentPage == 'add_nas.php' ? 'active' : ''; ?>">เพิ่ม Nas Client</a>
            <a href="add_user.php" class="<?php echo $currentPage == 'add_user.php' ? 'active' : ''; ?>">เพิ่มข้อมูลผู้ใช้</a>
            <a href="import_user.php" class="<?php echo $currentPage == 'import_user.php' ? 'active' : ''; ?>">นำเข้าข้อมูลผู้ใช้</a>
            <a href="view_radpostauth.php" class="<?php echo $currentPage == 'view_radpostauth.php' ? 'active' : ''; ?>">ดูประวัติการตรวจสอบสิทธิ์</a>
            <a href="add_group.php" class="<?php echo $currentPage == 'user_group.php' ? 'active' : ''; ?>">เพิ่มกลุ่มผู้ใช้</a>
            <a href="active_users.php" class="<?php echo $currentPage == 'active_users.php' ? 'active' : ''; ?>">ผู้ใช้ที่กำลังใช้งาน</a>
        </div>
    </div>
</body>

</html>
