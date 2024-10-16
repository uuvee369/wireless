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
    $groupname = isset($_POST['groupname']) && !empty($_POST['groupname']) ? $conn->real_escape_string($_POST['groupname']) : null; // ตรวจสอบการเลือกกลุ่ม

    // ตรวจสอบว่ามี username นี้อยู่แล้วหรือไม่
    $check_sql = "SELECT * FROM radcheck WHERE username='$username'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        
    } else {
        // เพิ่มผู้ใช้ลงใน radcheck พร้อม Cleartext-Password ใน attribute
        $sql = "INSERT INTO radcheck (fullname, username, attribute, op, value) VALUES ('$fullname','$username', 'Cleartext-Password', ':=', '$password')";
        if ($conn->query($sql) === TRUE) {
            // เพิ่มผู้ใช้เข้าไปในกลุ่มที่เลือกใน radusergroup (ถ้ามีการเลือกกลุ่ม)
            if ($groupname) {
                $group_sql = "INSERT INTO radusergroup (username, groupname) VALUES ('$username', '$groupname')";
                if ($conn->query($group_sql) === TRUE) {
                    
                } else {
                    
                }
            } else {
               
            }
        } else {
            
        }
    }
}

// แก้ไขกลุ่มของผู้ใช้จากรายการผู้ใช้
if (isset($_POST['update_group'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $groupname = isset($_POST['groupname']) ? $conn->real_escape_string($_POST['groupname']) : null;

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

    $conn->query($update_group_sql);
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
          
        } else {
           
        }
    } else {
     
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

        .form-section {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .user-list-section {
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
        }

        .form-row > div {
            flex: 1;
        }

        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
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
    </style>
    <script>
        // ฟังก์ชันในการอัปเดตกลุ่มโดยอัตโนมัติเมื่อเลือกกลุ่มใหม่
        function updateGroup(username, selectElement) {
            var groupname = selectElement.value;

            // ส่งข้อมูลอัปเดตกลุ่มด้วย Ajax
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log("Group updated for user: " + username);
                }
            };
            xhr.send("update_group=true&username=" + username + "&groupname=" + groupname);
        }
    </script>
</head>
<body>
    <div class="container">
        <header class="text-center py-3">
            <h1>เพิ่มผู้ใช้ในระบบ</h1>
        </header>
        <div class="mt-5">
            <!-- ส่วนฟอร์มการเพิ่มข้อมูล -->
            <div class="form-section">
                <form method="POST" action="">
                    <div class="form-row">
                        <div>
                            <label for="fullname">ชื่อเต็ม</label>
                            <input type="text" name="fullname" placeholder="ชื่อเต็ม" required>
                        </div>
                        <div>
                            <label for="username">ชื่อผู้ใช้</label>
                            <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
                        </div>
                        <div>
                            <label for="password">รหัสผ่าน</label>
                            <input type="password" name="password" placeholder="รหัสผ่าน" required>
                        </div>
                        <div>
                            <label for="groupname">กลุ่มผู้ใช้</label>
                            <select name="groupname">
                                <option value="">ไม่เลือกกลุ่ม</option>
                                <?php while ($group_row = $group_result->fetch_assoc()): ?>
                                    <option value="<?php echo $group_row['groupname']; ?>"><?php echo $group_row['groupname']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="add" class="btn">เพิ่มผู้ใช้</button>
                </form>
            </div>

            <!-- ส่วนรายการผู้ใช้ -->
            <div class="user-list-section">
                <div class="list-header">
                    <h2>รายการผู้ใช้</h2>
                    <div class="search-container">
                        <form method="POST" action="">
                            <input type="text" name="search_query" placeholder="ค้นหาชื่อผู้ใช้หรือชื่อเต็ม" value="<?php echo $search_query; ?>">
                            <button type="submit" name="search">ค้นหา</button>
                        </form>
                    </div>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ชื่อเต็ม</th>
                            <th>ชื่อผู้ใช้</th>
                            <th>รหัสผ่าน</th>
                            <th>กลุ่มผู้ใช้</th>
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
                                <select name="groupname" onchange="updateGroup('<?php echo $row['username']; ?>', this)">
                                    <option value="">เลือกกลุ่ม</option>
                                    <?php
                                    $group_query = "SELECT groupname FROM radusergroup WHERE username='".$row['username']."'";
                                    $group_result = $conn->query($group_query);
                                    $group_data = $group_result->fetch_assoc();

                                    $group_sql = "SELECT DISTINCT groupname FROM radgroupcheck";
                                    $groups = $conn->query($group_sql);
                                    while ($group = $groups->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $group['groupname']; ?>" <?php echo ($group_data && $group_data['groupname'] == $group['groupname']) ? 'selected' : ''; ?>>
                                            <?php echo $group['groupname']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </td>
                            <td>
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
</body>
</html>

<?php
$conn->close();
?>
