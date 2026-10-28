<?php

require_once __DIR__ . "/auth.php";
requireRole("admin");
require_once __DIR__ . "/audit.php";

function stopDelete($message, $status)
{
    http_response_code($status);

    echo "<p>" . htmlspecialchars(
        $message,
        ENT_QUOTES,
        "UTF-8"
    ) . "</p>";

    echo '<a href="../pages/students.php">Back to Students</a>';

    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    stopDelete("Please use the Delete button.", 405);
}

$token = $_POST["csrf_token"] ?? "";

if (
    !is_string($token) ||
    !isset($_SESSION["student_csrf"]) ||
    !hash_equals($_SESSION["student_csrf"], $token)
) {
    stopDelete("Invalid request. Open the student list again.", 403);
}

$idValue = $_POST["student_id"] ?? "";

if (!is_string($idValue)) {
    stopDelete("Invalid student ID.", 400);
}

$id = filter_var($idValue, FILTER_VALIDATE_INT);

if ($id === false || $id < 1) {
    stopDelete("Invalid student ID.", 400);
}

require_once __DIR__ . "/db_mysqli.php";
$transactionStarted = false;

try {
    // Display a confirmation page before deleting.
    if (!isset($_POST["confirmed"])) {
        $stmt = $conn->prepare(
            "SELECT full_name, email
             FROM students
             WHERE student_id = ? AND role = 'student'"
        );

        $conn->begin_transaction();
$transactionStarted = true;

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $student = $stmt->get_result()->fetch_assoc();

        $stmt->close();
        $conn->close();

        if (!$student) {
            stopDelete("Student not found.", 404);
        }

        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">

            <meta name="viewport"
                  content="width=device-width, initial-scale=1.0">

            <title>StudentHub - Confirm Delete</title>

            <link rel="stylesheet" href="../css/style.css">
        </head>

        <body>
        <main>
            <section>
                <h2>Confirm Student Deletion</h2>

                <p>
                    Delete
                    <?= htmlspecialchars(
                        $student["full_name"],
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                    (<?= htmlspecialchars(
                        $student["email"],
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>)?
                </p>

                <p>This permanently deletes the student account.</p>

                <form action="delete_student.php" method="POST">
                    <input
                        type="hidden"
                        name="student_id"
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

                    <a href="../pages/students.php">Cancel</a>
                </form>
            </section>
        </main>
        </body>
        </html>
        <?php
        exit;
    }

    if (
        !is_string($_POST["confirmed"]) ||
        $_POST["confirmed"] !== "yes"
    ) {
        stopDelete("Invalid confirmation.", 400);
    }

    $stmt = $conn->prepare(
        "DELETE FROM students
         WHERE student_id = ? AND role = 'student'"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $status = "missing";

    if ($stmt->affected_rows === 1) {
    $status = "deleted";

    recordAudit(
        $conn,
        "delete",
        "student",
        $id,
        "Deleted student account."
    );
}

$stmt->close();

$conn->commit();
$transactionStarted = false;

$conn->close();

} catch (Throwable $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }

    error_log($e->getMessage());

    $status = "delete_error";

    if (
    $e instanceof mysqli_sql_exception &&
    (int) $e->getCode() === 1451) {
        $status = "linked";
    }
}

header(
    "Location: ../pages/students.php?status=" . $status,
    true,
    303
);

exit;