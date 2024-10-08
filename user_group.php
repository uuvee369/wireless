<?php
include 'header.php';  // Include header เพื่อใช้ sidebar
include 'connect_db/connect.php';
include 'session.php';

// เพิ่มกลุ่มผู้ใช้พร้อมการตั้งค่า
if (isset($_POST['add_group'])) {
    $groupname = $conn->real_escape_string($_POST['groupname']);
    $simultaneous_use = $conn->real_escape_string($_POST['simultaneous_use']);
    $session_timeout_hours = $conn->real_escape_string($_POST['session_timeout']) * 3600; // แปลงชั่วโมงเป็นวินาที
    $idle_timeout_minutes = $conn->real_escape_string($_POST['idle_timeout']) * 60; // แปลงนาทีเป็นวินาที
    $rate_limit = $conn->real_escape_string($_POST['rate_limit']); // ความเร็วเน็ต Upload/Download เช่น 10M/10M
    $days_of_week = $_POST['days_of_week'];  // รับ Array ที่เก็บวันที่ใช้งานจาก checkbox

    // ตรวจสอบว่ามีกลุ่มนี้อยู่แล้วหรือไม่
    $check_sql = "SELECT * FROM radgroupcheck WHERE groupname='$groupname'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "<p class='error'>เกิดข้อผิดพลาด: กลุ่ม '$groupname' มีอยู่แล้ว</p>";
    } else {
        // เพิ่มกลุ่มผู้ใช้ใน radgroupcheck
        $sql = "INSERT INTO radgroupcheck (groupname, attribute, op, value) VALUES ('$groupname', 'Auth-Type', ':=', 'Accept')";
        if ($conn->query($sql) === TRUE) {
            // ตรวจสอบว่าจำกัดจำนวนการล็อกอินหรือไม่
            if ($simultaneous_use > 0) {
                // เพิ่มการตั้งค่า Simultaneous-Use หากมีการจำกัด
                $conn->query("INSERT INTO radgroupcheck (groupname, attribute, op, value) VALUES ('$groupname', 'Simultaneous-Use', ':=', '$simultaneous_use')");
            }

            // เพิ่มการตั้งค่าอื่นๆ สำหรับกลุ่มผู้ใช้
            $conn->query("INSERT INTO radgroupcheck (groupname, attribute, op, value) VALUES ('$groupname', 'Session-Timeout', ':=', '$session_timeout_hours')");
            $conn->query("INSERT INTO radgroupcheck (groupname, attribute, op, value) VALUES ('$groupname', 'Idle-Timeout', ':=', '$idle_timeout_minutes')");

            // จัดการความเร็วเน็ต Upload/Download (Mikrotik-Rate-Limit)
            $conn->query("INSERT INTO radgroupreply (groupname, attribute, op, value) VALUES ('$groupname', 'Mikrotik-Rate-Limit', ':=', '$rate_limit')");

            // บันทึกวันที่ใช้งาน
            foreach ($days_of_week as $day) {
                $conn->query("INSERT INTO radgroupcheck (groupname, attribute, op, value) VALUES ('$groupname', 'Day-Of-Week', ':=', '$day')");
            }

            echo "<p class='success'>เพิ่มกลุ่มผู้ใช้และกำหนดค่าเรียบร้อย!</p>";
        } else {
            echo "<p class='error'>เกิดข้อผิดพลาด: " . $conn->error . "</p>";
        }
    }
}

// ลบกลุ่มผู้ใช้
if (isset($_POST['delete_group'])) {
    $groupname = $conn->real_escape_string($_POST['groupname']);

    $sql = "DELETE FROM radgroupcheck WHERE groupname='$groupname'";
    if ($conn->query($sql) === TRUE) {
        echo "<p class='success'>ลบกลุ่มผู้ใช้สำเร็จ!</p>";
    } else {
        echo "<p class='error'>เกิดข้อผิดพลาด: " . $conn->error . "</p>";
    }
}

// ดึงรายการกลุ่มผู้ใช้ (ใช้ DISTINCT เพื่อป้องกันการแสดงกลุ่มซ้ำ)
$sql = "SELECT DISTINCT groupname FROM radgroupcheck";
$result = $conn->query($sql);

// ตรวจสอบว่ามีการกดดูรายละเอียดหรือไม่
$group_detail = null;
if (isset($_GET['view_group'])) {
    $groupname = $conn->real_escape_string($_GET['groupname']);
    
    // ดึงรายละเอียดของกลุ่มจาก radgroupcheck
    $detail_sql = "SELECT attribute, value FROM radgroupcheck WHERE groupname='$groupname'";
    $group_detail = $conn->query($detail_sql);

    // ดึงข้อมูลจาก radgroupreply สำหรับความเร็วเน็ต
    $reply_sql = "SELECT value FROM radgroupreply WHERE groupname='$groupname' AND attribute='Mikrotik-Rate-Limit'";
    $rate_limit_detail = $conn->query($reply_sql)->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งค่ากลุ่มผู้ใช้</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            margin-left: 270px;
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .form-section, .list-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .form-section {
            flex: 1;
        }

        .list-section {
            flex: 1;
        }

        h1 {
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 30px;
        }

        label {
            display: block;
            font-size: 16px;
            margin-bottom: 8px;
        }

        input[type="text"], input[type="number"], select {
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
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #218838;
        }

        .checkbox-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .checkbox-container label {
            margin-right: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .success {
            color: green;
            font-size: 16px;
        }

        .error {
            color: red;
            font-size: 16px;
        }

        .details-container {
            margin-top: 20px;
            padding: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- ส่วนการเพิ่มกลุ่มผู้ใช้ -->
        <div class="form-section">
            <h1>เพิ่มกลุ่มผู้ใช้</h1>
            <form method="POST" action="">
                <label for="groupname">ชื่อกลุ่มผู้ใช้</label>
                <input type="text" name="groupname" placeholder="ชื่อกลุ่มผู้ใช้" required>

                <label for="simultaneous_use">Login พร้อมกันกี่อุปกรณ์ (ใส่ 0 เพื่อไม่จำกัด)</label>
                <input type="number" name="simultaneous_use" placeholder="จำนวนอุปกรณ์ที่สามารถล็อกอินพร้อมกันได้" required>

                <label for="session_timeout">Login 1 ครั้งใช้ได้นาน (ชั่วโมง)</label>
                <input type="number" name="session_timeout" placeholder="เวลาใช้งานสูงสุดต่อการล็อกอิน (ชั่วโมง)" required>

                <label for="idle_timeout">หยุดอัตโนมัติเมื่อไม่ได้ใช้งาน (นาที)</label>
                <input type="number" name="idle_timeout" placeholder="เวลาหยุดอัตโนมัติเมื่อไม่ได้ใช้งาน (นาที)" required>

                <label for="rate_limit">ความเร็วเน็ต (Upload/Download) เช่น 10M/10M</label>
                <input type="text" name="rate_limit" placeholder="ความเร็ว Upload/Download" required>

                <label for="days_of_week">วันใช้งาน</label>
                <div class="checkbox-container">
                    <label><input type="checkbox" name="days_of_week[]" value="0"> อาทิตย์</label>
                    <label><input type="checkbox" name="days_of_week[]" value="1"> จันทร์</label>
                    <label><input type="checkbox" name="days_of_week[]" value="2"> อังคาร</label>
                    <label><input type="checkbox" name="days_of_week[]" value="3"> พุธ</label>
                    <label><input type="checkbox" name="days_of_week[]" value="4"> พฤหัสบดี</label>
                    <label><input type="checkbox" name="days_of_week[]" value="5"> ศุกร์</label>
                    <label><input type="checkbox" name="days_of_week[]" value="6"> เสาร์</label>
                </div>

                <button type="submit" name="add_group" class="btn">เพิ่มกลุ่มผู้ใช้</button>
            </form>
        </div>

        <!-- ส่วนรายการกลุ่มผู้ใช้ -->
        <div class="list-section">
            <h2>รายการกลุ่มผู้ใช้</h2>
            <table>
                <thead>
                    <tr>
                        <th>ชื่อกลุ่มผู้ใช้</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['groupname']; ?></td>
                        <td>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="groupname" value="<?php echo $row['groupname']; ?>">
                                <button type="submit" name="delete_group" class="btn">ลบ</button>
                            </form>
                            <a href="?view_group=true&groupname=<?php echo $row['groupname']; ?>" class="btn">ดูรายละเอียด</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- แสดงรายละเอียดของกลุ่ม -->
            <?php if ($group_detail): ?>
            <div class="details-container">
                <h3>รายละเอียดกลุ่ม: <?php echo $groupname; ?></h3>
                <ul>
                    <?php 
                    // แสดงผลแต่ละค่าเป็นข้อความที่ชัดเจน
                    while ($detail = $group_detail->fetch_assoc()):
                        if ($detail['attribute'] == 'Simultaneous-Use') {
                            echo "<li>Login พร้อมกันได้: " . ($detail['value'] == 0 ? "ไม่จำกัด" : $detail['value']) . " อุปกรณ์</li>";
                        } elseif ($detail['attribute'] == 'Session-Timeout') {
                            echo "<li>Login 1 ครั้งใช้ได้นาน: " . ($detail['value'] / 3600) . " ชั่วโมง</li>";
                        } elseif ($detail['attribute'] == 'Idle-Timeout') {
                            echo "<li>หยุดอัตโนมัติเมื่อไม่ได้ใช้งาน: " . ($detail['value'] / 60) . " นาที</li>";
                        }
                    endwhile;
                    if ($rate_limit_detail) {
                        echo "<li>ความเร็วเน็ต (Upload/Download): " . $rate_limit_detail['value'] . "</li>";
                    }

                    // แสดงวันที่ใช้งาน
                    $days_sql = "SELECT value FROM radgroupcheck WHERE groupname='$groupname' AND attribute='Day-Of-Week'";
                    $days_result = $conn->query($days_sql);
                    $days_list = [];
                    while ($day = $days_result->fetch_assoc()) {
                        $days_list[] = $day['value'];
                    }
                    if (!empty($days_list)) {
                        echo "<li>วันใช้งาน: " . implode(", ", $days_list) . "</li>";
                    }
                    ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
