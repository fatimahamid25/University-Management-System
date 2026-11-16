
<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter your username and password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT user_id, username, password, role, status
             FROM users
             WHERE username = ? OR email = ?
             LIMIT 1"
        );

        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (
                password_verify($password, $user['password']) &&
                $user['status'] === 'Active'
            ) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                header("Location: index.php");
                exit;
            }
        }

        $error = "Invalid login details or inactive account.";
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login | UMS</title>
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f4f7fa;color:#263449;font-family:Arial,sans-serif;padding:20px}
.card{width:100%;max-width:430px;background:white;padding:35px;border:1px solid #e3eaf1;border-radius:14px;box-shadow:0 8px 30px #172b4d12}
h1{color:#172b4d;margin-bottom:8px}
p{color:#64748b}
label{display:block;font-weight:600;margin:18px 0 7px}
input{width:100%;padding:13px;border:1px solid #d5dee8;border-radius:7px;font-size:15px}
button{width:100%;padding:13px;margin-top:24px;background:#315f96;color:white;border:0;border-radius:7px;font-size:16px;font-weight:600;cursor:pointer}
button:hover{background:#244b78}
.error{padding:12px;background:#fff0f0;color:#a62c2c;border-radius:7px;margin:15px 0}
a{display:block;text-align:center;margin-top:20px;color:#315f96;text-decoration:none;font-weight:600}
</style>
</head>
<body>
<div class="card">
<h1>Welcome Back</h1>
<p>Sign in to the University Management System.</p>

<?php if($error): ?>
<div class="error"><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></div>
<?php endif; ?>

<form method="POST">
<label>Username or Email</label>
<input type="text" name="username" autocomplete="username" required>

<label>Password</label>
<input type="password" name="password" autocomplete="current-password" required>

<button type="submit">Login</button>
</form>

<a href="admission.php">Apply for Admission</a>
</div>
</body>
</html>
