<?php

ini_set("display_errors", "0");

require_once __DIR__ . "/../session.php";

header("Content-Type: application/json; charset=utf-8");
header("X-Content-Type-Options: nosniff");

function apiResponse($success, $message, $data, $status = 200)
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

// Unexpected errors receive a JSON response.
set_exception_handler(function ($error) {
    error_log((string) $error);

    apiResponse(
        false,
        "Unable to complete the request. Please try again.",
        null,
        500
    );
});

if (!isset($_SESSION["student_id"])) {
    apiResponse(false, "Please log in again.", null, 401);
}

$timeout = 15 * 60;

if (
    !isset($_SESSION["last_activity"]) ||
    time() - $_SESSION["last_activity"] >= $timeout
) {
    destroyUserSession();

    apiResponse(
        false,
        "Your session expired. Please log in again.",
        null,
        401
    );
}

if (($_SESSION["role"] ?? "") !== "admin") {
    apiResponse(false, "Admin access required.", null, 403);
}

$_SESSION["last_activity"] = time();

function requireApiMethod($method)
{
    if ($_SERVER["REQUEST_METHOD"] !== $method) {
        header("Allow: " . $method);

        apiResponse(
            false,
            "Request method not allowed.",
            null,
            405
        );
    }
}

function checkApiToken()
{
    $token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

    if (
        !is_string($token) ||
        !isset($_SESSION["event_csrf"]) ||
        !hash_equals($_SESSION["event_csrf"], $token)
    ) {
        apiResponse(
            false,
            "Invalid form token. Refresh the page and try again.",
            null,
            403
        );
    }
}

function readApiJson()
{
    $contentType = $_SERVER["CONTENT_TYPE"] ?? "";

    $mediaType = strtolower(
        trim(explode(";", $contentType)[0])
    );

    if ($mediaType !== "application/json") {
        apiResponse(
            false,
            "Send the request as application/json.",
            null,
            415
        );
    }

    $body = file_get_contents("php://input");

    if ($body === false || strlen($body) > 16384) {
        apiResponse(false, "Invalid or oversized request.", null, 413);
    }

    try {
        $data = json_decode(
            $body,
            false,
            32,
            JSON_THROW_ON_ERROR
        );
    } catch (JsonException $e) {
        apiResponse(false, "Invalid JSON data.", null, 400);
    }

    if (!is_object($data)) {
        apiResponse(false, "A JSON object is required.", null, 400);
    }

    return (array) $data;
}

function apiDatabase()
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conn = new mysqli(
        "localhost",
        "root",
        "",
        "studenthub"
    );

    $conn->set_charset("utf8mb4");

    return $conn;
}