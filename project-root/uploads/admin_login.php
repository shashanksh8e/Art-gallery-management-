<?php
session_start();

$admin_username = "admin";
$admin_password = "admin123";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['is_admin'] = true;
        header('Location: admin_panel.php');
        exit;
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Login</title>
    <style>
        /* Reset some default */
        * {
            box-sizing: border-box;
        }

        body {
            background: #121212;
            color: #ddd;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        h2 {
            color: #00ffd8;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 1rem;
            text-align: center;
            text-transform: uppercase;
            text-shadow: 0 0 8px #00ffd8aa;
        }

        form {
            background: #1e1e1e;
            padding: 2rem 2.5rem;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 255, 216, 0.3);
            width: 320px;
            transition: box-shadow 0.3s ease;
        }

        form:hover {
            box-shadow: 0 8px 30px rgba(0, 255, 216, 0.5);
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            letter-spacing: 0.8px;
            user-select: none;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            margin-bottom: 16px;
            border: 2px solid #333;
            border-radius: 8px;
            background: #121212;
            color: #ddd;
            font-size: 1rem;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
            outline: none;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #00ffd8;
            box-shadow: 0 0 8px #00ffd8;
            background: #181818;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #00ffd8, #00bfa1);
            border: none;
            border-radius: 10px;
            color: #121212;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 255, 216, 0.6);
            transition: background 0.3s ease, transform 0.2s ease;
            user-select: none;
        }

        button:hover {
            background: linear-gradient(135deg, #00bfa1, #00ffd8);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 255, 216, 0.8);
        }

        p {
            margin: 0 0 16px 0;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-align: center;
            user-select: none;
        }

        p[style*="color:red"] {
            color: #ff4c4c;
            text-shadow: 0 0 5px #ff4c4caa;
        }
    </style>
</head>
<body>
    <div>
        <h2>Admin Login</h2>
        <form method="post" autocomplete="off">
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required autofocus />
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required />
            
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
