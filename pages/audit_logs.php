<?php

require_once __DIR__ . "/../php/auth.php";
requireRole("admin");

require_once __DIR__ . "/../php/db_mysqli.php";

function auditText($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}

$logs = [];
$error = "";

try {
    $stmt = $conn->prepare(
        "SELECT a.audit_id,
                a.actor_id,
                s.full_name AS actor_name,
                a.action,
                a.target_type,
                a.target_id,
                a.description,
                a.created_at
         FROM audit_logs AS a
         LEFT JOIN students AS s
             ON a.actor_id = s.student_id
         ORDER BY a.created_at DESC, a.audit_id DESC
         LIMIT 100"
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }

    $stmt->close();

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());

    http_response_code(500);

    $error = "Unable to load audit logs. Please try again.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>StudentHub - Audit Logs</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<header>
    <h1>StudentHub Admin</h1>

    <?php require __DIR__ . "/../php/menu.php"; ?>
</header>

<main>
    <section>
        <h2>Audit Logs</h2>

        <p>
            The latest 100 recorded admin actions are shown below.
            Most recent actions appear first.
        </p>

        <?php if ($error !== "") { ?>

            <p role="alert"><?= auditText($error) ?></p>

        <?php } elseif (empty($logs)) { ?>

            <p>No audit entries found.</p>

        <?php } else { ?>

            <div style="overflow-x: auto;">
                <table>
                    <caption>Recorded Admin Actions</caption>

                    <thead>
                        <tr>
                            <th scope="col">Log ID</th>
                            <th scope="col">Admin</th>
                            <th scope="col">Action</th>
                            <th scope="col">Target</th>
                            <th scope="col">Target ID</th>
                            <th scope="col">Description</th>
                            <th scope="col">Date and Time</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($logs as $log) { ?>
                            <tr>
                                <td>
                                    <?= auditText($log["audit_id"]) ?>
                                </td>

                                <td>
                                    <?php if ($log["actor_id"] === null) { ?>

                                        Deleted admin account

                                    <?php } else { ?>

                                        <?= auditText($log["actor_name"]) ?>
                                        (ID:
                                        <?= auditText($log["actor_id"]) ?>)

                                    <?php } ?>
                                </td>

                                <td>
                                    <?= auditText($log["action"]) ?>
                                </td>

                                <td>
                                    <?= auditText($log["target_type"]) ?>
                                </td>

                                <td>
                                    <?= auditText($log["target_id"]) ?>
                                </td>

                                <td>
                                    <?= auditText($log["description"]) ?>
                                </td>

                                <td>
                                    <?= auditText($log["created_at"]) ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        <?php } ?>
    </section>
</main>

<footer>
    <p>StudentHub &copy; 2026</p>
</footer>

<script src="../js/script.js"></script>

</body>
</html>