<?php

ini_set("display_errors", "0");

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");
header("X-Content-Type-Options: nosniff");

function publicEventResponse($success, $message, $data, $status)
{
    http_response_code($status);

    echo json_encode(
        [
            "success" => $success,
            "message" => $message,
            "data" => $data
        ],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Allow: GET");

    publicEventResponse(
        false,
        "Request method not allowed.",
        null,
        405
    );
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conn = new mysqli(
        "localhost",
        "root",
        "",
        "studenthub"
    );

    $conn->set_charset("utf8mb4");

    $stmt = $conn->prepare(
        "SELECT event_id, event_name, event_date,
                venue, category, status, poster_filename
         FROM events
         ORDER BY event_date ASC, event_id ASC"
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $events = [];

    while ($row = $result->fetch_assoc()) {
        $poster = null;

        if (
            is_string($row["poster_filename"]) &&
            preg_match(
                '/^[a-f0-9]{32}\.(jpg|png|webp)$/D',
                $row["poster_filename"]
            )
        ) {
            $poster = $row["poster_filename"];
        }

        $events[] = [
            "id" => (int) $row["event_id"],
            "title" => $row["event_name"],
            "date" => $row["event_date"],
            "category" => $row["category"],
            "description" => "Venue: " . $row["venue"],
            "status" => $row["status"],
            "poster_filename" => $poster
        ];
    }

    $stmt->close();
    $conn->close();

    publicEventResponse(
        true,
        "Events loaded successfully.",
        $events,
        200
    );

} catch (Throwable $e) {
    error_log((string) $e);

    publicEventResponse(
        false,
        "Unable to load events. Please try again later.",
        null,
        500
    );
}