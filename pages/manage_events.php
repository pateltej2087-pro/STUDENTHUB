<?php

require_once __DIR__ . "/../php/auth.php";
requireRole("admin");

if (!isset($_SESSION["event_csrf"])) {
    $_SESSION["event_csrf"] = bin2hex(random_bytes(32));
}

require_once __DIR__ . "/../php/db_mysqli.php";

function escapeEventText($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}

function getEventFilter($name)
{
    if (isset($_GET[$name]) && is_string($_GET[$name])) {
        return trim($_GET[$name]);
    }

    return "";
}

$category = substr(getEventFilter("category"), 0, 50);
$status = getEventFilter("status");

if (!in_array($status, ["open", "closed"], true)) {
    $status = "";
}

$events = [];
$categories = [];
$error = "";

try {
    $categoryStatement = $conn->prepare(
        "SELECT DISTINCT category
         FROM events
         ORDER BY category"
    );

    $categoryStatement->execute();

    $categoryResult = $categoryStatement->get_result();

    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row["category"];
    }

    $categoryStatement->close();

    $statement = $conn->prepare(
        "SELECT event_id, event_name, event_date,
                venue, category, status, poster_filename
         FROM events
         WHERE (? = '' OR category = ?)
           AND (? = '' OR status = ?)
         ORDER BY event_date DESC, event_id DESC"
    );

    $statement->bind_param(
        "ssss",
        $category,
        $category,
        $status,
        $status
    );

    $statement->execute();

    $result = $statement->get_result();

    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    $statement->close();

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());

    http_response_code(500);

    $error = "Unable to load events. Please try again.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>StudentHub - Event Management</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<header>
    <h1>StudentHub Admin</h1>

    <nav aria-label="Admin Navigation">
        <a href="admin.php">Admin Dashboard</a>
        <a href="students.php">Manage Students</a>
        <a href="manage_events.php">Manage Events</a>

        <form action="../php/logout.php" method="POST">
            <input
                type="hidden"
                name="logout_token"
                value="<?= escapeEventText(
                    $_SESSION['logout_token']
                ) ?>"
            >

            <button type="submit">Logout</button>
        </form>
    </nav>
</header>

<main>
    <section>
        <h2>Event Management</h2>

        <?php
$eventMessages = [
    "added" => "Event added successfully.",
    "updated" => "Event updated successfully.",
    "deleted" => "Event deleted successfully.",
    "linked" => "Cannot delete this event because students have registered for it.",
    "delete_error" => "Unable to delete the event. Please try again."
];

$messageKey = getEventFilter("message");
?>

<?php if (isset($eventMessages[$messageKey])) { ?>
    <p class="success-message" role="status">
        <?= escapeEventText($eventMessages[$messageKey]) ?>
    </p>
<?php } ?>

        <p>
    <a href="event_form.php">Add Event</a>
</p>

        <form action="manage_events.php" method="GET">

            <div class="form-group">
                <label for="categoryFilter">Category</label>

                <select id="categoryFilter" name="category">
                    <option value="">All Categories</option>

                    <?php foreach ($categories as $option) { ?>
                        <option
                            value="<?= escapeEventText($option) ?>"
                            <?php
                            if ($category === $option) {
                                echo "selected";
                            }
                            ?>
                        >
                            <?= escapeEventText($option) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="statusFilter">Status</label>

                <select id="statusFilter" name="status">
                    <option value="">All Statuses</option>

                    <option
                        value="open"
                        <?php
                        if ($status === "open") {
                            echo "selected";
                        }
                        ?>
                    >
                        Open
                    </option>

                    <option
                        value="closed"
                        <?php
                        if ($status === "closed") {
                            echo "selected";
                        }
                        ?>
                    >
                        Closed
                    </option>
                </select>
            </div>

            <button type="submit">Apply Filters</button>

            <a href="manage_events.php">Reset</a>
        </form>

        <?php if ($error !== "") { ?>

            <p role="alert">
                <?= escapeEventText($error) ?>
            </p>

        <?php } else { ?>

            <p>
                Matching events:
                <?= escapeEventText(count($events)) ?>
            </p>

            <?php if (empty($events)) { ?>

                <p>No events found.</p>

            <?php } else { ?>

                <div style="overflow-x: auto;">
                    <table>
                        <caption>Event Records</caption>

                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Event Name</th>
                                <th scope="col">Date</th>
                                <th scope="col">Venue</th>
                                <th scope="col">Category</th>
                                <th scope="col">Status</th>
                                <th scope="col">Poster</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($events as $event) { ?>
                                <tr>
                                    <td>
                                        <?= escapeEventText(
                                            $event["event_id"]
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= escapeEventText(
                                            $event["event_name"]
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= escapeEventText(
                                            $event["event_date"]
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= escapeEventText(
                                            $event["venue"]
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= escapeEventText(
                                            $event["category"]
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= escapeEventText(
                                            ucfirst($event["status"])
                                        ) ?>
                                    </td>

                                    <td>
                                        <?php
                                        $poster = $event["poster_filename"];

                                        if (
                                            is_string($poster) &&
                                            preg_match(
                                                '/^[a-f0-9]{32}\.(jpg|png|webp)$/D',
                                                $poster
                                            )
                                        ) {
                                        ?>
                                            <img
                                                src="../uploads/posters/<?= escapeEventText($poster) ?>"
                                                alt="<?= escapeEventText(
                                                    $event["event_name"] . " poster"
                                                ) ?>"
                                                width="120"
                                                loading="lazy"
                                            >
                                        <?php } else { ?>
                                            No poster
                                                                                <?php } ?>
                                    </td>

                                    <td>
    <a href="event_form.php?id=<?= escapeEventText(
        $event['event_id']
    ) ?>">
        Edit
    </a>

    <form action="../php/delete_event.php" method="POST">
        <input
            type="hidden"
            name="event_id"
            value="<?= escapeEventText($event['event_id']) ?>"
        >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= escapeEventText($_SESSION['event_csrf']) ?>"
        >

        <button type="submit">Delete</button>
    </form>
</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            <?php } ?>

        <?php } ?>
    </section>
</main>

<footer>
    <p>StudentHub &copy; 2026</p>
</footer>

<script src="../js/script.js"></script>

</body>
</html>