<?php
include 'header.php';  // Include header เพื่อใช้ sidebar
include 'connect_db/connect.php';
include 'session.php';

// ฟังก์ชันการลบผู้ใช้จาก radacct
if (isset($_POST['delete_user'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $sql_delete = "DELETE FROM radacct WHERE username = '$username' AND acctstoptime IS NULL";
    
    if ($conn->query($sql_delete) === TRUE) {
        echo "<p class='success'>ลบผู้ใช้สำเร็จ!</p>";
    } else {
        echo "<p class='error'>เกิดข้อผิดพลาด: " . $conn->error . "</p>";
    }
}

// Query ดึงข้อมูลผู้ใช้ที่ยังเชื่อมต่ออยู่ (AcctStopTime IS NULL)
$sql = "SELECT username, acctstarttime, nasipaddress, framedipaddress, acctinputoctets, acctoutputoctets, callingstationid 
        FROM radacct 
        WHERE acctstoptime IS NULL
        LIMIT 10"; // ดึงข้อมูล 10 แถวแรก โดยที่ acctstoptime ยังไม่ถูกตั้งค่า

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active User Connections</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            margin-left: 270px; /* ช่องว่างเพื่อให้พอดีกับ sidebar */
            padding: 20px;
        }
        table {
            font-family: Arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .header-title {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .delete-btn {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }
        .delete-btn:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-title">Active User Connections</div>

    <?php
    // แสดงข้อมูลในรูปแบบตาราง
    if ($result->num_rows > 0) {
        echo "<table><tr><th>UserName</th><th>Start Time</th><th>NAS IP</th><th>Framed IP</th><th>Download (Bytes)</th><th>Upload (Bytes)</th><th>MAC Address</th><th>Action</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row["username"]) . "</td>
                    <td>" . htmlspecialchars($row["acctstarttime"]) . "</td>
                    <td>" . htmlspecialchars($row["nasipaddress"]) . "</td>
                    <td>" . htmlspecialchars($row["framedipaddress"]) . "</td>
                    <td>" . htmlspecialchars($row["acctinputoctets"]) . "</td>
                    <td>" . htmlspecialchars($row["acctoutputoctets"]) . "</td>
                    <td>" . htmlspecialchars($row["callingstationid"]) . "</td>
                    <td>
                        <form method='POST' action=''>
                            <input type='hidden' name='username' value='" . htmlspecialchars($row["username"]) . "'>
                            <button type='submit' name='delete_user' class='delete-btn'>ลบ</button>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No active connections</p>";
    }
    ?>

</div>

</body>
</html>
