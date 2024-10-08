<?php
include 'header.php';  // Include header ที่มี sidebar และ navbar
include 'connect_db/connect.php';
include 'session.php';

// ดึงข้อมูลจากตาราง radpostauth
$sql = "SELECT * FROM radpostauth ORDER BY authdate DESC";
$result = $conn->query($sql);

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

        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            text-align: center;
        }

        .back-link:hover {
            background-color: #0056b3;
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
    </div>
</body>
</html>
