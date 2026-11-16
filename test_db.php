
<?php
require_once 'db.php';

echo "
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Database Test | UMS</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f7fa;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}
.card {
    background: white;
    padding: 35px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 5px 20px #172b4d12;
}
h2 { color: #21643c; }
p { color: #64748b; }
a { color: #315f96; text-decoration: none; font-weight: bold; }
</style>
</head>
<body>
<div class='card'>
<h2>Database Connected Successfully!</h2>
<p>Your University Management System database is working.</p>
<a href='index.php'>Go to Dashboard</a>
</div>
</body>
</html>
";
?>
