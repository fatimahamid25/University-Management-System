
<?php
require_once 'auth.php';
require_once 'db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semester_name = trim($_POST['semester_name'] ?? '');
    $semester_year = trim($_POST['semester_year'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');

    if ($semester_name === '' || $semester_year === '' || $start_date === '' || $end_date === '') {
        $error = "Please fill in all required fields.";
    } elseif (!ctype_digit($semester_year) || (int)$semester_year < 2000) {
        $error = "Enter a valid semester year.";
    } elseif ($end_date < $start_date) {
        $error = "End date cannot be earlier than start date.";
    } else {
        $semester_year = (int)$semester_year;

        $stmt = $conn->prepare(
            "INSERT INTO semesters (semester_name, semester_year, start_date, end_date)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("siss", $semester_name, $semester_year, $start_date, $end_date);

        try {
            $stmt->execute();
            $stmt->close();
            header("Location: semesters.php?added=1");
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = "Unable to add semester. Please check the information.";
            $stmt->close();
        }
    }
}

if (isset($_GET['added'])) {
    $message = "Semester added successfully!";
}

$semesters = $conn->query(
    "SELECT * FROM semesters ORDER BY semester_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semesters | UMS</title>
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
            <span class="welcome-tag">ACADEMIC MANAGEMENT</span>
            <h1>Semester Management</h1>
            <p>Organize academic semesters and manage their schedules.</p>
        </div>
        <div class="module-heading-icon">📅</div>
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
                    <h2>Create New Semester</h2>
                    <p>Set the semester name, year and academic dates.</p>
                </div>
            </div>
            <span class="module-small-tag">NEW RECORD</span>
        </div>

        <form method="POST">
            <div class="module-form-grid">

                <div class="module-form-group">
                    <label>Semester Name <span>*</span></label>
                    <select name="semester_name" required>
                        <option value="">Select Semester</option>
                        <option value="Spring">Spring</option>
                        <option value="Summer">Summer</option>
                        <option value="Fall">Fall</option>
                        <option value="Winter">Winter</option>
                    </select>
                </div>

                <div class="module-form-group">
                    <label>Academic Year <span>*</span></label>
                    <input type="number" name="semester_year" min="2000" max="2100"
                           placeholder="e.g. 2026" required>
                </div>

                <div class="module-form-group">
                    <label>Start Date <span>*</span></label>
                    <input type="date" name="start_date" required>
                </div>

                <div class="module-form-group">
                    <label>End Date <span>*</span></label>
                    <input type="date" name="end_date" required>
                </div>

            </div>

            <div class="module-form-actions">
                <button type="submit" class="module-primary-btn">+ Add Semester</button>
            </div>
        </form>
    </section>

    <section class="module-card records-card">
        <div class="module-card-heading">
            <div class="module-card-title">
                <div class="module-card-icon">▤</div>
                <div>
                    <h2>Semester Records</h2>
                    <p>View all academic semesters and their dates.</p>
                </div>
            </div>
            <span class="module-small-tag">ACADEMIC CALENDAR</span>
        </div>

        <div class="module-table-wrap">
            <table class="module-table">
                <thead>
                    <tr>
                        <th>Semester ID</th>
                        <th>Semester</th>
                        <th>Year</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($semesters && $semesters->num_rows > 0): ?>
                    <?php while ($row = $semesters->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo (int)$row['semester_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['semester_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars((string)$row['semester_year']); ?></td>
                            <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="module-empty-state">
                            <span>📂</span>
                            <strong>No semesters found</strong>
                            <p>Create your first semester using the form above.</p>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer class="dashboard-footer">
        <span>© 2026 University Management System</span>
        <span>Semester Management</span>
    </footer>
</main>
</body>
</html>
