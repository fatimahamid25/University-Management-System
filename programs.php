
<?php
require_once 'auth.php';
require_once 'db.php';

$message = '';
$error = '';

// Add Program
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['program_name'] ?? '');
    $code = trim($_POST['program_code'] ?? '');
    $years = trim($_POST['program_years'] ?? '');
    $department_id = trim($_POST['department_id'] ?? '');

    if ($name === '' || $code === '' || $years === '' || $department_id === '') {
        $error = "Please fill in all required fields.";
    } elseif (!ctype_digit($years) || (int)$years <= 0) {
        $error = "Please enter a valid program duration.";
    } elseif (!ctype_digit($department_id) || (int)$department_id <= 0) {
        $error = "Please select a valid department.";
    } else {
        $years = (int)$years;
        $department_id = (int)$department_id;

        $stmt = $conn->prepare(
            "INSERT INTO programs
            (program_name, program_code, program_years, department_id)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param("ssii", $name, $code, $years, $department_id);

        try {
            $stmt->execute();
            $stmt->close();
            header("Location: programs.php?added=1");
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = "Unable to add program. Check whether the program code already exists.";
            $stmt->close();
        }
    }
}

if (isset($_GET['added'])) {
    $message = "Program added successfully!";
}

$departments = $conn->query(
    "SELECT department_id, department_name
     FROM departments
     ORDER BY department_name ASC"
);

$programs = $conn->query(
    "SELECT
        p.program_id AS id,
        p.program_name,
        p.program_code,
        p.program_years AS duration,
        d.department_name
     FROM programs p
     LEFT JOIN departments d
        ON p.department_id = d.department_id
     ORDER BY p.program_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programs | UMS</title>
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
        <a href="students.php"><span class="nav-icon">🎓</span> Students</a>
        <a href="teachers.php"><span class="nav-icon">👨‍🏫</span> Teachers</a>
        <a href="courses.php"><span class="nav-icon">📚</span> Courses</a>
        <a href="admission.php"><span class="nav-icon">📨</span> Admissions</a>
        <a href="logout.php" class="nav-logout"><span class="nav-icon">↪</span> Logout</a>
    </nav>
</header>

<main class="dashboard-container module-container">

    <section class="module-welcome">
        <div>
            <span class="welcome-tag">ACADEMIC MANAGEMENT</span>
            <h1>Program Management</h1>
            <p>Create and manage university degree programs and their departments.</p>
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
                    <h2>Add New Program</h2>
                    <p>Enter the academic program information.</p>
                </div>
            </div>
            <span class="module-small-tag">NEW RECORD</span>
        </div>

        <form method="POST" action="programs.php">
            <div class="module-form-grid">

                <div class="module-form-group">
                    <label for="program_name">Program Name <span>*</span></label>
                    <input type="text" id="program_name" name="program_name"
                           placeholder="e.g. BS Software Engineering" required>
                </div>

                <div class="module-form-group">
                    <label for="program_code">Program Code <span>*</span></label>
                    <input type="text" id="program_code" name="program_code"
                           placeholder="e.g. BSSE" required>
                </div>

                <div class="module-form-group">
                    <label for="program_years">Duration (Years) <span>*</span></label>
                    <input type="number" id="program_years" name="program_years"
                           min="1" placeholder="e.g. 4" required>
                </div>

                <div class="module-form-group">
                    <label for="department_id">Department <span>*</span></label>
                    <select id="department_id" name="department_id" required>
                        <option value="">Select Department</option>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars((string)$dept['department_id']); ?>">
                                <?php echo htmlspecialchars($dept['department_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

            </div>

            <div class="module-form-actions">
                <button type="submit" class="module-primary-btn">+ Add Program</button>
            </div>
        </form>
    </section>

    <section class="module-card records-card">
        <div class="module-card-heading">
            <div class="module-card-title">
                <div class="module-card-icon">▤</div>
                <div>
                    <h2>Program Records</h2>
                    <p>All academic programs registered in the system.</p>
                </div>
            </div>
            <span class="module-small-tag">RECORDS</span>
        </div>

        <div class="module-table-wrap">
            <table class="module-table">
                <thead>
                    <tr>
                        <th>Program ID</th>
                        <th>Program Name</th>
                        <th>Program Code</th>
                        <th>Duration</th>
                        <th>Department</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($programs && $programs->num_rows > 0): ?>
                        <?php while ($row = $programs->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars((string)$row['id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['program_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['program_code']); ?></td>
                                <td><?php echo htmlspecialchars((string)$row['duration']); ?> Years</td>
                                <td><?php echo htmlspecialchars($row['department_name'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="module-empty-state">
                                <span>📂</span>
                                <strong>No programs found</strong>
                                <p>Add your first academic program using the form above.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer class="dashboard-footer">
        <span>© 2026 University Management System</span>
        <span>Program Management</span>
    </footer>

</main>
</body>
</html>
