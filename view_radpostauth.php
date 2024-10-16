<?php
include 'header.php';  // Include header ที่มี sidebar และ navbar
include 'connect_db/connect.php';
include 'session.php';

// จำนวนรายการที่ต้องการแสดงผลต่อหน้า
$records_per_page = 10;

// ตรวจสอบว่ามีการกำหนดค่าหน้าปัจจุบันหรือไม่ ถ้าไม่กำหนดให้ค่าเป็น 1
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $current_page = (int) $_GET['page'];
} else {
    $current_page = 1;
}

// คำนวณการเริ่มต้นของการดึงข้อมูล
$start_from = ($current_page - 1) * $records_per_page;

// ดึงข้อมูลจากตาราง radpostauth โดยใช้การแบ่งหน้า
$sql = "SELECT * FROM radpostauth ORDER BY authdate DESC LIMIT $start_from, $records_per_page";
$result = $conn->query($sql);

// ดึงจำนวนทั้งหมดของรายการ
$total_records_sql = "SELECT COUNT(*) AS total FROM radpostauth";
$total_records_result = $conn->query($total_records_sql);
$total_records_row = $total_records_result->fetch_assoc();
$total_records = $total_records_row['total'];

// คำนวณจำนวนหน้าทั้งหมด
$total_pages = ceil($total_records / $records_per_page);

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการตรวจสอบสิทธิ์</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-left: 270px;
            margin-top: 80px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            max-width: calc(100% - 290px);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
        }

        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: flex-start;
        }

        .pagination a {
            margin: 0 5px;
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }

        .pagination a:hover {
            background-color: #0056b3;
        }

        .pagination a.disabled {
            background-color: #cccccc;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ประวัติการตรวจสอบสิทธิ์</h1>
        <table>
            <tr>
                <th>Username</th>
                <th>Reply</th>
                <th>วันที่และเวลา</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['reply']; ?></td>
                <td><?php echo $row['authdate']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <!-- แสดงปุ่ม pagination -->
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>">หน้าก่อนหน้า</a>
            <?php else: ?>
                <a class="disabled">หน้าก่อนหน้า</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" <?php if ($current_page == $i) echo 'class="disabled"'; ?>><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>">หน้าถัดไป</a>
            <?php else: ?>
                <a class="disabled">หน้าถัดไป</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
