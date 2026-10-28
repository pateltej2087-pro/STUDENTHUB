<?php

require_once __DIR__ . "/common.php";
require_once __DIR__ . "/../audit.php";

requireApiMethod("POST");
checkApiToken();

$data = readApiJson();

function eventApiText($data, $key)
{
    if (isset($data[$key]) && is_string($data[$key])) {
        return trim($data[$key]);
    }

    return "";
}

$id = 0;

if (array_key_exists("event_id", $data)) {
    if (!is_int($data["event_id"]) || $data["event_id"] < 0) {
        apiResponse(false, "Invalid event ID.", null, 422);
    }

    $id = $data["event_id"];
}

$isEdit = $id > 0;

$eventName = eventApiText($data, "event_name");
$eventDate = eventApiText($data, "event_date");
$venue = eventApiText($data, "venue");
$category = eventApiText($data, "category");
$status = eventApiText($data, "status");

$errors = [];

if (
    $eventName === "" ||
    mb_strlen($eventName, "UTF-8") > 150
) {
    $errors[] = "Event name is required, up to 150 characters.";
}

$date = DateTime::createFromFormat("!Y-m-d", $eventDate);

if (
    !$date ||
    $date->format("Y-m-d") !== $eventDate ||
    $date->format("Y") < 1000
) {
    $errors[] = "Enter a valid date with a year from 1000 to 9999.";
}

if ($venue === "" || mb_strlen($venue, "UTF-8") > 150) {
    $errors[] = "Venue is required, up to 150 characters.";
}

if ($category === "" || mb_strlen($category, "UTF-8") > 50) {
    $errors[] = "Category is required, up to 50 characters.";
}

if (!in_array($status, ["open", "closed"], true)) {
    $errors[] = "Select Open or Closed.";
}

if (!empty($errors)) {
    apiResponse(
        false,
        "Please correct the event details.",
        ["errors" => $errors],
        422
    );
}

$conn = apiDatabase();
$transactionStarted = false;

try {
    $conn->begin_transaction();
    $transactionStarted = true;

    if ($isEdit) {
        $check = $conn->prepare(
            "SELECT event_id
             FROM events
             WHERE event_id = ?
             FOR UPDATE"
        );

        $check->bind_param("i", $id);
        $check->execute();
        $check->store_result();

        $exists = $check->num_rows > 0;

        $check->close();

        if (!$exists) {
            $conn->rollback();
            $transactionStarted = false;
            $conn->close();

            apiResponse(false, "Event not found.", null, 404);
        }

        $stmt = $conn->prepare(
            "UPDATE events
             SET event_name = ?, event_date = ?, venue = ?,
                 category = ?, status = ?
             WHERE event_id = ?"
        );

        $stmt->bind_param(
            "sssssi",
            $eventName,
            $eventDate,
            $venue,
            $category,
            $status,
            $id
        );

        $message = "Event updated successfully.";
        $responseStatus = 200;

    } else {
        $stmt = $conn->prepare(
            "INSERT INTO events
             (event_name, event_date, venue, category, status)
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssss",
            $eventName,
            $eventDate,
            $venue,
            $category,
            $status
        );

        $message = "Event added successfully.";
        $responseStatus = 201;
    }

    $stmt->execute();

    if (!$isEdit) {
        $id = (int) $conn->insert_id;
    }

    $stmt->close();

$action = "create";
$description = "Created event through JSON API.";

if ($isEdit) {
    $action = "update";
    $description = "Updated event through JSON API.";
}

recordAudit(
    $conn,
    $action,
    "event",
    $id,
    $description
);

$conn->commit();
    $transactionStarted = false;

    $conn->close();

} catch (Throwable $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }

    throw $e;
}

apiResponse(
    true,
    $message,
    ["event_id" => $id],
    $responseStatus
);