<?php
include 'connect_db/connect.php';
include 'session.php';
include 'header.php';

// ดึงข้อมูลสถิติ
$total_users = $conn->query("SELECT COUNT(*) as count FROM radcheck")->fetch_assoc()['count'];
$total_nas = $conn->query("SELECT COUNT(*) as count FROM nas")->fetch_assoc()['count'];
$auth_success_count = $conn->query("SELECT COUNT(*) as count FROM radpostauth WHERE reply = 'Access-Accept'")->fetch_assoc()['count'];
$auth_fail_count = $conn->query("SELECT COUNT(*) as count FROM radpostauth WHERE reply = 'Access-Reject'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>FreeRADIUS Dashboard</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .stat {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 30%;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .stat h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #007bff;
        }

        .start-stop {
            text-align: center;
            margin-top: 20px;
        }

        .start-stop button {
            padding: 10px 20px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            font-size: 16px;
        }

        .start-stop button.stop {
            background-color: #d9534f;
        }

        .start-stop button:hover {
            background-color: #4cae4c;
        }

        .start-stop button.stop:hover {
            background-color: #c9302c;
        }

        canvas {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <header class="text-center py-3">
        <h1>FreeRADIUS Dashboard</h1>
    </header>
    
    <div class="stats">
        <div class="stat">
            <h2>ผู้ใช้ทั้งหมด</h2>
            <p><?php echo $total_users; ?></p>
        </div>
        <div class="stat">
            <h2>NAS Client ที่เชื่อมต่อ</h2>
            <p><?php echo $total_nas; ?></p>
        </div>
        <div class="stat">
            <h2>สำเร็จ / ล้มเหลว</h2>
            <p><?php echo $auth_success_count . " / " . $auth_fail_count; ?></p>
        </div>
    </div>

    <canvas id="authChart"></canvas>

    <div class="start-stop">
        <button onclick="toggleServer('start')">Start Server</button>
        <button class="stop" onclick="toggleServer('stop')">Stop Server</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('authChart').getContext('2d');
    var authChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [], // ดึงข้อมูลวันที่มาจากฐานข้อมูลในภายหลัง
            datasets: [{
                label: 'Authentication Success',
                data: [], // ข้อมูลจำนวนการตรวจสอบสิทธิ์สำเร็จ
                borderColor: 'rgba(75, 192, 192, 1)',
                fill: false
            }, {
                label: 'Authentication Fail',
                data: [], // ข้อมูลจำนวนการตรวจสอบสิทธิ์ล้มเหลว
                borderColor: 'rgba(255, 99, 132, 1)',
                fill: false
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Authentication Statistics'
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Count'
                    }
                }
            }
        }
    });

    function toggleServer(action) {
        alert(action === 'start' ? 'Starting server...' : 'Stopping server...');
    }
</script>

</body>
</html>
