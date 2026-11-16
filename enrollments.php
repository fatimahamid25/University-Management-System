
<?php
require_once 'auth.php';
require_once 'db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $offering_id = (int)($_POST['offering_id'] ?? 0);
    $enrollment_date = trim($_POST['enrollment_date'] ?? '');
    $status = trim($_POST['status'] ?? '');

    $allowed_statuses = ['Enrolled', 'Pending', 'Dropped'];

    if ($student_id <= 0 || $offering_id <= 0 || $enrollment_date === '' || !in_array($status, $allowed_statuses, true)) {
        $error = "Please complete all required fields.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO enrollments (student_id, offering_id, enrollment_date, status)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("iiss", $student_id, $offering_id, $enrollment_date, $status);

        try {
            $stmt->execute();
            $stmt->close();
            header("Location: enrollments.php?added=1");
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = "Unable to create enrollment. Check whether this student is already enrolled in this offering.";
            $stmt->close();
        }
    }
}

if (isset($_GET['added'])) {
    $message = "Enrollment recorded successfully!";
}

$students = $conn->query(
    "SELECT student_id, first_name, last_name
     FROM students ORDER BY first_name"
);

$offerings = $conn->query(
    "SELECT o.offering_id, o.section, c.course_name, c.course_code
     FROM course_offerings o
     LEFT JOIN courses c ON o.course_id = c.course_id
     ORDER BY c.course_name"
);

$enrollments = $conn->query(
    "SELECT
        e.enrollment_id,
        e.enrollment_date,
        e.status,
        s.first_name,
        s.last_name,
        c.course_name,
        c.course_code,
        o.section
     FROM enrollments e
     LEFT JOIN students s ON e.student_id = s.student_id
     LEFT JOIN course_offerings o ON e.offering_id = o.offering_id
     LEFT JOIN courses c ON o.course_id = c.course_id
     ORDER BY e.enrollment_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollments | UMS</title>
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
        <a href="courses.php">📚 Courses</a>
        <a href="admission.php">📨 Admissions</a>
        <a href="logout.php" class="nav-logout">↪ Logout</a>
    </nav>
</header>

<main class="dashboard-container module-container">

    <section class="module-welcome">
        <div>
            <span class="welcome-tag">ACADEMIC OPERATIONS</span>
            <h1>Enrollment Management</h1>
            <p>Enroll students in course offerings and monitor their enrollment status.</p>
        </div>
        <div class="module-heading-icon">📝</div>
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
                    <h2>Register Enrollment</h2>
                    <p>Select a student and the course offering.</p>
                </div>
            </div>
            <span class="module-small-tag">NEW ENROLLMENT</span>
        </div>

        <form method="POST">
            <div class="module-form-grid">

                <div class="module-form-group">
                    <label>Student <span>*</span></label>
                    <select name="student_id" required>
                        <option value="">Select Student</option>
                        <?php while ($student = $students->fetch_assoc()): ?>
                            <option value="<?php echo (int)$student['student_id']; ?>">
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' (#' . $student['student_id'] . ')'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="module-form-group">
                    <label>Course Offering <span>*</span></label>
                    <select name="offering_id" required>
                        <option value="">Select Course Offering</option>
                        <?php while ($offering = $offerings->fetch_assoc()): ?>
                            <option value="<?php echo (int)$offering['offering_id']; ?>">
                                <?php echo htmlspecialchars($offering['course_code'] . ' - ' . $offering['course_name'] . ' (Section ' . $offering['section'] . ')'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="module-form-group">
                    <label>Enrollment Date <span>*</span></label>
                    <input type="date" name="enrollment_date"
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="module-form-group">
                    <label>Enrollment Status <span>*</span></label>
                    <select name="status" required>
                        <option value="">Select Status</option>
                        <option value="Enrolled">Enrolled</option>
                        <option value="Pending">Pending</option>
                        <option value="Dropped">Dropped</option>
                    </select>
                </div>

            </div>

            <div class="module-form-actions">
                <button type="submit" class="module-primary-btn">+ Register Enrollment</button>
            </div>
        </form>
    </section>

    <section class="module-card records-card">
        <div class="module-card-heading">
            <div class="module-card-title">
                <div class="module-card-icon">▤</div>
                <div>
                    <h2>Enrollment Records</h2>
                    <p>View students enrolled in university course offerings.</p>
                </div>
            </div>
            <span class="module-small-tag">ENROLLMENT DIRECTORY</span>
        </div>

        <div class="module-table-wrap">
            <table class="module-table">
                <thead>
                    <tr>
                        <th>Enrollment ID</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Section</th>
                        <th>Enrollment Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($enrollments && $enrollments->num_rows > 0): ?>
                    <?php while ($row = $enrollments->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo (int)$row['enrollment_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($row['course_code'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($row['course_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['section'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['enrollment_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="module-empty-state">
                            <span>📂</span>
                            <strong>No enrollment records found</strong>
                            <p>Register a student in a course offering using the form above.</p>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer class="dashboard-footer">
        <span>© 2026 University Management System</span>
        <span>Enrollment Management</span>
    </footer>
</main>
</body>
</html>
