<?php

require_once __DIR__ . "/session.php";

function returnToLogin($status)
{
    header("Location: ../pages/login.html?status=" . $status);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/login.html");
    exit;
}

$email = "";
$password = "";

if (
    isset($_POST["loginEmail"]) &&
    is_string($_POST["loginEmail"])
) {
    $email = trim($_POST["loginEmail"]);
}

if (
    isset($_POST["loginPassword"]) &&
    is_string($_POST["loginPassword"])
) {
    $password = $_POST["loginPassword"];
}

if ($email === "" || $password === "") {
    returnToLogin("required");
}

if (strlen($email) > 254 || strlen($password) > 72) {
    returnToLogin("invalid");
}

require_once __DIR__ . "/db_mysqli.php";

try {
    $stmt = $conn->prepare(
        "SELECT student_id, full_name, password_hash, role
         FROM students
         WHERE email = ?"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $stmt->bind_result(
        $studentId,
        $fullName,
        $passwordHash,
        $role
    );

    $found = $stmt->fetch();

    $stmt->close();

    // A fixed dummy hash keeps password verification
    // in the flow even when the email is not found.
    $hashToCheck =
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

    if ($found) {
        $hashToCheck = $passwordHash;
    }

    $passwordMatches = password_verify(
        $password,
        $hashToCheck
    );

    if (!$found || !$passwordMatches) {
        $conn->close();
        returnToLogin("invalid");
    }

    if (!in_array($role, ["student", "admin"], true)) {
        $conn->close();
        returnToLogin("invalid");
    }

    $update = $conn->prepare(
        "UPDATE students
         SET last_login = NOW()
         WHERE student_id = ?"
    );

    $update->bind_param("i", $studentId);
    $update->execute();
    $update->close();

    $conn->close();

    // Replace the old session ID after successful login.
    if (!session_regenerate_id(true)) {
        destroyUserSession();
        returnToLogin("error");
    }

    $_SESSION = [];

    $_SESSION["student_id"] = $studentId;
    $_SESSION["full_name"] = $fullName;
    $_SESSION["role"] = $role;
    $_SESSION["last_activity"] = time();

    $_SESSION["logout_token"] =
        bin2hex(random_bytes(32));

    if ($role === "admin") {
        header("Location: ../pages/admin.php");
        exit;
    }

    header("Location: ../pages/dashboard.php");
    exit;

} catch (Throwable $e) {
    error_log($e->getMessage());
    returnToLogin("error");
}