<?php
require_once 'auth.php';
require_once 'db.php';

$message = '';
$error = '';

// Fetch available programs
$programs = $conn->query("
    SELECT program_id, program_name, program_code
    FROM programs
    ORDER BY program_name ASC
");

// Handle admission form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $father_name = trim($_POST['father_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $program_id = (int)($_POST['program_id'] ?? 0);

    if (
        $first_name === '' ||
        $last_name === '' ||
        $father_name === '' ||
        $email === '' ||
        $phone === '' ||
        $date_of_birth === '' ||
        $program_id <= 0
    ) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {

        // Confirm selected program exists
        $check = $conn->prepare("
            SELECT program_id
            FROM programs
            WHERE program_id = ?
        ");

        $check->bind_param("i", $program_id);
        $check->execute();

        $program_exists = $check->get_result()->num_rows > 0;

        $check->close();

        if (!$program_exists) {
            $error = "Please select a valid program.";
        } else {

            $stmt = $conn->prepare("
                INSERT INTO applicants
                (
                    first_name,
                    last_name,
                    father_name,
                    email,
                    phone,
                    date_of_birth,
                    program_id,
                    application_status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')
            ");

            $stmt->bind_param(
                "ssssssi",
                $first_name,
                $last_name,
                $father_name,
                $email,
                $phone,
                $date_of_birth,
                $program_id
            );

            if ($stmt->execute()) {
                $message = "Admission application submitted successfully!";
                $_POST = [];
            } else {
                $error = "Unable to submit application. Please try again.";
            }

            $stmt->close();
        }
    }
}

// Fetch programs again for dropdown
$programs = $conn->query("
    SELECT program_id, program_name, program_code
    FROM programs
    ORDER BY program_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Management | UMS</title>

    <link rel="stylesheet" href="ums.css?v=6">
</head>

<body>

<nav class="dashboard-nav">

    <div class="dashboard-brand">
        <div class="brand-mark">U</div>

        <div class="brand-details">
            <strong>UMS</strong>
            <span>University Management System</span>
        </div>
    </div>

    <div class="dashboard-nav-links">
        <a href="index.php">
            <span class="nav-icon">⌂</span>
            Dashboard
        </a>

        <a href="admission.php">
            <span class="nav-icon">▣</span>
            Admissions
        </a>

        <a href="view_applicants.php">
            <span class="nav-icon">▤</span>
            Applicants
        </a>

        <a href="logout.php" class="nav-logout">
            Logout
        </a>
    </div>

</nav>

<main class="module-container">

    <section class="module-welcome">

        <div>
            <span class="welcome-tag">ADMISSIONS MANAGEMENT</span>

            <h1>
                <span class="module-heading-icon">▣</span>
                Admission Management
            </h1>

            <p>
                Register new applicants and manage university admission applications.
            </p>
        </div>

    </section>

    <div class="module-back-row">
        <a href="index.php" class="back-dashboard">
            ← Back to Dashboard
        </a>
    </div>

    <?php if ($message): ?>
        <div class="module-alert success-alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="module-alert error-alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <section class="module-card">

        <div class="module-card-heading">
            <div>
                <h2 class="module-card-title">New Admission Application</h2>
                <p>Enter the applicant's personal and academic information.</p>
            </div>

            <span class="module-small-tag">NEW APPLICATION</span>
        </div>

        <form method="POST" action="admission.php">

            <div class="module-form-grid">

                <div class="module-form-group">
                    <label for="first_name">First Name</label>

                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                        placeholder="Enter first name"
                        required
                    >
                </div>

                <div class="module-form-group">
                    <label for="last_name">Last Name</label>

                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                        placeholder="Enter last name"
                        required
                    >
                </div>

                <div class="module-form-group">
                    <label for="father_name">Father Name</label>

                    <input
                        type="text"
                        id="father_name"
                        name="father_name"
                        value="<?= htmlspecialchars($_POST['father_name'] ?? '') ?>"
                        placeholder="Enter father's name"
                        required
                    >
                </div>

                <div class="module-form-group">
                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        placeholder="example@email.com"
                        required
                    >
                </div>

                <div class="module-form-group">
                    <label for="phone">Phone Number</label>

                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                        placeholder="Enter phone number"
                        required
                    >
                </div>

                <div class="module-form-group">
                    <label for="date_of_birth">Date of Birth</label>

                    <input
                        type="date"
                        id="date_of_birth"
                        name="date_of_birth"
                        value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="module-form-group">
                    <label for="program_id">Select Program</label>

                    <select name="program_id" id="program_id" required>
                        <option value="">Choose a program</option>

                        <?php while ($program = $programs->fetch_assoc()): ?>

                            <option
                                value="<?= (int)$program['program_id'] ?>"
                                <?= (($_POST['program_id'] ?? '') == $program['program_id']) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars(
                                    $program['program_name'] .
                                    ' (' . $program['program_code'] . ')'
                                ) ?>
                            </option>

                        <?php endwhile; ?>

                    </select>
                </div>

            </div>

            <div class="module-form-actions">
                <button type="submit" class="module-primary-btn">
                    Submit Application →
                </button>
            </div>

        </form>

    </section>

    <footer class="dashboard-footer">
        University Management System · Admissions Module
    </footer>

</main>

</body>
</html>