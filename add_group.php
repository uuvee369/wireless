<?php
include 'header.php';  // Include header เพื่อใช้ sidebar
include 'connect_db/connect.php';
include 'session.php';

// เพิ่มกลุ่มผู้ใช้พร้อมการตั้งค่า
if (isset($_POST['add_group'])) {
    $groupname = $conn->real_escape_string($_POST['groupname']);
    $simultaneous_use = $conn->real_escape_string($_POST['simultaneous_use']);
    $idle_timeout_minutes = $conn->real_escape_string($_POST['idle_timeout']) * 60; // แปลงนาทีเป็นวินาที
    $upload_limit = $conn->real_escape_string($_POST['upload_limit']) . "M/"; // เพิ่ม M/ ต่อท้ายสำหรับ Upload
    $download_limit = $conn->real_escape_string($_POST['download_limit']) . "M"; // เพิ่ม M ต่อท้ายสำหรับ Download

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
            $conn->query("INSERT INTO radgroupcheck (groupname, attribute, op, value) VALUES ('$groupname', 'Idle-Timeout', ':=', '$idle_timeout_minutes')");

            // จัดการความเร็วเน็ต Upload/Download (Mikrotik-Rate-Limit)
            $rate_limit = $upload_limit . $download_limit;
            $conn->query("INSERT INTO radgroupreply (groupname, attribute, op, value) VALUES ('$groupname', 'Mikrotik-Rate-Limit', ':=', '$rate_limit')");
        }
    }
}

// ลบกลุ่มผู้ใช้
if (isset($_POST['delete_group'])) {
    $groupname = $conn->real_escape_string($_POST['groupname']);

    // ลบข้อมูลใน radgroupreply ก่อน
    $sql_reply = "DELETE FROM radgroupreply WHERE groupname='$groupname'";
    if ($conn->query($sql_reply) === TRUE) {
        // จากนั้นลบข้อมูลใน radgroupcheck
        $sql_check = "DELETE FROM radgroupcheck WHERE groupname='$groupname'";
        $conn->query($sql_check);
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
            margin-right: 20px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            /* จัดให้ "เพิ่มกลุ่ม" อยู่ซ้าย และ "รายการกลุ่ม" อยู่ขวา */
            gap: 20px;
            /* ระยะห่างระหว่างส่วนต่าง ๆ */
        }

        .form-section,
        .list-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            flex: 1;
            /* ทำให้ทั้งสองส่วนขยายเต็มพื้นที่เท่ากัน */
        }

        h1,
        h2 {
            margin-bottom: 20px;
        }

        .form-section {
            flex: 0.4;
            /* ความกว้างของฟอร์มเพิ่มกลุ่มผู้ใช้ด้านซ้าย */
        }

        .list-section {
            flex: 0.6;
            /* ความกว้างของรายการกลุ่มด้านขวา */
        }

        form {
            margin-bottom: 30px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            /* เพิ่มระยะห่างระหว่างแต่ละช่อง */
            margin-bottom: 15px;
        }

        .form-group {
            flex: 1;
            /* ทำให้ช่อง input และ select มีความกว้างเท่ากัน */
        }

        label {
            display: block;
            font-size: 16px;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
            /* เพื่อให้ padding ไม่ทำให้ความกว้างเปลี่ยน */
        }

        .btn {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #218838;
            transition: 0.5s tra;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
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
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-start;
            /* เลื่อนปุ่มไปทางขวา */
            margin-top: 15px;
            /* เพิ่มระยะห่างด้านบนของปุ่ม */
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- ส่วนการเพิ่มกลุ่มผู้ใช้ -->
        <div class="form-section">
            <h1>เพิ่มกลุ่มผู้ใช้</h1>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="groupname">ชื่อกลุ่มผู้ใช้</label>
                        <input type="text" name="groupname" placeholder="กรอกชื่อกลุ่มผู้ใช้" required>
                    </div>
                    <div class="form-group">
                        <label for="simultaneous_use">Login พร้อมกันกี่อุปกรณ์</label>
                        <select name="simultaneous_use" required>
                            <option value="1">1 อุปกรณ์</option>
                            <option value="2">2 อุปกรณ์</option>
                            <option value="3">3 อุปกรณ์</option>
                            <option value="0">ไม่จำกัด</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="upload_limit">ความเร็ว Upload เช่น 10</label>
                        <input type="number" name="upload_limit" placeholder="หน่วยเป็น Mbps" required>
                    </div>
                    <div class="form-group">
                        <label for="download_limit">ความเร็ว Download เช่น 10</label>
                        <input type="number" name="download_limit" placeholder="หน่วยเป็น Mbps" required>
                    </div>
                </div>
                <div>
                    <label for="idle_timeout">หยุดอัตโนมัติเมื่อไม่ได้ใช้งาน (นาที)</label>
                    <input type="number" name="idle_timeout" placeholder="ให้ผู้ใช้หยุดอัตโนมัติเมื่อไม่ได้ใช้งาน" required>
                </div>
                <div class="form-actions">
                    <button type="submit" name="add_group" class="btn">เพิ่มกลุ่มผู้ใช้</button>
                </div>
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
                        while ($detail = $group_detail->fetch_assoc()):
                            if ($detail['attribute'] == 'Simultaneous-Use') {
                                echo "<li>Login พร้อมกันได้: " . ($detail['value'] == 0 ? "ไม่จำกัด" : $detail['value']) . " อุปกรณ์</li>";
                            } elseif ($detail['attribute'] == 'Idle-Timeout') {
                                echo "<li>หยุดอัตโนมัติเมื่อไม่ได้ใช้งาน: " . ($detail['value'] / 60) . " นาที</li>";
                            }
                        endwhile;
                        if ($rate_limit_detail) {
                            echo "<li>ความเร็วเน็ต (Upload/Download): " . $rate_limit_detail['value'] . "</li>";
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