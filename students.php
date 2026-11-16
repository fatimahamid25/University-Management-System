
<?php
require_once 'auth.php';
require_once 'db.php';

$message = '';
$error = '';

// Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);

    if (
        $first_name === '' ||
        $last_name === '' ||
        $email === '' ||
        $phone === '' ||
        $date_of_birth === '' ||
        $department_id <= 0
    ) {
        $error = "Please complete all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO students
            (first_name, last_name, email, phone, date_of_birth, department_id)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssssi",
            $first_name,
            $last_name,
            $email,
            $phone,
            $date_of_birth,
            $department_id
        );

        try {
            $stmt->execute();
            $stmt->close();
            header("Location: students.php?added=1");
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = "Unable to add student. Please check whether the email already exists.";
            $stmt->close();
        }
    }
}

if (isset($_GET['added'])) {
    $message = "Student added successfully!";
}

$departments = $conn->query(
    "SELECT department_id, department_name
     FROM departments
     ORDER BY department_name"
);

$result = $conn->query(
    "SELECT students.*, departments.department_name
     FROM students
     LEFT JOIN departments
        ON students.department_id = departments.department_id
     ORDER BY student_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students | UMS</title>
    <link rel="stylesheet" href="ums.css?v=6">
</head>

<body class="module-page">

<header class="dashboard-nav">
    <a href="index.php" class="dashboard-brand">
        <div class="brand-mark">U</div>
        <div class="brand-details">
            <h2>UMS</h2>
            <span>University Management System</span>
        </div>
    </a>

    <nav class="dashboard-nav-links">
        <a href="index.php"><span class="nav-icon">⌂</span> Dashboard</a>
        <a href="departments.php"><span class="nav-icon">🏛️</span> Departments</a>
        <a href="students.php" class="active"><span class="nav-icon">🎓</span> Students</a>
        <a href="teachers.php"><span class="nav-icon">👨‍🏫</span> Teachers</a>
        <a href="courses.php"><span class="nav-icon">📚</span> Courses</a>
        <a href="admission.php"><span class="nav-icon">📨</span> Admissions</a>
        <a href="logout.php" class="nav-logout"><span class="nav-icon">↪</span> Logout</a>
    </nav>
</header>

<main class="dashboard-container module-container">

    <section class="module-welcome">
        <div>
            <span class="welcome-tag">ACADEMIC OPERATIONS</span>
            <h1>Student Management</h1>
            <p>Register students and maintain their personal and academic information.</p>
        </div>
        <div class="module-heading-icon">🎓</div>
    </section>

    <div class="module-back-row">
        <a href="index.php" class="back-dashboard">← Back to Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="module-alert success-alert">
            <span>✓</span>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="module-alert error-alert">
            <span>!</span>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <section class="module-card">
        <div class="module-card-heading">
            <div class="module-card-title">
                <div class="module-card-icon">＋</div>
                <div>
                    <h2>Register New Student</h2>
                    <p>Enter the student's personal and academic details.</p>
                </div>
            </div>
            <span class="module-small-tag">NEW RECORD</span>
        </div>

        <form method="POST" action="students.php">
            <div class="module-form-grid">

                <div class="module-form-group">
                    <label for="first_name">First Name <span>*</span></label>
                    <input type="text" id="first_name" name="first_name"
                           placeholder="Enter first name" required>
                </div>

                <div class="module-form-group">
                    <label for="last_name">Last Name <span>*</span></label>
                    <input type="text" id="last_name" name="last_name"
                           placeholder="Enter last name" required>
                </div>

                <div class="module-form-group">
                    <label for="email">Email Address <span>*</span></label>
                    <input type="email" id="email" name="email"
                           placeholder="student@example.com" required>
                </div>

                <div class="module-form-group">
                    <label for="phone">Phone Number <span>*</span></label>
                    <input type="tel" id="phone" name="phone"
                           placeholder="Enter phone number" required>
                </div>

                <div class="module-form-group">
                    <label for="date_of_birth">Date of Birth <span>*</span></label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required>
                </div>

                <div class="module-form-group">
                    <label for="department_id">Department <span>*</span></label>
                    <select id="department_id" name="department_id" required>
                        <option value="">Select Department</option>
                        <?php while ($department = $departments->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars((string)$department['department_id']); ?>">
                                <?php echo htmlspecialchars($department['department_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

            </div>

            <div class="module-form-actions">
                <button type="submit" class="module-primary-btn">+ Register Student</button>
            </div>
        </form>
    </section>

    <section class="module-card records-card">
        <div class="module-card-heading">
            <div class="module-card-title">
                <div class="module-card-icon">▤</div>
                <div>
                    <h2>Student Records</h2>
                    <p>View registered students and their department details.</p>
                </div>
            </div>
            <span class="module-small-tag">STUDENT DIRECTORY</span>
        </div>

        <div class="module-table-wrap">
            <table class="module-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars((string)$row['student_id']); ?></td>
                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                    </strong>
                                </td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['department_name'] ?? 'Not assigned'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="module-empty-state">
                                <span>📂</span>
                                <strong>No student records found</strong>
                                <p>Register your first student using the form above.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer class="dashboard-footer">
        <span>© 2026 University Management System</span>
        <span>Student Management</span>
    </footer>

</main>
</body>
</html>
