
<?php
require_once 'auth.php';
require_once 'db.php';

$message = '';
$error = '';

// Add Result
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $student_id = (int)($_POST['student_id'] ?? 0);
    $course_id = (int)($_POST['course_id'] ?? 0);
    $total_marks = (float)($_POST['total_marks'] ?? 0);
    $obtained_marks = (float)($_POST['obtained_marks'] ?? 0);

    if ($student_id <= 0 || $course_id <= 0 || $total_marks <= 0) {
        $error = "Please complete all required fields.";
    } elseif ($obtained_marks < 0 || $obtained_marks > $total_marks) {
        $error = "Obtained marks must be between 0 and total marks.";
    } else {

        // Calculate Grade
        $percentage = ($obtained_marks / $total_marks) * 100;

        if ($percentage >= 80) {
            $grade = 'A';
        } elseif ($percentage >= 70) {
            $grade = 'B';
        } elseif ($percentage >= 60) {
            $grade = 'C';
        } elseif ($percentage >= 50) {
            $grade = 'D';
        } else {
            $grade = 'F';
        }

        try {
            $stmt = $conn->prepare(
                "INSERT INTO results
                (student_id, course_id, total_marks, obtained_marks)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iidd",
                $student_id,
                $course_id,
                $total_marks,
                $obtained_marks
            );

            $stmt->execute();
            $stmt->close();

            header("Location: results.php?added=1");
            exit;

        } catch (mysqli_sql_exception $e) {
            $error = "Unable to save result. Please check the selected records.";
        }
    }
}

if (isset($_GET['added'])) {
    $message = "Result added successfully!";
}

// Fetch Students
$students = $conn->query(
    "SELECT student_id, first_name, last_name
     FROM students
     ORDER BY first_name"
);

// Fetch Courses
$courses = $conn->query(
    "SELECT course_id, course_name, course_code
     FROM courses
     ORDER BY course_name"
);

// Fetch Results
$result = $conn->query(
    "SELECT
        r.*,
        s.first_name,
        s.last_name,
        c.course_name,
        c.course_code
     FROM results r
     LEFT JOIN students s ON r.student_id = s.student_id
     LEFT JOIN courses c ON r.course_id = c.course_id
     ORDER BY r.result_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination Results | UMS</title>
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
            <h1>Examination Results</h1>
            <p>Record student marks and manage academic performance records.</p>
        </div>
        <div class="module-heading-icon">🏆</div>
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
                    <h2>Add Examination Result</h2>
                    <p>Enter student marks for the selected course.</p>
                </div>
            </div>
            <span class="module-small-tag">NEW RESULT</span>
        </div>

        <form method="POST" action="results.php">

            <div class="module-form-grid">

                <div class="module-form-group">
                    <label for="student_id">Student <span>*</span></label>

                    <select name="student_id" id="student_id" required>
                        <option value="">Select Student</option>

                        <?php while ($student = $students->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars((string)$student['student_id']); ?>">
                                <?php echo htmlspecialchars(
                                    $student['first_name'] . ' ' . $student['last_name']
                                ); ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="module-form-group">
                    <label for="course_id">Course <span>*</span></label>

                    <select name="course_id" id="course_id" required>
                        <option value="">Select Course</option>

                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars((string)$course['course_id']); ?>">
                                <?php echo htmlspecialchars(
                                    $course['course_code'] . ' - ' . $course['course_name']
                                ); ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="module-form-group">
                    <label for="total_marks">Total Marks <span>*</span></label>
                    <input
                        type="number"
                        id="total_marks"
                        name="total_marks"
                        min="1"
                        step="0.01"
                        placeholder="e.g. 100"
                        required
                    >
                </div>

                <div class="module-form-group">
                    <label for="obtained_marks">Obtained Marks <span>*</span></label>
                    <input
                        type="number"
                        id="obtained_marks"
                        name="obtained_marks"
                        min="0"
                        step="0.01"
                        placeholder="Enter obtained marks"
                        required
                    >
                </div>

            </div>

            <div class="module-form-actions">
                <button type="submit" class="module-primary-btn">
                    + Save Result
                </button>
            </div>

        </form>

    </section>

    <section class="module-card records-card">

        <div class="module-card-heading">
            <div class="module-card-title">
                <div class="module-card-icon">▤</div>
                <div>
                    <h2>Student Results</h2>
                    <p>View recorded examination marks, percentages and grades.</p>
                </div>
            </div>
            <span class="module-small-tag">ACADEMIC PERFORMANCE</span>
        </div>

        <div class="module-table-wrap">

            <table class="module-table">

                <thead>
                    <tr>
                        <th>Result ID</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Total Marks</th>
                        <th>Obtained Marks</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <?php
                            $total = (float)$row['total_marks'];
                            $obtained = (float)$row['obtained_marks'];

                            $percentage = $total > 0
                                ? ($obtained / $total) * 100
                                : 0;

                            if ($percentage >= 80) {
                                $grade = 'A';
                            } elseif ($percentage >= 70) {
                                $grade = 'B';
                            } elseif ($percentage >= 60) {
                                $grade = 'C';
                            } elseif ($percentage >= 50) {
                                $grade = 'D';
                            } else {
                                $grade = 'F';
                            }
                            ?>

                            <tr>

                                <td>#<?php echo htmlspecialchars((string)$row['result_id']); ?></td>

                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars(
                                            trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))
                                        ); ?>
                                    </strong>
                                </td>

                                <td>
                                    <strong><?php echo htmlspecialchars($row['course_name'] ?? 'N/A'); ?></strong>
                                    <br>
                                    <small><?php echo htmlspecialchars($row['course_code'] ?? ''); ?></small>
                                </td>

                                <td><?php echo htmlspecialchars((string)$row['total_marks']); ?></td>

                                <td><?php echo htmlspecialchars((string)$row['obtained_marks']); ?></td>

                                <td>
                                    <strong><?php echo number_format($percentage, 2); ?>%</strong>
                                </td>

                                <td>
                                    <span class="module-status">
                                        <?php echo htmlspecialchars($grade); ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="module-empty-state">
                                <span>📂</span>
                                <strong>No examination results found</strong>
                                <p>Add the first examination result using the form above.</p>
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

    <footer class="dashboard-footer">
        <span>© 2026 University Management System</span>
        <span>Examination Management</span>
    </footer>

</main>

</body>
</html>
