
<?php
require_once 'auth.php';
require_once 'db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = trim($_POST['course_name'] ?? '');
    $course_code = trim($_POST['course_code'] ?? '');
    $credit_hours = trim($_POST['credit_hours'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);

    if ($course_name === '' || $course_code === '' || $credit_hours === '' || $semester === '' || $department_id <= 0) {
        $error = "Please fill in all required fields.";
    } elseif (!ctype_digit($credit_hours) || (int)$credit_hours <= 0) {
        $error = "Enter valid credit hours.";
    } else {
        $credit_hours = (int)$credit_hours;

        $stmt = $conn->prepare(
            "INSERT INTO courses (course_name, course_code, credit_hours, semester, department_id)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssisi", $course_name, $course_code, $credit_hours, $semester, $department_id);

        try {
            $stmt->execute();
            $stmt->close();
            header("Location: courses.php?added=1");
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = "Unable to add course. Check whether the course code already exists.";
            $stmt->close();
        }
    }
}

if (isset($_GET['added'])) {
    $message = "Course added successfully!";
}

$departments = $conn->query(
    "SELECT department_id, department_name FROM departments ORDER BY department_name"
);

$courses = $conn->query(
    "SELECT c.*, d.department_name
     FROM courses c
     LEFT JOIN departments d ON c.department_id = d.department_id
     ORDER BY c.course_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses | UMS</title>
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
        <a href="index.php">⌂ Dashboard</a>
        <a href="departments.php">🏛️ Departments</a>
        <a href="students.php">🎓 Students</a>
        <a href="teachers.php">👨‍🏫 Teachers</a>
        <a href="courses.php" class="active">📚 Courses</a>
        <a href="admission.php">📨 Admissions</a>
        <a href="logout.php" class="nav-logout">↪ Logout</a>
    </nav>
</header>

<main class="dashboard-container module-container">

    <section class="module-welcome">
        <div>
            <span class="welcome-tag">ACADEMIC MANAGEMENT</span>
            <h1>Course Management</h1>
            <p>Create and manage university courses and their academic details.</p>
        </div>
        <div class="module-heading-icon">📚</div>
    </section>

    <div class="module-back-row">
        <a href="index.php" class="back-dashboard">← Back to Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="module-alert success-alert">✓ <?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="module-alert error-alert">! <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <section class="module-card">
        <div class="module-card-heading">
            <div class="module-card-title">
                <div class="module-card-icon">＋</div>
                <div>
                    <h2>Add New Course</h2>
                    <p>Enter the course information below.</p>
                </div>
            </div>
            <span class="module-small-tag">NEW RECORD</span>
        </div>

        <form method="POST">
            <div class="module-form-grid">

                <div class="module-form-group">
                    <label>Course Name <span>*</span></label>
                    <input type="text" name="course_name" placeholder="Enter course name" required>
                </div>

                <div class="module-form-group">
                    <label>Course Code <span>*</span></label>
                    <input type="text" name="course_code" placeholder="e.g. CS-101" required>
                </div>

                <div class="module-form-group">
                    <label>Credit Hours <span>*</span></label>
                    <input type="number" name="credit_hours" min="1" placeholder="e.g. 3" required>
                </div>

                <div class="module-form-group">
                    <label>Semester <span>*</span></label>
                    <input type="text" name="semester" placeholder="e.g. Fall" required>
                </div>

                <div class="module-form-group">
                    <label>Department <span>*</span></label>
                    <select name="department_id" required>
                        <option value="">Select Department</option>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                            <option value="<?php echo (int)$dept['department_id']; ?>">
                                <?php echo htmlspecialchars($dept['department_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

            </div>

            <div class="module-form-actions">
                <button type="submit" class="module-primary-btn">+ Add Course</button>
            </div>
        </form>
    </section>

    <section class="module-card records-card">
        <div class="module-card-heading">
            <div class="module-card-title">
                <div class="module-card-icon">▤</div>
                <div>
                    <h2>Course Records</h2>
                    <p>All courses registered in the university.</p>
                </div>
            </div>
            <span class="module-small-tag">COURSE DIRECTORY</span>
        </div>

        <div class="module-table-wrap">
            <table class="module-table">
                <thead>
                    <tr>
                        <th>Course ID</th>
                        <th>Course Name</th>
                        <th>Course Code</th>
                        <th>Credit Hours</th>
                        <th>Semester</th>
                        <th>Department</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($courses && $courses->num_rows > 0): ?>
                    <?php while ($row = $courses->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo (int)$row['course_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['course_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['course_code']); ?></td>
                            <td><?php echo htmlspecialchars((string)$row['credit_hours']); ?></td>
                            <td><?php echo htmlspecialchars($row['semester']); ?></td>
                            <td><?php echo htmlspecialchars($row['department_name'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="module-empty-state">
                            <span>📂</span>
                            <strong>No courses found</strong>
                            <p>Add your first course using the form above.</p>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer class="dashboard-footer">
        <span>© 2026 University Management System</span>
        <span>Course Management</span>
    </footer>
</main>
</body>
</html>
