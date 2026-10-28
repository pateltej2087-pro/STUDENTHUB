<?php

require_once __DIR__ . "/auth.php";
requireRole("admin");
require_once __DIR__ . "/audit.php";

function readEventField($name)
{
    if (isset($_POST[$name]) && is_string($_POST[$name])) {
        return $_POST[$name];
    }

    return "";
}

function showEventErrors($messages, $status)
{
    http_response_code($status);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, initial-scale=1.0">
        <title>StudentHub - Event Error</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body>
    <main>
        <section>
            <h2>Unable to Save Event</h2>

            <?php foreach ($messages as $message) { ?>
                <p>
                    <?= htmlspecialchars(
                        $message,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </p>
            <?php } ?>

            <p>Use your browser Back button to correct the form.</p>

            <a href="../pages/manage_events.php">
                Back to Event Management
            </a>
        </section>
    </main>
    </body>
    </html>
    <?php
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    showEventErrors(["Please submit the event form."], 405);
}

// PHP can discard POST data when the whole request is too large.
if (empty($_POST) && empty($_FILES)) {
    showEventErrors(
        ["No form data received. Your upload may exceed the server limit."],
        413
    );
}

$token = readEventField("csrf_token");

if (
    !isset($_SESSION["event_csrf"]) ||
    !hash_equals($_SESSION["event_csrf"], $token)
) {
    showEventErrors(
        ["Invalid form token. Open the event form again."],
        403
    );
}

$idText = readEventField("event_id");
$id = 0;

if ($idText !== "") {
    $id = filter_var($idText, FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        showEventErrors(["Invalid event ID."], 400);
    }
}

$isEdit = $id > 0;

$eventName = trim(readEventField("eventName"));
$eventDate = readEventField("eventDate");
$venue = trim(readEventField("venue"));
$category = trim(readEventField("category"));
$status = readEventField("status");

$errors = [];

if (
    $eventName === "" ||
    mb_strlen($eventName, "UTF-8") > 150
) {
    $errors[] = "Event name is required and must not exceed 150 characters.";
}

$date = DateTime::createFromFormat("!Y-m-d", $eventDate);

if (!$date || $date->format("Y-m-d") !== $eventDate) {
    $errors[] = "Enter a valid event date.";
}

if (
    $venue === "" ||
    mb_strlen($venue, "UTF-8") > 150
) {
    $errors[] = "Venue is required and must not exceed 150 characters.";
}

if (
    $category === "" ||
    mb_strlen($category, "UTF-8") > 50
) {
    $errors[] = "Category is required and must not exceed 50 characters.";
}

if (!in_array($status, ["open", "closed"], true)) {
    $errors[] = "Select a valid event status.";
}

$hasPoster = false;
$temporaryPath = "";
$extension = "";

if (isset($_FILES["poster"])) {
    $file = $_FILES["poster"];

    if (
        !is_array($file) ||
        !isset($file["error"]) ||
        !is_int($file["error"])
    ) {
        $errors[] = "Invalid poster upload.";

    } elseif ($file["error"] !== UPLOAD_ERR_NO_FILE) {
        if (
            $file["error"] === UPLOAD_ERR_INI_SIZE ||
            $file["error"] === UPLOAD_ERR_FORM_SIZE
        ) {
            $errors[] = "Poster exceeds the allowed upload size.";

        } elseif ($file["error"] !== UPLOAD_ERR_OK) {
            $errors[] = "Poster upload failed. Please choose the file again.";

        } elseif (
            !isset($file["tmp_name"], $file["name"]) ||
            !is_string($file["tmp_name"]) ||
            !is_string($file["name"]) ||
            !is_uploaded_file($file["tmp_name"])
        ) {
            $errors[] = "Invalid uploaded file.";

        } else {
            $temporaryPath = $file["tmp_name"];
            $size = filesize($temporaryPath);

            if ($size === false || $size < 1 || $size > 2 * 1024 * 1024) {
                $errors[] = "Poster must be a non-empty image up to 2 MB.";
            }

            $allowedTypes = [
                "image/jpeg" => "jpg",
                "image/png" => "png",
                "image/webp" => "webp"
            ];

            $mime = (new finfo(FILEINFO_MIME_TYPE))
                ->file($temporaryPath);

            $imageInfo = @getimagesize($temporaryPath);

            $originalExtension = strtolower(
                pathinfo($file["name"], PATHINFO_EXTENSION)
            );

            if (
                !in_array(
                    $originalExtension,
                    ["jpg", "jpeg", "png", "webp"],
                    true
                ) ||
                !isset($allowedTypes[$mime]) ||
                $imageInfo === false ||
                ($imageInfo["mime"] ?? "") !== $mime
            ) {
                $errors[] = "Upload a genuine JPG, PNG or WebP image.";

            } else {
                $extension = $allowedTypes[$mime];

                $expectedExtensions = [$extension];

                if ($mime === "image/jpeg") {
                    $expectedExtensions[] = "jpeg";
                }

                if (!in_array(
                    $originalExtension,
                    $expectedExtensions,
                    true
                )) {
                    $errors[] = "Poster extension does not match its image type.";
                }

                $hasPoster = true;
            }
        }
    }
}

if (!empty($errors)) {
    showEventErrors($errors, 422);
}

require_once __DIR__ . "/db_mysqli.php";

$posterDirectory = __DIR__ . "/../uploads/posters/";
$newPoster = null;
$oldPoster = null;
$newPath = null;
$transactionStarted = false;

try {
    $conn->begin_transaction();
    $transactionStarted = true;

    if ($isEdit) {
        $check = $conn->prepare(
            "SELECT poster_filename
             FROM events
             WHERE event_id = ?
             FOR UPDATE"
        );

        $check->bind_param("i", $id);
        $check->execute();

        $existing = $check->get_result()->fetch_assoc();

        $check->close();

        if (!$existing) {
            $conn->rollback();
            showEventErrors(["Event not found."], 404);
        }

        $oldPoster = $existing["poster_filename"];
    }

    $posterFilename = $oldPoster;

    if ($hasPoster) {
        if (
            !is_dir($posterDirectory) ||
            !is_writable($posterDirectory)
        ) {
            throw new RuntimeException("Poster directory is not writable.");
        }

        $newPoster = bin2hex(random_bytes(16)) . "." . $extension;
        $newPath = $posterDirectory . $newPoster;

        if (!move_uploaded_file($temporaryPath, $newPath)) {
            throw new RuntimeException("Unable to move uploaded poster.");
        }

        $posterFilename = $newPoster;
    }

    if ($isEdit) {
        $stmt = $conn->prepare(
            "UPDATE events
             SET event_name = ?, event_date = ?, venue = ?,
                 category = ?, status = ?, poster_filename = ?
             WHERE event_id = ?"
        );

        $stmt->bind_param(
            "ssssssi",
            $eventName,
            $eventDate,
            $venue,
            $category,
            $status,
            $posterFilename,
            $id
        );

        $resultStatus = "updated";

    } else {
        $stmt = $conn->prepare(
            "INSERT INTO events
             (event_name, event_date, venue,
              category, status, poster_filename)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssss",
            $eventName,
            $eventDate,
            $venue,
            $category,
            $status,
            $posterFilename
        );

        $resultStatus = "added";
    }

    $stmt->execute();

if (!$isEdit) {
    $id = (int) $conn->insert_id;
}

$stmt->close();

$action = "create";
$description = "Created event.";

if ($isEdit) {
    $action = "update";
    $description = "Updated event details.";
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

} catch (Throwable $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }

    if ($newPath !== null && is_file($newPath)) {
        if (!@unlink($newPath)) {
            error_log("Unable to remove failed event upload.");
        }
    }

    error_log($e->getMessage());

    showEventErrors(
        ["Unable to save the event. Please try again later."],
        500
    );
}

// Remove the previous poster only after the database update succeeds.
if (
    $newPoster !== null &&
    is_string($oldPoster) &&
    preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/D', $oldPoster)
) {
    $oldPath = $posterDirectory . $oldPoster;

    if (is_file($oldPath) && !@unlink($oldPath)) {
        error_log("Unable to remove replaced event poster.");
    }
}

$conn->close();

header(
    "Location: ../pages/manage_events.php?message=" . $resultStatus,
    true,
    303
);

exit;