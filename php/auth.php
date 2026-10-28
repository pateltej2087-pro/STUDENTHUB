<?php

require_once __DIR__ . "/session.php";

function requireRole($requiredRole)
{
    if (!isset($_SESSION["student_id"])) {
        header("Location: ../pages/login.html");
        exit;
    }

    $timeout = 15 * 60;

    if (
        !isset($_SESSION["last_activity"]) ||
        time() - $_SESSION["last_activity"] >= $timeout
    ) {
        destroyUserSession();

        header("Location: ../pages/login.html?status=timeout");
        exit;
    }

    if (
        !isset($_SESSION["role"]) ||
        $_SESSION["role"] !== $requiredRole
    ) {
        http_response_code(403);

        echo "<h2>Access denied</h2>";
        echo "<p>You do not have permission to access this page.</p>";
        echo '<a href="../index.html">Go to Home</a>';

        exit;
    }

    $_SESSION["last_activity"] = time();
}