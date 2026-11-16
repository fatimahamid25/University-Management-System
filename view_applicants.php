<?php
require_once 'auth.php';
require_once 'db.php';

$error = '';
$applicants = [];

try {

    $sql = "
        SELECT
            a.applicant_id,
            a.first_name,
            a.last_name,
            a.father_name,
            a.email,
            a.phone,
            a.date_of_birth,
            a.application_status,
            p.program_name,
            p.program_code
        FROM applicants a
        LEFT JOIN programs p
            ON a.program_id = p.program_id
        ORDER BY a.applicant_id DESC
    ";

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $applicants[] = $row;
    }

} catch (mysqli_sql_exception $e) {
    $error = "Unable to load applicant records. Please check the database columns.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Applicant Records | UMS</title>

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
                <span class="module-heading-icon">▤</span>
                Applicant Records
            </h1>

            <p>
                View and monitor university admission applications.
            </p>
        </div>

    </section>

    <div class="module-back-row">
        <a href="index.php" class="back-dashboard">
            ← Back to Dashboard
        </a>
    </div>

    <?php if ($error): ?>

        <div class="module-alert error-alert">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <section class="module-card">

        <div class="module-card-heading">

            <div>
                <h2 class="module-card-title">All Applicants</h2>

                <p>
                    Complete list of submitted admission applications.
                </p>
            </div>

            <span class="module-small-tag">
                TOTAL: <?= count($applicants) ?>
            </span>

        </div>

        <?php if (!$error && count($applicants) > 0): ?>

            <div class="module-table-wrap">

                <table class="module-table">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Applicant Name</th>
                            <th>Father Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Date of Birth</th>
                            <th>Program</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($applicants as $applicant): ?>

                            <tr>

                                <td>
                                    <?= (int)$applicant['applicant_id'] ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars(
                                            $applicant['first_name'] . ' ' .
                                            $applicant['last_name']
                                        ) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($applicant['father_name']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($applicant['email']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($applicant['phone']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($applicant['date_of_birth']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $applicant['program_name'] ?? 'Not Assigned'
                                    ) ?>

                                    <?php if (!empty($applicant['program_code'])): ?>
                                        <br>
                                        <small>
                                            <?= htmlspecialchars($applicant['program_code']) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="module-status">
                                        <?= htmlspecialchars($applicant['application_status']) ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php elseif (!$error): ?>

            <div class="module-empty-state">
                <h3>No Applications Found</h3>
                <p>New admission applications will appear here.</p>

                <a href="admission.php" class="module-primary-btn">
                    Add New Applicant →
                </a>
            </div>

        <?php endif; ?>

    </section>

    <footer class="dashboard-footer">
        University Management System · Applicant Records
    </footer>

</main>

</body>
</html>