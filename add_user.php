<?php
include 'header.php';  // Include header เพื่อใช้ sidebar
include 'connect_db/connect.php';
include 'session.php';

// ดึงรายชื่อกลุ่มจาก radgroupcheck เพื่อนำมาแสดงในฟอร์ม
$group_sql = "SELECT DISTINCT groupname FROM radgroupcheck";
$group_result = $conn->query($group_sql);

// เพิ่มข้อมูลผู้ใช้
if (isset($_POST['add'])) {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $groupname = $conn->real_escape_string($_POST['groupname']); // รับค่ากลุ่ม

    // ตรวจสอบว่ามี username นี้อยู่แล้วหรือไม่
    $check_sql = "SELECT * FROM radcheck WHERE username='$username'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "<p class='error'>เกิดข้อผิดพลาด: ชื่อผู้ใช้ '$username' มีอยู่แล้ว</p>";
    } else {
        // เพิ่มผู้ใช้ลงใน radcheck พร้อม Cleartext-Password ใน attribute
        $sql = "INSERT INTO radcheck (fullname,username, attribute, op, value) VALUES ('$fullname','$username', 'Cleartext-Password', ':=', '$password')";
        if ($conn->query($sql) === TRUE) {
            // เพิ่มผู้ใช้เข้าไปในกลุ่มที่เลือกใน radusergroup
            $group_sql = "INSERT INTO radusergroup (username, groupname) VALUES ('$username', '$groupname')";
            if ($conn->query($group_sql) === TRUE) {
                echo "<p class='success'>เพิ่มผู้ใช้และแอดเข้าในกลุ่ม '$groupname' สำเร็จ!</p>";
            } else {
                echo "<p class='error'>เกิดข้อผิดพลาดในการเพิ่มผู้ใช้ในกลุ่ม: " . $conn->error . "</p>";
            }
        } else {
            echo "<p class='error'>เกิดข้อผิดพลาด: " . $conn->error . "</p>";
        }
    }
}

// แก้ไขกลุ่มของผู้ใช้จากรายการผู้ใช้
if (isset($_POST['update_group'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $groupname = $conn->real_escape_string($_POST['groupname']); // รับค่ากลุ่มใหม่

    // ตรวจสอบว่าผู้ใช้อยู่ในกลุ่มหรือไม่
    $check_group_sql = "SELECT * FROM radusergroup WHERE username='$username'";
    $check_group_result = $conn->query($check_group_sql);

    if ($check_group_result->num_rows > 0) {
        // แก้ไขกลุ่มผู้ใช้ใน radusergroup
        $update_group_sql = "UPDATE radusergroup SET groupname='$groupname' WHERE username='$username'";
    } else {
        // เพิ่มผู้ใช้เข้าไปในกลุ่ม
        $update_group_sql = "INSERT INTO radusergroup (username, groupname) VALUES ('$username', '$groupname')";
    }

    if ($conn->query($update_group_sql) === TRUE) {
        echo "<p class='success'>แก้ไขกลุ่มผู้ใช้เรียบร้อย!</p>";
    } else {
        echo "<p class='error'>เกิดข้อผิดพลาดในการแก้ไขกลุ่ม: " . $conn->error . "</p>";
    }
}

// ลบข้อมูลผู้ใช้
if (isset($_POST['delete'])) {
    $id = $conn->real_escape_string($_POST['id']);
    $username = $conn->real_escape_string($_POST['username']); // ต้องการชื่อผู้ใช้เพื่อลบจาก radusergroup ด้วย

    // ลบผู้ใช้จาก radcheck
    $sql = "DELETE FROM radcheck WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        // ลบผู้ใช้จาก radusergroup
        $group_sql = "DELETE FROM radusergroup WHERE username='$username'";
        if ($conn->query($group_sql) === TRUE) {
            echo "<p class='success'>ลบผู้ใช้และกลุ่มสำเร็จ!</p>";
        } else {
            echo "<p class='error'>เกิดข้อผิดพลาดในการลบกลุ่ม: " . $conn->error . "</p>";
        }
    } else {
        echo "<p class='error'>เกิดข้อผิดพลาด: " . $conn->error . "</p>";
    }
}

// ค้นหาผู้ใช้
$search_query = "";
if (isset($_POST['search'])) {
    $search_query = $conn->real_escape_string($_POST['search_query']);
    $sql = "SELECT * FROM radcheck WHERE fullname LIKE '%$search_query%' OR username LIKE '%$search_query%'";
} else {
    $sql = "SELECT * FROM radcheck";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มและจัดการผู้ใช้ในระบบ FreeRADIUS</title>
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

        .user-list-section {
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

        input[type="text"], input[type="password"], textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .btn {
            background-color: #28a745;
            color: white;
            padding: 5px 5px;
            border: none;
            border-radius: 4px;
            font-size: 10px;
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
            <h1>เพิ่มและจัดการผู้ใช้ในระบบ FreeRADIUS</h1>
        </header>
        <div class="mt-5">
            <div class="row">
                <!-- ส่วนฟอร์มการเพิ่มข้อมูล -->
                <div class="form-section">
                    <h2>เพิ่มผู้ใช้ใหม่</h2>
                    <form method="POST" action="">
                        <label for="fullname">ชื่อเต็ม</label>
                        <input type="text" name="fullname" placeholder="ชื่อเต็ม" required>

                        <label for="username">ชื่อผู้ใช้</label>
                        <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>

                        <label for="password">รหัสผ่าน</label>
                        <input type="password" name="password" placeholder="รหัสผ่าน" required>

                        <label for="groupname">กลุ่มผู้ใช้</label>
                        <select name="groupname" required>
                            <?php while ($group_row = $group_result->fetch_assoc()): ?>
                                <option value="<?php echo $group_row['groupname']; ?>"><?php echo $group_row['groupname']; ?></option>
                            <?php endwhile; ?>
                        </select>

                        <button type="submit" name="add" class="btn">เพิ่มผู้ใช้</button>
                    </form>
                </div>

                <!-- ส่วนรายการผู้ใช้ -->
                <div class="user-list-section">
                    <h2>ค้นหาผู้ใช้</h2>
                    <div class="search-container">
                        <form method="POST" action="">
                            <input type="text" name="search_query" placeholder="ค้นหาชื่อผู้ใช้หรือชื่อเต็ม" value="<?php echo $search_query; ?>">
                            <button type="submit" name="search">ค้นหา</button>
                        </form>
                    </div>

                    <h2>รายการผู้ใช้</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ชื่อเต็ม</th>
                                <th>ชื่อผู้ใช้</th>
                                <th>รหัสผ่าน</th>
                                <th>กลุ่มผู้ใช้</th>
                                <th>จัดการกลุ่ม</th>
                                <th>การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr id="row-<?php echo $row['id']; ?>">
                                <td><?php echo $row['id']; ?></td>
                                <td class="fullname"><?php echo $row['fullname']; ?></td>
                                <td class="username"><?php echo $row['username']; ?></td>
                                <td class="password"><?php echo $row['value']; ?></td>
                                <td>
                                    <?php
                                    // ดึงกลุ่มผู้ใช้จาก radusergroup
                                    $group_query = "SELECT groupname FROM radusergroup WHERE username='".$row['username']."'";
                                    $group_result = $conn->query($group_query);
                                    $group_data = $group_result->fetch_assoc();
                                    echo $group_data ? $group_data['groupname'] : "ไม่ได้อยู่ในกลุ่ม";
                                    ?>
                                </td>
                                <td>
                                    <form method="POST" action="">
                                        <input type="hidden" name="username" value="<?php echo $row['username']; ?>">
                                        <select name="groupname">
                                            <option value="">เลือกกลุ่ม</option>
                                            <?php
                                            $group_sql = "SELECT DISTINCT groupname FROM radgroupcheck";
                                            $groups = $conn->query($group_sql);
                                            while ($group = $groups->fetch_assoc()):
                                            ?>
                                                <option value="<?php echo $group['groupname']; ?>" <?php echo ($group_data && $group_data['groupname'] == $group['groupname']) ? 'selected' : ''; ?>>
                                                    <?php echo $group['groupname']; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <button type="submit" name="update_group" class="btn">อัปเดตกลุ่ม</button>
                                    </form>
                                </td>
                                <td>
                                    <button onclick="editRow(<?php echo $row['id']; ?>)">แก้ไข</button> |
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="username" value="<?php echo $row['username']; ?>">
                                        <button type="submit" name="delete" style="background-color: #d9534f; color: white; border: none; border-radius: 4px;">ลบ</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
