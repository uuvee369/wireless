<?php
include 'header.php';  
include 'connect_db/connect.php';
include 'session.php';

// ดึงรายชื่อกลุ่มจาก radgroupcheck เพื่อนำมาแสดงในฟอร์ม
$group_sql = "SELECT DISTINCT groupname FROM radgroupcheck";
$group_result = $conn->query($group_sql);

$message = ""; // ตัวแปรสำหรับเก็บข้อความแจ้งเตือน

// การอัปโหลด CSV และเพิ่มผู้ใช้ในกลุ่มที่เลือก
if (isset($_POST['upload_csv'])) {
    $groupname = $conn->real_escape_string($_POST['groupname']);
    
    // ตรวจสอบว่ามีกลุ่มที่เลือกหรือไม่
    if ($groupname && isset($_FILES['csv_file'])) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");
        if ($handle) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $fullname = $conn->real_escape_string($data[0]);
                $username = $conn->real_escape_string($data[1]);
                $password = $conn->real_escape_string($data[2]);
                
                // เพิ่มผู้ใช้ลงใน radcheck
                $sql = "INSERT INTO radcheck (fullname, username, attribute, op, value) VALUES ('$fullname', '$username', 'Cleartext-Password', ':=', '$password')";
                $conn->query($sql);
                
                // เพิ่มผู้ใช้เข้าไปในกลุ่มที่เลือกใน radusergroup
                $group_sql = "INSERT INTO radusergroup (username, groupname) VALUES ('$username', '$groupname')";
                $conn->query($group_sql);
            }
            fclose($handle);
            $message = "อัปโหลดและเพิ่มผู้ใช้จาก CSV สำเร็จ";
        } else {
            $message = "ไม่สามารถอ่านไฟล์ CSV ได้";
        }
    } else {
        $message = "กรุณาเลือกกลุ่มและอัปโหลดไฟล์ CSV";
    }
}

// ดึงรายการกลุ่มผู้ใช้
$sql = "SELECT DISTINCT groupname FROM radgroupcheck";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>นำเข้าผู้ใช้จากไฟล์ CSV</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            margin-left: 270px;
            margin-top: 80px;
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

        h1 {
            margin-bottom: 20px;
        }

        .description {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
        }

        input[type="file"], select {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
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
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-section">
            <h1>นำเข้าผู้ใช้จากไฟล์ CSV</h1>
            <div class="description">
                <p>ไฟล์ CSV ที่จะนำเข้า ควรมีรูปแบบดังนี้:</p>
                <p>คอลัมน์ที่ 1: ชื่อเต็ม (Full Name)</p>
                <p>คอลัมน์ที่ 2: ชื่อผู้ใช้ (Username)</p>
                <p>คอลัมน์ที่ 3: รหัสผ่าน (Password)</p>
                <p>ตัวอย่างข้อมูลในไฟล์ CSV:</p>

                <div class="example">
                    John Doe,johndoe,password123<br>
                    Jane Smith,janesmith,pass456<br>
                    Mike Johnson,mikej,pass789
                </div>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <label for="groupname">เลือกกลุ่มผู้ใช้</label>
                <select name="groupname" required>
                    <option value="">กรุณาเลือกกลุ่ม</option>
                    <?php while ($group_row = $group_result->fetch_assoc()): ?>
                        <option value="<?php echo $group_row['groupname']; ?>"><?php echo $group_row['groupname']; ?></option>
                    <?php endwhile; ?>
                </select>
                
                <label for="csv_file">อัปโหลดไฟล์ CSV</label>
                <input type="file" name="csv_file" accept=".csv" required>

                <button type="submit" name="upload_csv" class="btn">อัปโหลดไฟล์ CSV</button>
            </form>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <script>
            alert("<?php echo $message; ?>");
        </script>
    <?php endif; ?>
</body>

</html>

<?php
$conn->close();
?>
