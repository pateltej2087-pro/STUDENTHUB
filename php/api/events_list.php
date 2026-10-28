<?php

require_once __DIR__ . "/common.php";

requireApiMethod("GET");

$search = "";

if (isset($_GET["search"])) {
    if (!is_string($_GET["search"])) {
        apiResponse(false, "Invalid search value.", null, 422);
    }

    $search = trim($_GET["search"]);

    if (mb_strlen($search, "UTF-8") > 150) {
        apiResponse(
            false,
            "Search must not exceed 150 characters.",
            null,
            422
        );
    }
}

$searchValue = "%" . $search . "%";

$conn = apiDatabase();

$stmt = $conn->prepare(
    "SELECT event_id, event_name, event_date,
            venue, category, status
     FROM events
     WHERE event_name LIKE ?
        OR venue LIKE ?
        OR category LIKE ?
     ORDER BY event_date DESC, event_id DESC"
);

$stmt->bind_param(
    "sss",
    $searchValue,
    $searchValue,
    $searchValue
);

$stmt->execute();

$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {
    $row["event_id"] = (int) $row["event_id"];
    $events[] = $row;
}

$stmt->close();
$conn->close();

apiResponse(
    true,
    "Events loaded successfully.",
    $events
);