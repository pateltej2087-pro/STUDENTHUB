<?php

require_once __DIR__ . "/common.php";
require_once __DIR__ . "/../audit.php";

requireApiMethod("POST");
checkApiToken();

$data = readApiJson();

if (
    !isset($data["event_id"]) ||
    !is_int($data["event_id"]) ||
    $data["event_id"] < 1
) {
    apiResponse(false, "Invalid event ID.", null, 422);
}

if (($data["confirmed"] ?? false) !== true) {
    apiResponse(
        false,
        "Please confirm event deletion.",
        null,
        422
    );
}

$id = $data["event_id"];

$conn = apiDatabase();

$transactionStarted = false;
$poster = null;

try {
    $conn->begin_transaction();
    $transactionStarted = true;

    $check = $conn->prepare(
        "SELECT poster_filename
         FROM events
         WHERE event_id = ?
         FOR UPDATE"
    );

    $check->bind_param("i", $id);
    $check->execute();

    $event = $check->get_result()->fetch_assoc();

    $check->close();

    if (!$event) {
        $conn->rollback();
        $transactionStarted = false;
        $conn->close();

        apiResponse(false, "Event not found.", null, 404);
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
    "Deleted event through JSON API."
);

$conn->commit();
    $transactionStarted = false;

    $conn->close();

} catch (Throwable $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }

    if (
        $e instanceof mysqli_sql_exception &&
        (int) $e->getCode() === 1451
    ) {
        $conn->close();

        apiResponse(
            false,
            "Cannot delete this event because students have registered for it.",
            null,
            409
        );
    }

    throw $e;
}

// Remove the poster only after database deletion succeeds.
if (
    is_string($poster) &&
    preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/D', $poster)
) {
    $posterPath = __DIR__ . "/../../uploads/posters/" . $poster;

    if (is_file($posterPath) && !@unlink($posterPath)) {
        error_log("Unable to remove poster after API event deletion.");
    }
}

apiResponse(
    true,
    "Event deleted successfully.",
    ["event_id" => $id]
);