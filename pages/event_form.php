<?php

require_once __DIR__ . "/../php/auth.php";
requireRole("admin");

require_once __DIR__ . "/../php/db_mysqli.php";

function escapeEventText($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}

if (!isset($_SESSION["event_csrf"])) {
    $_SESSION["event_csrf"] = bin2hex(random_bytes(32));
}

$event = [
    "event_id" => "",
    "event_name" => "",
    "event_date" => "",
    "venue" => "",
    "category" => "",
    "status" => "open",
    "poster_filename" => ""
];

$isEdit = false;

if (isset($_GET["id"])) {
    $id = filter_var($_GET["id"], FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        http_response_code(400);
        exit("Invalid event ID.");
    }

    try {
        $stmt = $conn->prepare(
            "SELECT event_id, event_name, event_date,
                    venue, category, status, poster_filename
             FROM events
             WHERE event_id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $record = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if (!$record) {
            http_response_code(404);
            exit("Event not found.");
        }

        $event = $record;
        $isEdit = true;

    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        exit("Unable to load the event.");
    }
}

$conn->close();

$title = "Add Event";

if ($isEdit) {
    $title = "Edit Event";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>StudentHub - <?= escapeEventText($title) ?></title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<header>
    <h1>StudentHub Admin</h1>

    <nav aria-label="Admin Navigation">
        <a href="admin.php">Admin Dashboard</a>
        <a href="manage_events.php">Manage Events</a>
    </nav>
</header>

<main>
    <section>
        <h2><?= escapeEventText($title) ?></h2>

        <form action="../php/save_event.php"
              method="POST"
              enctype="multipart/form-data">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= escapeEventText($_SESSION['event_csrf']) ?>"
            >

            <input
                type="hidden"
                name="event_id"
                value="<?= escapeEventText($event['event_id']) ?>"
            >

            <div class="form-group">
                <label for="eventName">Event Name</label>

                <input
                    type="text"
                    id="eventName"
                    name="eventName"
                    maxlength="150"
                    value="<?= escapeEventText($event['event_name']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="eventDate">Event Date</label>

                <input
                    type="date"
                    id="eventDate"
                    name="eventDate"
                    value="<?= escapeEventText($event['event_date']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="venue">Venue</label>

                <input
                    type="text"
                    id="venue"
                    name="venue"
                    maxlength="150"
                    value="<?= escapeEventText($event['venue']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="category">Category</label>

                <input
                    type="text"
                    id="category"
                    name="category"
                    maxlength="50"
                    placeholder="Example: Technical, Cultural or Sports"
                    value="<?= escapeEventText($event['category']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="status">Status</label>

                <select id="status" name="status" required>
                    <option
                        value="open"
                        <?php
                        if ($event["status"] === "open") {
                            echo "selected";
                        }
                        ?>
                    >
                        Open
                    </option>

                    <option
                        value="closed"
                        <?php
                        if ($event["status"] === "closed") {
                            echo "selected";
                        }
                        ?>
                    >
                        Closed
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="poster">Event Poster (Optional)</label>

                <input
                    type="file"
                    id="poster"
                    name="poster"
                    accept=".jpg,.jpeg,.png,.webp"
                    aria-describedby="posterHelp"
                >

                <p id="posterHelp">
                    JPG, PNG or WebP only. Maximum size: 2 MB.
                    When editing, leave this field empty to keep
                    the existing poster.
                </p>

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
                    <p>Current poster:</p>

                    <img
                        src="../uploads/posters/<?= escapeEventText($poster) ?>"
                        alt="Current event poster"
                        width="180"
                    >
                <?php } ?>
            </div>

            <button type="submit">Save Event</button>

            <a href="manage_events.php">Cancel</a>
        </form>
    </section>
</main>

<footer>
    <p>StudentHub &copy; 2026</p>
</footer>

<script src="../js/script.js"></script>

</body>
</html>