<?php
include 'header.php'; // ดึงส่วนของ header เข้ามา
include 'connect_db/connect.php';
include 'session.php';

$success_message = "";
$error_message = "";

// ตรวจสอบว่ามีการอัปโหลดไฟล์หรือไม่
if (isset($_POST['upload'])) {
    $fileName = $_FILES['userfile']['tmp_name'];

    if ($_FILES['userfile']['size'] > 0) {
        // เปิดไฟล์ CSV
        $file = fopen($fileName, 'r');

        // อ่านไฟล์ทีละบรรทัด
        while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
            $fullname = $data[0];
            $username = $data[1];
            $password = $data[2];

            // เพิ่มผู้ใช้ใหม่ลงในฐานข้อมูล
            $sql = "INSERT INTO radcheck (fullname, username, value) VALUES ('$fullname', '$username', '$password')";
            if ($conn->query($sql) !== TRUE) {
                $error_message = "เกิดข้อผิดพลาด: " . $conn->error;
            }
        }

        fclose($file);
        $success_message = "นำเข้าข้อมูลผู้ใช้สำเร็จ!";
    } else {
        $error_message = "กรุณาเลือกไฟล์ CSV";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>นำเข้าข้อมูลผู้ใช้</title>
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
        }

        .form-section {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        input[type="file"] {
            margin: 10px 0;
        }

        button {
            padding: 10px 20px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #4cae4c;
        }

        .success, .error {
            color: green;
            margin-top: 10px;
            display: none;
        }

        .error {
            color: red;
        }

        .description {
            font-size: 14px;
            color: #555;
            margin-top: 10px;
        }

        .example {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-top: 10px;
            font-family: monospace;
        }

        .alert {
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            margin-bottom: 20px;
            display: none;
        }

        .alert.error {
            background-color: #f44336;
        }
    </style>
    <script>
        function showAlert(type, message) {
            var alertBox = document.getElementById('alert-box');
            alertBox.classList.add(type);
            alertBox.innerHTML = message;
            alertBox.style.display = 'block';
            
            // ซ่อนข้อความแจ้งเตือนหลังจาก 3 วินาที
            setTimeout(function() {
                alertBox.style.display = 'none';
            }, 3000);
        }

        window.onload = function() {
            <?php if (!empty($success_message)) { ?>
                showAlert('success', '<?php echo $success_message; ?>');
            <?php } elseif (!empty($error_message)) { ?>
                showAlert('error', '<?php echo $error_message; ?>');
            <?php } ?>
        }
    </script>
</head>

<body>

    <div class="container">
        <h1>นำเข้าข้อมูลผู้ใช้</h1>

        <!-- ฟอร์มนำเข้าข้อมูลผู้ใช้ -->
        <div class="form-section">
            <form action="" method="post" enctype="multipart/form-data">
                <label for="userfile">เลือกไฟล์ CSV</label>
                <input type="file" name="userfile" id="userfile" required>

                <button type="submit" name="upload">นำเข้าผู้ใช้</button>
            </form>

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
        </div>

        <!-- ข้อความแจ้งเตือน -->
        <div id="alert-box" class="alert"></div>
    </div>

</body>

</html>
