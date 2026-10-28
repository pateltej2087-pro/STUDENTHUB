<?php
$menuRole = $_SESSION["role"] ?? "";
?>

<nav aria-label="Main Navigation">
    <a href="../index.html">Home</a>

    <?php if ($menuRole === "admin") { ?>

        <a href="admin.php">Admin Dashboard</a>
        <a href="students.php">Manage Students</a>
        <a href="manage_events.php">Manage Events</a>
        <a href="ajax_events.php">AJAX Events</a>
        <a href="audit_logs.php">Audit Logs</a>

    <?php } elseif ($menuRole === "student") { ?>

        <a href="dashboard.php">Student Dashboard</a>
        <a href="profile.html">Profile</a>
        <a href="events.html">Events</a>

    <?php } ?>

    <?php if (in_array($menuRole, ["admin", "student"], true)) { ?>

        <form action="../php/logout.php" method="POST">
            <input
                type="hidden"
                name="logout_token"
                value="<?= htmlspecialchars(
                    $_SESSION["logout_token"] ?? "",
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>"
            >

            <button type="submit">Logout</button>
        </form>

    <?php } else { ?>

        <a href="login.html">Login</a>
        <a href="register.html">Register</a>

    <?php } ?>
</nav>