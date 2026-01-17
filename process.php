<?php
// Configuration
$host = 'localhost';
$dbname = 'web_form_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$status = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        if ($action === 'login') {
            // LOGIN LOGIC
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (password_verify($password, $row['password_hash'])) {
                    $status = "success";
                    $message = "Welcome back! Login successful.";
                } else {
                    $status = "error";
                    $message = "Incorrect password.";
                }
            } else {
                $status = "error";
                $message = "No account found with that email.";
            }

        } elseif ($action === 'signup') {
            // SIGNUP LOGIC
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $status = "error";
                $message = "Account already exists.";
            } else {
                $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
                
                // Server-side Validation
                if (strlen($password) < 8) {
                    $status = "error";
                    $message = "Password must be at least 8 characters.";
                } elseif (!preg_match('/^\d{10}$/', $phone)) {
                    $status = "error";
                    $message = "Phone number must be exactly 10 digits.";
                } else {
                    $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("INSERT INTO users (email, phone, password_hash) VALUES (?, ?, ?)");
                    
                    if ($stmt->execute([$email, $phone, $hashed_pwd])) {
                        $status = "success";
                        $message = "Account created! You can now log in.";
                    } else {
                        $status = "error";
                        $message = "Registration failed.";
                    }
                }
            }
        }
    } else {
        $status = "error";
        $message = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --card-bg: rgba(17, 25, 40, 0.75);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                        radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                        radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            background-color: #0f172a;
            background-size: 200% 200%;
            animation: gradientMove 15s ease infinite;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            color: var(--text-main);
        }
        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            text-align: center;
            max-width: 400px;
            width: 90%;
            border: 1px solid rgba(255, 255, 255, 0.125);
        }
        .icon { font-size: 3rem; margin-bottom: 20px; display: block; }
        .success { color: #10b981; text-shadow: 0 0 20px rgba(16, 185, 129, 0.4); }
        .error { color: #ef4444; text-shadow: 0 0 20px rgba(239, 68, 68, 0.4); }
        h1 { margin-top: 0; color: white; }
        p { color: var(--text-muted); }
        .btn {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="card">
        <span class="icon <?php echo $status; ?>">
            <?php echo $status == 'success' ? '🎉' : '🚫'; ?>
        </span>
        <h1><?php echo $status == 'success' ? 'Success' : 'Error'; ?></h1>
        <p><?php echo htmlspecialchars($message); ?></p>
        <a href="index.html" class="btn">Back to Home</a>
    </div>
</body>
</html>
