
<?php
require_once 'auth.php';
require_once 'db.php';

$message = '';
$error = '';

// Add Department
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $department_name = trim($_POST['department_name'] ?? '');
    $department_code = trim($_POST['department_code'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($department_name === '' || $department_code === '') {
        $error = "Department name and department code are required.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO departments
            (department_name, department_code, description)
            VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "sss",
            $department_name,
            $department_code,
            $description
        );

        try {
            $stmt->execute();
            $stmt->close();

            header("Location: departments.php?added=1");
            exit;

        } catch (mysqli_sql_exception $e) {
            $error = "Unable to add department. Please check whether the department code already exists.";
            $stmt->close();
        }
    }
}

if (isset($_GET['added'])) {
    $message = "Department added successfully!";
}

// Fetch Departments
$result = $conn->query(
    "SELECT * FROM departments ORDER BY department_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Departments | UMS</title>

    <link rel="stylesheet" href="ums.css?v=6">
</head>

<body class="module-page">

<!-- NAVIGATION -->
<header class="dashboard-nav">

    <a href="index.php" class="dashboard-brand">
        <div class="brand-mark">U</div>

        <div class="brand-details">
            <h2>UMS</h2>
            <span>University Management System</span>
        </div>
    </a>

    <nav class="dashboard-nav-links">
        <a href="index.php">
            <span class="nav-icon">⌂</span>
            Dashboard
        </a>

        <a href="departments.php" class="active">
            <span class="nav-icon">🏛️</span>
            Departments
        </a>

        <a href="students.php">
            <span class="nav-icon">🎓</span>
            Students
        </a>

        <a href="teachers.php">
            <span class="nav-icon">👨‍🏫</span>
            Teachers
        </a>

        <a href="courses.php">
            <span class="nav-icon">📚</span>
            Courses
        </a>

        <a href="admission.php">
            <span class="nav-icon">📨</span>
            Admissions
        </a>

        <a href="logout.php" class="nav-logout">
            <span class="nav-icon">↪</span>
            Logout
        </a>
    </nav>

</header>

<main class="dashboard-container module-container">

    <!-- PAGE HEADING -->
    <section class="module-welcome">

        <div>
            <span class="welcome-tag">ACADEMIC MANAGEMENT</span>

            <h1>Department Management</h1>

            <p>
                Create and maintain university departments
                and their academic information.
            </p>
        </div>

        <div class="module-heading-icon">🏛️</div>

    </section>

    <!-- BACK LINK -->
    <div class="module-back-row">
        <a href="index.php" class="back-dashboard">
            ← Back to Dashboard
        </a>
    </div>

    <!-- ALERTS -->
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

    <!-- ADD DEPARTMENT -->
    <section class="module-card">

        <div class="module-card-heading">

            <div class="module-card-title">
                <div class="module-card-icon">＋</div>

                <div>
                    <h2>Add New Department</h2>
                    <p>Enter the department information below.</p>
                </div>
            </div>

            <span class="module-small-tag">NEW RECORD</span>

        </div>

        <form method="POST" action="departments.php">

            <div class="module-form-grid">

                <div class="module-form-group">
                    <label for="department_name">
                        Department Name <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="department_name"
                        name="department_name"
                        placeholder="e.g. Computer Science"
                        required
                    >
                </div>

                <div class="module-form-group">
                    <label for="department_code">
                        Department Code <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="department_code"
                        name="department_code"
                        placeholder="e.g. CS, SE, IT"
                        required
                    >
                </div>

                <div class="module-form-group full-width">
                    <label for="description">Description</label>

                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        placeholder="Enter a brief department description..."
                    ></textarea>
                </div>

            </div>

            <div class="module-form-actions">
                <button type="submit" class="module-primary-btn">
                    + Add Department
                </button>
            </div>

        </form>

    </section>

    <!-- DEPARTMENT RECORDS -->
    <section class="module-card records-card">

        <div class="module-card-heading">

            <div class="module-card-title">
                <div class="module-card-icon">▤</div>

                <div>
                    <h2>Department Records</h2>
                    <p>All departments registered in the system.</p>
                </div>
            </div>

            <span class="module-small-tag">RECORDS</span>

        </div>

        <div class="module-table-wrap">

            <table class="module-table">

                <thead>
                    <tr>
                        <th>Department ID</th>
                        <th>Department Name</th>
                        <th>Department Code</th>
                        <th>Description</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <tr>
                                <td>
                                    <span class="department-id">
                                        #<?php echo htmlspecialchars((string)$row['department_id']); ?>
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars($row['department_name']); ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="department-code">
                                        <?php echo htmlspecialchars($row['department_code']); ?>
                                    </span>
                                </td>

                                <td class="department-description">
                                    <?php
                                    echo htmlspecialchars(
                                        $row['description'] ?? ''
                                    );
                                    ?>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4" class="module-empty-state">
                                <span>📂</span>
                                <strong>No departments found</strong>
                                <p>Add your first department using the form above.</p>
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

    <!-- FOOTER -->
    <footer class="dashboard-footer">
        <span>© 2026 University Management System</span>
        <span>Department Management</span>
    </footer>

</main>

</body>
</html>
