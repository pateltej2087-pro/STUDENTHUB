<?php

require_once __DIR__ . "/session.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    http_response_code(405);
    exit("Please use the Logout button.");
}

$submittedToken = $_POST["logout_token"] ?? "";

if (
    !is_string($submittedToken) ||
    !isset($_SESSION["logout_token"]) ||
    !hash_equals($_SESSION["logout_token"], $submittedToken)
) {
    http_response_code(403);
    exit("Invalid logout request.");
}

destroyUserSession();

header("Location: ../pages/login.html?status=logout");
exit;