<?php
include 'header.php';  // Include header ที่มี sidebar และ navbar
include 'connect_db/connect.php';
include 'session.php';

// ฟังก์ชันการลบ NAS
if (isset($_GET['delete_id'])) {
    $delete_id = $conn->real_escape_string($_GET['delete_id']);
    $sql_delete = "DELETE FROM nas WHERE id = '$delete_id'";
    
    if ($conn->query($sql_delete) === TRUE) {
        echo "<p class='success'>ลบ NAS สำเร็จ!</p>";
    } else {
        echo "<p class='error'>เกิดข้อผิดพลาด: " . $conn->error . "</p>";
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
        // เปลี่ยนเส้นทางหลังจากเพิ่มข้อมูลสำเร็จ
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<p class='error'>เกิดข้อผิดพลาด: " . $conn->error . "</p>";
    }
}

// ฟังก์ชันการค้นหา NAS
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $conn->real_escape_string($_GET['search_query']);
    $sql = "SELECT * FROM nas WHERE nasname LIKE '%$search_query%' OR shortname LIKE '%$search_query%'";
} else {
    $sql = "SELECT * FROM nas";
}

$result = $conn->query($sql);
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

        .row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .form-section {
            width: 40%;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .nas-list-section {
            width: 55%;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            font-size: 16px;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"], input[type="password"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        textarea {
            height: 100px;
        }

        .btn {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
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

        .search-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        .search-container input[type="text"] {
            padding: 5px;
            width: 200px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .search-container button {
            padding: 6px 10px;
            margin-left: 5px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-container button:hover {
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
        <div class="row">
            <!-- ส่วนฟอร์มการเพิ่มข้อมูล -->
            <div class="form-section">
                <form method="POST" action="">
                    <label for="nasname">NAS Name</label>
                    <input type="text" name="nasname" placeholder="กรอกชื่อ NAS" required>

                    <label for="shortname">Short Name</label>
                    <input type="text" name="shortname" placeholder="กรอกชื่อย่อ NAS" required>

                    <label for="secret">Secret</label>
                    <input type="password" name="secret" placeholder="กรอกรหัสลับ" required>

                    <label for="description">Description</label>
                    <textarea name="description" placeholder="กรอกรายละเอียด NAS"></textarea>

                    <button type="submit" name="add_nas" class="btn">เพิ่ม NAS</button>
                </form>
            </div>

            <!-- ส่วนรายการ NAS -->
            <div class="nas-list-section">
                <h2>List Nas Client</h2>

                <!-- Search Bar -->
                <div class="search-container">
                    <form method="GET" action="">
                        <input type="text" name="search_query" placeholder="ค้นหา NAS" value="<?php echo $search_query; ?>">
                        <button type="submit" name="search">ค้นหา</button>
                    </form>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NAS Name</th>
                            <th>Short Name</th>
                            <th>Secret</th>
                            <th>Description</th>
                            <th>Action</th> <!-- เพิ่มส่วนการจัดการ -->
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
