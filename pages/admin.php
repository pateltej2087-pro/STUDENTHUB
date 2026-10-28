<?php

require_once __DIR__ . "/../php/auth.php";
requireRole("admin");

require_once __DIR__ . "/../php/db_mysqli.php";

function dashboardText($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}

$stats = [
    "accounts" => 0,
    "students" => 0,
    "admins" => 0,
    "events" => 0,
    "registrations" => 0
];

$latestRegistrations = [];
$error = "";

try {
    $users = $conn->prepare(
        "SELECT COUNT(*) AS accounts,
                COALESCE(SUM(role = 'student'), 0) AS students,
                COALESCE(SUM(role = 'admin'), 0) AS admins
         FROM students"
    );

    $users->execute();

    $userCounts = $users->get_result()->fetch_assoc();

    $stats["accounts"] = (int) $userCounts["accounts"];
    $stats["students"] = (int) $userCounts["students"];
    $stats["admins"] = (int) $userCounts["admins"];

    $users->close();

    $events = $conn->prepare(
        "SELECT COUNT(*) AS total FROM events"
    );

    $events->execute();

    $stats["events"] = (int) $events
        ->get_result()
        ->fetch_assoc()["total"];

    $events->close();

    $registrations = $conn->prepare(
        "SELECT COUNT(*) AS total FROM registrations"
    );

    $registrations->execute();

    $stats["registrations"] = (int) $registrations
        ->get_result()
        ->fetch_assoc()["total"];

    $registrations->close();

    $latest = $conn->prepare(
        "SELECT r.registration_id,
                s.full_name,
                e.event_name,
                r.registered_at
         FROM registrations AS r
         INNER JOIN students AS s
             ON r.student_id = s.student_id
         INNER JOIN events AS e
             ON r.event_id = e.event_id
         ORDER BY r.registered_at DESC,
                  r.registration_id DESC
         LIMIT 5"
    );

    $latest->execute();

    $result = $latest->get_result();

    while ($row = $result->fetch_assoc()) {
        $latestRegistrations[] = $row;
    }

    $latest->close();

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());

    http_response_code(500);

    $error = "Unable to load dashboard data. Please try again.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>StudentHub - Admin Dashboard</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<header>
    <h1>StudentHub Admin</h1>

    <?php require __DIR__ . "/../php/menu.php"; ?>
</header>

<main>
    <section>
        <h2>Admin Dashboard</h2>

        <p>
            Welcome,
            <?= dashboardText($_SESSION["full_name"]) ?>!
        </p>

        <?php if ($error !== "") { ?>

            <p role="alert"><?= dashboardText($error) ?></p>

        <?php } else { ?>

            <div class="dashboard-cards">

                <article class="dashboard-card">
                    <h3>Total User Accounts</h3>
                    <p><?= dashboardText($stats["accounts"]) ?></p>
                </article>

                <article class="dashboard-card">
                    <h3>Students</h3>
                    <p><?= dashboardText($stats["students"]) ?></p>
                </article>

                <article class="dashboard-card">
                    <h3>Admins</h3>
                    <p><?= dashboardText($stats["admins"]) ?></p>
                </article>

                <article class="dashboard-card">
                    <h3>Events</h3>
                    <p><?= dashboardText($stats["events"]) ?></p>
                </article>

                <article class="dashboard-card">
                    <h3>Event Registrations</h3>
                    <p><?= dashboardText($stats["registrations"]) ?></p>
                </article>

            </div>

            <h2>Latest Event Registrations</h2>

            <?php if (empty($latestRegistrations)) { ?>

                <p>No event registrations yet.</p>

            <?php } else { ?>

                <div style="overflow-x: auto;">
                    <table>
                        <caption>
                            Five Most Recent Event Registrations
                        </caption>

                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Student</th>
                                <th scope="col">Event</th>
                                <th scope="col">Registered At</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($latestRegistrations as $row) { ?>
                                <tr>
                                    <td>
                                        <?= dashboardText(
                                            $row["registration_id"]
                                        ) ?>
                                    </td>
                                    <td>
                                        <?= dashboardText(
                                            $row["full_name"]
                                        ) ?>
                                    </td>
                                    <td>
                                        <?= dashboardText(
                                            $row["event_name"]
                                        ) ?>
                                    </td>
                                    <td>
                                        <?= dashboardText(
                                            $row["registered_at"]
                                        ) ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            <?php } ?>

        <?php } ?>
    </section>

    <section>
        <h2>Management Options</h2>

        <article>
            <h3>Manage Students</h3>
            <p>Add, view, edit, delete, search and filter students.</p>
            <a href="students.php">Open Student Management</a>
        </article>

        <article>
            <h3>Manage Faculty</h3>
            <p>View and manage faculty records.</p>
        </article>

        <article>
            <h3>Manage Courses</h3>
            <p>Manage course information.</p>
        </article>

        <article>
            <h3>Manage Notices</h3>
            <p>Create and manage important notices.</p>
        </article>

        <article>
            <h3>Manage Events</h3>
            <p>Add, edit, delete events and upload posters.</p>

            <a href="manage_events.php">Open Event Management</a>

            <p>
                <a href="ajax_events.php">
                    Open AJAX Event Management
                </a>
            </p>
        </article>
    </section>
</main>

<footer>
    <p>StudentHub &copy; 2026</p>
</footer>

<script src="../js/script.js"></script>

</body>
</html>