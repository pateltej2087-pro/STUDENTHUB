<?php

require_once __DIR__ . "/auth.php";
requireRole("admin");
require_once __DIR__ . "/audit.php";

function stopEventDelete($message, $status)
{
    http_response_code($status);

    echo "<p>" . htmlspecialchars(
        $message,
        ENT_QUOTES,
        "UTF-8"
    ) . "</p>";

    echo '<a href="../pages/manage_events.php">
        Back to Event Management
    </a>';

    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    stopEventDelete("Please use the Delete button.", 405);
}

$token = $_POST["csrf_token"] ?? "";

if (
    !is_string($token) ||
    !isset($_SESSION["event_csrf"]) ||
    !hash_equals($_SESSION["event_csrf"], $token)
) {
    stopEventDelete("Invalid request. Open the event list again.", 403);
}

$idValue = $_POST["event_id"] ?? "";

if (!is_string($idValue)) {
    stopEventDelete("Invalid event ID.", 400);
}

$id = filter_var($idValue, FILTER_VALIDATE_INT);

if ($id === false || $id < 1) {
    stopEventDelete("Invalid event ID.", 400);
}

$confirmed = $_POST["confirmed"] ?? "";

if (!is_string($confirmed)) {
    stopEventDelete("Invalid confirmation.", 400);
}

if ($confirmed !== "" && $confirmed !== "yes") {
    stopEventDelete("Invalid confirmation.", 400);
}

require_once __DIR__ . "/db_mysqli.php";

$transactionStarted = false;
$poster = null;

try {
    if ($confirmed === "yes") {
        $conn->begin_transaction();
        $transactionStarted = true;
    }

    $sql = "SELECT event_name, poster_filename
            FROM events
            WHERE event_id = ?";

    if ($confirmed === "yes") {
        $sql .= " FOR UPDATE";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $event = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$event) {
        if ($transactionStarted) {
            $conn->rollback();
        }

        stopEventDelete("Event not found.", 404);
    }

    if ($confirmed !== "yes") {
        $conn->close();
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">

            <meta name="viewport"
                  content="width=device-width, initial-scale=1.0">

            <title>StudentHub - Confirm Event Deletion</title>

            <link rel="stylesheet" href="../css/style.css">
        </head>

        <body>
        <main>
            <section>
                <h2>Confirm Event Deletion</h2>

                <p>
                    Delete
                    <?= htmlspecialchars(
                        $event["event_name"],
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>?
                </p>

                <p>
                    This permanently deletes the event and its poster.
                    Events with student registrations cannot be deleted.
                </p>

                <form action="delete_event.php" method="POST">
                    <input
                        type="hidden"
                        name="event_id"
                        value="<?= (int) $id ?>"
                    >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                            $token,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>"
                    >

                    <input
                        type="hidden"
                        name="confirmed"
                        value="yes"
                    >

                    <button type="submit">Yes, Delete</button>

                    <a href="../pages/manage_events.php">Cancel</a>
                </form>
            </section>
        </main>
        </body>
        </html>
        <?php
        exit;
    }

    $poster = $event["poster_filename"];

    $delete = $conn->prepare(
        "DELETE FROM events WHERE event_id = ?"
    );

    $delete->bind_param("i", $id);

    $delete->execute();
$delete->close();

recordAudit(
    $conn,
    "delete",
    "event",
    $id,
    "Deleted event."
);

$conn->commit();
    $transactionStarted = false;

    $resultStatus = "deleted";

} catch (Throwable $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }

    error_log($e->getMessage());

    $resultStatus = "delete_error";

    if (
        $e instanceof mysqli_sql_exception &&
        (int) $e->getCode() === 1451
    ) {
        $resultStatus = "linked";
    }
}

// Remove the poster only after successful database deletion.
if (
    $resultStatus === "deleted" &&
    is_string($poster) &&
    preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/D', $poster)
) {
    $posterPath = __DIR__ . "/../uploads/posters/" . $poster;

    if (is_file($posterPath) && !@unlink($posterPath)) {
        error_log("Unable to remove deleted event poster.");
    }
}

$conn->close();

header(
    "Location: ../pages/manage_events.php?message=" . $resultStatus,
    true,
    303
);

exit;