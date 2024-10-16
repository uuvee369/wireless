<?php
include 'header.php';  // Include header ที่มี sidebar และ navbar
include 'connect_db/connect.php';
include 'session.php';

// กำหนดจำนวนรายการที่จะแสดงต่อหน้า
$records_per_page = 6;

// ตรวจสอบหน้าปัจจุบัน หากไม่มีการระบุ ให้แสดงหน้าแรก
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $current_page = (int) $_GET['page'];
} else {
    $current_page = 1;
}

// คำนวณตำแหน่งเริ่มต้นในการดึงข้อมูล
$start_from = ($current_page - 1) * $records_per_page;

// ฟังก์ชันการลบ NAS
if (isset($_GET['delete_id'])) {
    $delete_id = $conn->real_escape_string($_GET['delete_id']);
    $sql_delete = "DELETE FROM nas WHERE id = '$delete_id'";
    
    if ($conn->query($sql_delete) === TRUE) {
        
    } else {
        
    }
}

// ฟังก์ชันการเพิ่ม NAS
if (isset($_POST['add_nas'])) {
    $nasname = $conn->real_escape_string($_POST['nasname']);
    $shortname = $conn->real_escape_string($_POST['shortname']);
    $secret = $conn->real_escape_string($_POST['secret']);
    $description = $conn->real_escape_string($_POST['description']);

    $sql_insert = "INSERT INTO nas (nasname, shortname, secret, description) VALUES ('$nasname', '$shortname', '$secret', '$description')";

    if ($conn->query($sql_insert) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        
    }
}

// ฟังก์ชันการค้นหา NAS
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $conn->real_escape_string($_GET['search_query']);
    $sql = "SELECT * FROM nas WHERE nasname LIKE '%$search_query%' OR shortname LIKE '%$search_query%' LIMIT $start_from, $records_per_page";
} else {
    $sql = "SELECT * FROM nas LIMIT $start_from, $records_per_page";
}

$result = $conn->query($sql);

// ดึงจำนวนทั้งหมดของ NAS เพื่อใช้ในการคำนวณจำนวนหน้าทั้งหมด
$total_records_sql = "SELECT COUNT(*) AS total FROM nas";
$total_records_result = $conn->query($total_records_sql);
$total_records = $total_records_result->fetch_assoc()['total'];

// คำนวณจำนวนหน้าทั้งหมด
$total_pages = ceil($total_records / $records_per_page);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=no" />
    <title>Nas Client</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-left: 270px;
            margin-top: 80px;
            max-width: calc(100% - 290px);
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .form-section, .nas-list-section {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        label {
            font-size: 16px;
            font-weight: bold;
        }

        .form-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .form-row > div {
            flex: 1;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .btn {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            display: block;
            width: 100%;
            text-align: center;
        }

        .btn:hover {
            background-color: #218838;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .search-container {
            display: flex;
            align-items: center;
        }

        .search-container input[type="text"] {
            padding: 5px;
            width: 200px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-right: 10px;
        }

        .search-container button {
            padding: 6px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #0056b3;
        }

        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }

        .pagination a:hover {
            background-color: #0056b3;
        }

        .success {
            color: green;
            font-size: 16px;
            margin-top: 10px;
        }

        .error {
            color: red;
            font-size: 16px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <header class="text-center py-3">
        <h1>Nas Client</h1>
    </header>
    <div class="mt-5">
        <!-- ส่วนฟอร์มการเพิ่มข้อมูล -->
        <div class="form-section">
            <form method="POST" action="">
                <div class="form-row">
                    <div>
                        <label for="nasname">NAS Name</label>
                        <input type="text" name="nasname" placeholder="กรอกชื่อ NAS" required>
                    </div>

                    <div>
                        <label for="shortname">Short Name</label>
                        <input type="text" name="shortname" placeholder="กรอกชื่อย่อ NAS" required>
                    </div>

                    <div>
                        <label for="secret">Secret</label>
                        <input type="password" name="secret" placeholder="กรอกรหัสลับ" required>
                    </div>

                    <div>
                        <label for="description">Description</label>
                        <input type="text" name="description" placeholder="กรอกรายละเอียด NAS">
                    </div>
                </div>

                <button type="submit" name="add_nas" class="btn">เพิ่ม NAS</button>
            </form>
        </div>

        <!-- ส่วนรายการ NAS -->
        <div class="nas-list-section">
            <div class="list-header">
                <h2>List Nas Client</h2>
                <!-- Search Bar -->
                <div class="search-container">
                    <form method="GET" action="">
                        <input type="text" name="search_query" placeholder="ค้นหา NAS" value="<?php echo $search_query; ?>">
                        <button type="submit" name="search">ค้นหา</button>
                    </form>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NAS Name</th>
                        <th>Short Name</th>
                        <th>Secret</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['nasname']; ?></td>
                        <td><?php echo $row['shortname']; ?></td>
                        <td><?php echo $row['secret']; ?></td>
                        <td><?php echo $row['description']; ?></td>
                        <td>
                            <!-- ลิงก์การลบ -->
                            <a href="?delete_id=<?php echo $row['id']; ?>" class="delete-link" data-id="<?php echo $row['id']; ?>">ลบ</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo $current_page - 1; ?>">ก่อนหน้า</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" <?php if ($i == $current_page) echo 'style="background-color:#0056b3"'; ?>><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo $current_page + 1; ?>">ถัดไป</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    const deleteLinks = document.querySelectorAll('.delete-link');

    deleteLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            const confirmDelete = confirm('คุณแน่ใจที่จะลบ NAS นี้หรือไม่?');
            if (!confirmDelete) {
                event.preventDefault(); // ถ้าผู้ใช้กดยกเลิก จะไม่ทำการลบ
            }
        });
    });
</script>

</body>
</html>
