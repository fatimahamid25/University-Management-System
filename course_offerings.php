
<?php
require_once 'auth.php';
require_once 'db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = (int)($_POST['course_id'] ?? 0);
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $semester_id = (int)($_POST['semester_id'] ?? 0);
    $section = trim($_POST['section'] ?? '');
    $room = trim($_POST['room'] ?? '');

    if ($course_id <= 0 || $teacher_id <= 0 || $semester_id <= 0 || $section === '' || $room === '') {
        $error = "Please complete all required fields.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO course_offerings (course_id, teacher_id, semester_id, section, room)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("iiiss", $course_id, $teacher_id, $semester_id, $section, $room);

        try {
            $stmt->execute();
            $stmt->close();
            header("Location: course_offerings.php?added=1");
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = "Unable to create course offering. Please check the selected records.";
            $stmt->close();
        }
    }
}

if (isset($_GET['added'])) {
    $message = "Course offering created successfully!";
}

$courses = $conn->query(
    "SELECT course_id, course_name, course_code FROM courses ORDER BY course_name"
);

$teachers = $conn->query(
    "SELECT teacher_id, first_name, last_name FROM teachers ORDER BY first_name"
);

$semesters = $conn->query(
    "SELECT semester_id, semester_name, semester_year FROM semesters
     ORDER BY semester_year DESC, semester_id DESC"
);

$offerings = $conn->query(
    "SELECT
        o.offering_id,
        o.section,
        o.room,
        c.course_name,
        c.course_code,
        t.first_name,
        t.last_name,
        s.semester_name,
        s.semester_year
     FROM course_offerings o
     LEFT JOIN courses c ON o.course_id = c.course_id
     LEFT JOIN teachers t ON o.teacher_id = t.teacher_id
     LEFT JOIN semesters s ON o.semester_id = s.semester_id
     ORDER BY o.offering_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Offerings | UMS</title>
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
            <h1>Course Offering Management</h1>
            <p>Assign courses to teachers and semesters with section and room details.</p>
        </div>
        <div class="module-heading-icon">📖</div>
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
                    <h2>Create Course Offering</h2>
                    <p>Assign a course, instructor and semester.</p>
                </div>
            </div>
            <span class="module-small-tag">NEW OFFERING</span>
        </div>

        <form method="POST">
            <div class="module-form-grid">

                <div class="module-form-group">
                    <label>Course <span>*</span></label>
                    <select name="course_id" required>
                        <option value="">Select Course</option>
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <option value="<?php echo (int)$course['course_id']; ?>">
                                <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="module-form-group">
                    <label>Teacher <span>*</span></label>
                    <select name="teacher_id" required>
                        <option value="">Select Teacher</option>
                        <?php while ($teacher = $teachers->fetch_assoc()): ?>
                            <option value="<?php echo (int)$teacher['teacher_id']; ?>">
                                <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="module-form-group">
                    <label>Semester <span>*</span></label>
                    <select name="semester_id" required>
                        <option value="">Select Semester</option>
                        <?php while ($semester = $semesters->fetch_assoc()): ?>
                            <option value="<?php echo (int)$semester['semester_id']; ?>">
                                <?php echo htmlspecialchars($semester['semester_name'] . ' ' . $semester['semester_year']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="module-form-group">
                    <label>Section <span>*</span></label>
                    <input type="text" name="section" placeholder="e.g. A" required>
                </div>

                <div class="module-form-group">
                    <label>Room <span>*</span></label>
                    <input type="text" name="room" placeholder="e.g. Lab 02" required>
                </div>

            </div>

            <div class="module-form-actions">
                <button type="submit" class="module-primary-btn">+ Create Offering</button>
            </div>
        </form>
    </section>

    <section class="module-card records-card">
        <div class="module-card-heading">
            <div class="module-card-title">
                <div class="module-card-icon">▤</div>
                <div>
                    <h2>Course Offering Records</h2>
                    <p>View course assignments and their teaching details.</p>
                </div>
            </div>
            <span class="module-small-tag">OFFERING DIRECTORY</span>
        </div>

        <div class="module-table-wrap">
            <table class="module-table">
                <thead>
                    <tr>
                        <th>Offering ID</th>
                        <th>Course</th>
                        <th>Teacher</th>
                        <th>Semester</th>
                        <th>Section</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($offerings && $offerings->num_rows > 0): ?>
                    <?php while ($row = $offerings->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo (int)$row['offering_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['course_name'] ?? 'N/A'); ?></strong>
                                <br><small><?php echo htmlspecialchars($row['course_code'] ?? ''); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))); ?></td>
                            <td><?php echo htmlspecialchars(($row['semester_name'] ?? 'N/A') . ' ' . ($row['semester_year'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($row['section']); ?></td>
                            <td><?php echo htmlspecialchars($row['room']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="module-empty-state">
                            <span>📂</span>
                            <strong>No course offerings found</strong>
                            <p>Create your first offering using the form above.</p>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer class="dashboard-footer">
        <span>© 2026 University Management System</span>
        <span>Course Offering Management</span>
    </footer>
</main>
</body>
</html>
