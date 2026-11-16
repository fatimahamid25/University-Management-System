
<?php
require_once 'auth.php';
require_once 'db.php';

$message = '';
$error = '';

// Record Attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enrollment_id = trim($_POST['enrollment_id'] ?? '');
    $attendance_date = trim($_POST['attendance_date'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    if ($enrollment_id === '' || $attendance_date === '' || $status === '') {
        $error = "Please fill in all required fields.";
    } elseif (!ctype_digit($enrollment_id)) {
        $error = "Please select a valid enrollment.";
    } elseif (!in_array($status, ['Present', 'Absent', 'Late', 'Leave'], true)) {
        $error = "Please select a valid attendance status.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO attendance
            (enrollment_id, attendance_date, status, remarks)
            VALUES (?, ?, ?, ?)"
        );

        $enrollment_id = (int)$enrollment_id;

        $stmt->bind_param(
            "isss",
            $enrollment_id,
            $attendance_date,
            $status,
            $remarks
        );

        try {
            $stmt->execute();
            $message = "Attendance recorded successfully!";
        } catch (mysqli_sql_exception $e) {
            $error = "Unable to record attendance. Please check the enrollment details.";
        }

        $stmt->close();
    }
}

// Fetch Enrollments
$enrollments = $conn->query(
    "SELECT
        e.enrollment_id,
        st.first_name,
        st.last_name,
        c.course_code,
        c.course_name,
        co.section
     FROM enrollments e
     LEFT JOIN students st
        ON e.student_id = st.student_id
     LEFT JOIN course_offerings co
        ON e.offering_id = co.offering_id
     LEFT JOIN courses c
        ON co.course_id = c.course_id
     ORDER BY e.enrollment_id DESC"
);

// Fetch Attendance Records
$attendance_records = $conn->query(
    "SELECT
        a.attendance_id,
        a.attendance_date,
        a.status,
        a.remarks,
        st.first_name,
        st.last_name,
        c.course_name,
        c.course_code,
        co.section
     FROM attendance a
     LEFT JOIN enrollments e
        ON a.enrollment_id = e.enrollment_id
     LEFT JOIN students st
        ON e.student_id = st.student_id
     LEFT JOIN course_offerings co
        ON e.offering_id = co.offering_id
     LEFT JOIN courses c
        ON co.course_id = c.course_id
     ORDER BY a.attendance_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management | UMS</title>
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
            <h1>Attendance Management</h1>
            <p>Record and monitor student attendance for enrolled courses.</p>
        </div>
        <div class="module-heading-icon">📋</div>
    </section>

    <div class="module-back-row">
        <a href="index.php" class="back-dashboard">← Back to Dashboard</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="module-alert success-alert">
            <span>✓</span>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
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
                    <h2>Record Student Attendance</h2>
                    <p>Select an enrolled student and enter attendance details.</p>
                </div>
            </div>
            <span class="module-small-tag">NEW RECORD</span>
        </div>

        <form method="POST" action="attendance.php">

            <div class="module-form-grid">

                <div class="module-form-group">
                    <label for="enrollment_id">Student / Course Enrollment <span>*</span></label>

                    <select name="enrollment_id" id="enrollment_id" required>
                        <option value="">Select Enrollment</option>

                        <?php while ($enrollment = $enrollments->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars((string)$enrollment['enrollment_id']); ?>">
                                <?php
                                echo htmlspecialchars(
                                    trim(($enrollment['first_name'] ?? '') . ' ' . ($enrollment['last_name'] ?? '')) .
                                    ' | ' . ($enrollment['course_code'] ?? '') .
                                    ' - ' . ($enrollment['course_name'] ?? '') .
                                    ' | Section ' . ($enrollment['section'] ?? '')
                                );
                                ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

                <div class="module-form-group">
                    <label for="attendance_date">Attendance Date <span>*</span></label>
                    <input
                        type="date"
                        name="attendance_date"
                        id="attendance_date"
                        value="<?php echo date('Y-m-d'); ?>"
                        required
                    >
                </div>

                <div class="module-form-group">
                    <label for="status">Attendance Status <span>*</span></label>

                    <select name="status" id="status" required>
                        <option value="">Select Status</option>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                        <option value="Leave">Leave</option>
                    </select>
                </div>

                <div class="module-form-group">
                    <label for="remarks">Remarks</label>
                    <input
                        type="text"
                        name="remarks"
                        id="remarks"
                        placeholder="Enter additional remarks (optional)"
                    >
                </div>

            </div>

            <div class="module-form-actions">
                <button type="submit" class="module-primary-btn">
                    + Save Attendance
                </button>
            </div>

        </form>
    </section>

    <section class="module-card records-card">

        <div class="module-card-heading">
            <div class="module-card-title">
                <div class="module-card-icon">▤</div>
                <div>
                    <h2>Attendance Records</h2>
                    <p>Review recorded attendance information.</p>
                </div>
            </div>
            <span class="module-small-tag">ATTENDANCE REGISTER</span>
        </div>

        <div class="module-table-wrap">

            <table class="module-table">

                <thead>
                    <tr>
                        <th>Record ID</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Section</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($attendance_records && $attendance_records->num_rows > 0): ?>

                        <?php while ($row = $attendance_records->fetch_assoc()): ?>

                            <tr>
                                <td>#<?php echo htmlspecialchars((string)$row['attendance_id']); ?></td>

                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars(
                                            trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))
                                        ); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['course_name'] ?? 'N/A'); ?>
                                    <br>
                                    <small><?php echo htmlspecialchars($row['course_code'] ?? ''); ?></small>
                                </td>

                                <td><?php echo htmlspecialchars($row['section'] ?? 'N/A'); ?></td>

                                <td><?php echo htmlspecialchars($row['attendance_date']); ?></td>

                                <td>
                                    <span class="module-status">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>

                                <td><?php echo htmlspecialchars($row['remarks'] ?? '—'); ?></td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="module-empty-state">
                                <span>📂</span>
                                <strong>No attendance records found</strong>
                                <p>Record the first attendance entry using the form above.</p>
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>
            </table>

        </div>
    </section>

    <footer class="dashboard-footer">
        <span>© 2026 University Management System</span>
        <span>Attendance Management</span>
    </footer>

</main>

</body>
</html>
