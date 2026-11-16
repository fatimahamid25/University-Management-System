
<?php
require_once 'auth.php';
require_once 'db.php';

$username = $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? 'Administrator';

/* Safely count records from allowed database tables */
function getCount($conn, $table) {
    $allowed = [
        'departments', 'programs', 'students', 'teachers',
        'courses', 'semesters', 'course_offerings',
        'enrollments', 'attendance', 'results', 'applicants'
    ];

    if (!in_array($table, $allowed, true)) {
        return 0;
    }

    try {
        $result = $conn->query("SELECT COUNT(*) AS total FROM `$table`");

        if ($result) {
            $row = $result->fetch_assoc();
            return (int)($row['total'] ?? 0);
        }
    } catch (mysqli_sql_exception $e) {
        return 0;
    }

    return 0;
}

/* Dashboard statistics */
$stats = [
    ['label' => 'Total Students', 'table' => 'students', 'icon' => '🎓'],
    ['label' => 'Total Teachers', 'table' => 'teachers', 'icon' => '👨‍🏫'],
    ['label' => 'Departments', 'table' => 'departments', 'icon' => '🏛️'],
    ['label' => 'Courses', 'table' => 'courses', 'icon' => '📚'],
    ['label' => 'Programs', 'table' => 'programs', 'icon' => '📖'],
    ['label' => 'Enrollments', 'table' => 'enrollments', 'icon' => '📝'],
    ['label' => 'Attendance Records', 'table' => 'attendance', 'icon' => '📋'],
    ['label' => 'Applicants', 'table' => 'applicants', 'icon' => '📨']
];

/* Organised management modules */
$moduleGroups = [
    [
        'title' => 'Academic Management',
        'description' => 'Manage the university structure, degree programs and academic resources.',
        'icon' => '🎓',
        'class' => 'academic',
        'modules' => [
            [
                'title' => 'Departments',
                'description' => 'Manage academic departments and their details.',
                'icon' => '🏛️',
                'link' => 'departments.php'
            ],
            [
                'title' => 'Programs',
                'description' => 'Manage degree programs and academic structures.',
                'icon' => '🎓',
                'link' => 'programs.php'
            ],
            [
                'title' => 'Courses',
                'description' => 'Organize course details and information.',
                'icon' => '📚',
                'link' => 'courses.php'
            ],
            [
                'title' => 'Semesters',
                'description' => 'Manage academic years and semesters.',
                'icon' => '📅',
                'link' => 'semesters.php'
            ]
        ]
    ],
    [
        'title' => 'Academic Operations',
        'description' => 'Manage people, course delivery, registrations and academic performance.',
        'icon' => '📊',
        'class' => 'operations',
        'modules' => [
            [
                'title' => 'Students',
                'description' => 'Maintain student information and records.',
                'icon' => '👨‍🎓',
                'link' => 'students.php'
            ],
            [
                'title' => 'Teachers',
                'description' => 'Manage faculty and teaching staff.',
                'icon' => '👨‍🏫',
                'link' => 'teachers.php'
            ],
            [
                'title' => 'Course Offerings',
                'description' => 'Manage course sections and allocations.',
                'icon' => '📖',
                'link' => 'course_offerings.php'
            ],
            [
                'title' => 'Enrollments',
                'description' => 'Manage student course registrations.',
                'icon' => '📝',
                'link' => 'enrollments.php'
            ],
            [
                'title' => 'Attendance',
                'description' => 'Record and monitor student attendance.',
                'icon' => '📋',
                'link' => 'attendance.php'
            ],
            [
                'title' => 'Results',
                'description' => 'Manage examination marks and grades.',
                'icon' => '📊',
                'link' => 'results.php'
            ]
        ]
    ],
    [
        'title' => 'Admissions Management',
        'description' => 'Handle admission applications and maintain applicant information.',
        'icon' => '📨',
        'class' => 'admissions',
        'modules' => [
            [
                'title' => 'Admissions',
                'description' => 'Register and manage admission applications.',
                'icon' => '📨',
                'link' => 'admission.php'
            ],
            [
                'title' => 'Applicant Records',
                'description' => 'View submitted admission applications.',
                'icon' => '📁',
                'link' => 'view_applicants.php'
            ]
        ]
    ]
];

/* Count total modules */
$totalModules = 0;
foreach ($moduleGroups as $group) {
    $totalModules += count($group['modules']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UMS | University Management System</title>
    <link rel="stylesheet" href="ums.css?v=5">
</head>

<body class="dashboard-page">

    <!-- HEADER -->
    <header class="dashboard-nav">

        <a href="index.php" class="dashboard-brand">
            <div class="brand-mark">U</div>

            <div class="brand-details">
                <h2>UMS</h2>
                <span>University Management System</span>
            </div>
        </a>

        <nav class="dashboard-nav-links">
            <a href="index.php" class="active">
                <span class="nav-icon">⌂</span>
                Dashboard
            </a>

            <a href="departments.php">
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

            <a href="logout.php" class="nav-logout">
                <span class="nav-icon">↪</span>
                Logout
            </a>
        </nav>

    </header>

    <main class="dashboard-container">

        <!-- WELCOME BANNER -->
        <section class="welcome-banner">

            <div class="welcome-content">

                <span class="welcome-tag">
                    UNIVERSITY ADMINISTRATION
                </span>

                <h1>
                    Welcome back,
                    <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>
                </h1>

                <p>
                    Manage academic records, university operations
                    and student information from one central platform.
                </p>

                <div class="role-label">
                    <span class="role-dot"></span>
                    <?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?> Account
                </div>

            </div>

            <div class="welcome-decoration">
                <div class="decoration-circle circle-one"></div>
                <div class="decoration-circle circle-two"></div>
                <div class="welcome-symbol">U</div>
            </div>

        </section>

        <!-- COMPACT UNIVERSITY OVERVIEW -->
        <section class="university-overview">

            <div class="overview-content">
                <div class="overview-icon">🏛️</div>

                <div class="overview-text">
                    <h2>University Overview</h2>
                    <p>
                        One platform for academic management,
                        university operations and admissions.
                    </p>
                </div>
            </div>

        </section>

        <!-- COMPACT STATISTICS -->
        <section class="dashboard-stats-section">

            <div class="stats-grid">

                <?php foreach ($stats as $stat): ?>

                    <div class="stat-card">

                        <div class="stat-icon">
                            <?php echo $stat['icon']; ?>
                        </div>

                        <div class="stat-details">

                            <div class="stat-number">
                                <?php echo getCount($conn, $stat['table']); ?>
                            </div>

                            <div class="stat-label">
                                <?php echo htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </section>

        <!-- MANAGEMENT MODULES -->
        <section class="dashboard-section modules-section">

            <div class="section-title">
                <div>
                    <h2>Management Modules</h2>
                    <p>Explore university functions organised by category</p>
                </div>

                <span class="section-tag">
                    <?php echo $totalModules; ?> MODULES
                </span>
            </div>

            <?php foreach ($moduleGroups as $group): ?>

                <section class="module-group <?php echo htmlspecialchars($group['class'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="module-group-heading">

                        <div class="module-group-icon">
                            <?php echo $group['icon']; ?>
                        </div>

                        <div class="module-group-info">
                            <h3>
                                <?php echo htmlspecialchars($group['title'], ENT_QUOTES, 'UTF-8'); ?>
                            </h3>

                            <p>
                                <?php echo htmlspecialchars($group['description'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>

                        <span class="module-group-count">
                            <?php echo count($group['modules']); ?>
                            <?php echo count($group['modules']) === 1 ? 'MODULE' : 'MODULES'; ?>
                        </span>

                    </div>

                    <div class="module-groups-grid">

                        <?php foreach ($group['modules'] as $module): ?>

                            <a
                                href="<?php echo htmlspecialchars($module['link'], ENT_QUOTES, 'UTF-8'); ?>"
                                class="ums-module-card"
                            >

                                <div class="ums-module-icon">
                                    <?php echo $module['icon']; ?>
                                </div>

                                <div class="ums-module-content">

                                    <h4>
                                        <?php echo htmlspecialchars($module['title'], ENT_QUOTES, 'UTF-8'); ?>
                                    </h4>

                                    <p>
                                        <?php echo htmlspecialchars($module['description'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>

                                </div>

                                <span class="ums-module-arrow">↗</span>

                            </a>

                        <?php endforeach; ?>

                    </div>

                </section>

            <?php endforeach; ?>

        </section>

        <!-- FOOTER -->
        <footer class="dashboard-footer">
            <span>© 2026 University Management System</span>
            <span>Academic Administration Portal</span>
        </footer>

    </main>

</body>
</html>
