<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aram Hospital</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: url('https://img.freepik.com/free-photo/medical-banner-with-blue-tone_23-2149611193.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .overlay {
            background: rgba(255, 255, 255, 0.85);
            min-height: 100vh;
            width: 100vw;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
        }
        .header {
            text-align: center;
            color: #1976d2;
            font-size: 2.8em;
            font-weight: bold;
            margin-top: 40px;
            margin-bottom: 40px;
            z-index: 2;
            position: relative;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        .role-bar {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 0;
            margin-top: 20px;
        }
        .role-btn {
            margin: 0 20px;
            padding: 18px 40px;
            font-size: 1.2em;
            border-radius: 12px;
            border: none;
            background: #1976d2;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .role-btn:hover {
            background: #1565c0;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }
        @media (max-width: 700px) {
            .header { 
                font-size: 1.8em; 
                margin-top: 20px;
                margin-bottom: 20px;
                padding: 0 20px;
            }
            .role-bar { 
                flex-direction: column; 
                padding: 20px 0; 
            }
            .role-btn { 
                margin: 10px 0; 
                width: 80vw; 
            }
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="header">
        🏥 Aram Hospital
    </div>
    <div class="role-bar">
        <a href="patient/login.php"><button class="role-btn">👤 Patient Portal</button></a>
        <a href="doctor/login.php"><button class="role-btn">🧑‍⚕️ Doctor Portal</button></a>
        <a href="admin/dashboard.php"><button class="role-btn">🛡️ Admin Portal</button></a>
    </div>
</body>
</html> 