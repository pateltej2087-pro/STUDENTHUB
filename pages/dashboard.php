<?php
require_once __DIR__ . "/../php/auth.php";
requireRole("student");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>StudentHub - Dashboard</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<header>
    <h1>StudentHub</h1>

    <?php require __DIR__ . "/../php/menu.php"; ?>
</header>

<main>
    <div class="dashboard-layout">

        <aside class="sidebar">
            <h2>Dashboard</h2>

            <a href="dashboard.php">Dashboard</a>
            <a href="profile.html">Profile</a>
            <a href="#">Courses</a>
            <a href="#">Attendance</a>
            <a href="#">Assignments</a>
            <a href="#">Timetable</a>
            <a href="#">Results</a>
            <a href="events.html">Events</a>

            <form action="../php/logout.php" method="POST">
                <input
                    type="hidden"
                    name="logout_token"
                    value="<?= htmlspecialchars(
                        $_SESSION['logout_token'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <button type="submit">Logout</button>
            </form>
        </aside>

        <section class="dashboard-content">
            <h2>Student Dashboard</h2>

            <p>
                Welcome,
                <?= htmlspecialchars(
                    $_SESSION["full_name"],
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>!
            </p>

            <div class="dashboard-cards">

                <article class="dashboard-card">
                    <h3>Courses</h3>
                    <p>6 Active Courses</p>
                </article>

                <article class="dashboard-card">
                    <h3>Attendance</h3>
                    <p>85%</p>
                </article>

                <article class="dashboard-card">
                    <h3>Assignments</h3>
                    <p>3 Pending</p>
                </article>

                <article class="dashboard-card">
                    <h3>Results</h3>
                    <p>View Results</p>
                </article>

            </div>

            <section>
                <h2>Important Notices</h2>

                <p>
                    Check the upcoming college events
                    and academic notices.
                </p>
            </section>
        </section>

    </div>
</main>

<footer>
    <p>StudentHub &copy; 2026</p>
</footer>

<script src="../js/script.js"></script>

</body>
</html>