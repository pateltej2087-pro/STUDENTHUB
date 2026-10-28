<?php

require_once __DIR__ . "/auth.php";
requireRole("admin");
require_once __DIR__ . "/audit.php";

function readStudentField($name)
{
    if (isset($_POST[$name]) && is_string($_POST[$name])) {
        return $_POST[$name];
    }

    return "";
}

function showStudentErrors($messages, $status)
{
    http_response_code($status);

    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, initial-scale=1.0">
        <title>StudentHub - Student Error</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body>
    <main>
    <section>
    <h2>Unable to Save Student</h2>';

    foreach ($messages as $message) {
        echo "<p>" . htmlspecialchars(
            $message,
            ENT_QUOTES,
            "UTF-8"
        ) . "</p>";
    }

    echo '<p>Use your browser Back button to correct the form.</p>
          <a href="../pages/students.php">Back to Student List</a>
          </section>
          </main>
          </body>
          </html>';

    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    showStudentErrors(["Please submit the student form."], 405);
}

$token = readStudentField("csrf_token");

if (
    !isset($_SESSION["student_csrf"]) ||
    !hash_equals($_SESSION["student_csrf"], $token)
) {
    showStudentErrors(
        ["Invalid form token. Open the student form again."],
        403
    );
}

$idText = readStudentField("student_id");
$id = 0;

if ($idText !== "") {
    $id = filter_var($idText, FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        showStudentErrors(["Invalid student ID."], 400);
    }
}

$isEdit = $id > 0;

$fullName = trim(readStudentField("fullName"));
$email = trim(readStudentField("email"));
$mobile = trim(readStudentField("mobile"));
$password = readStudentField("password");
$course = readStudentField("course");
$year = readStudentField("year");
$gender = readStudentField("gender");

$errors = [];

if (!preg_match('/^[A-Za-z ]{3,50}$/', $fullName)) {
    $errors[] = "Name must contain 3–50 letters and spaces.";
}

if (
    strlen($email) > 254 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    $errors[] = "Enter a valid email address.";
}

if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
    $errors[] = "Enter a 10-digit mobile number starting with 6–9.";
}

$passwordPattern =
    '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,72}$/';

if (!$isEdit || $password !== "") {
    if (!preg_match($passwordPattern, $password)) {
        $errors[] =
            "Password must contain 8–72 characters with uppercase, lowercase, a number and a special character from @$!%*?&.";
    }
}

if (!in_array($course, ["AIML", "CE", "IT"], true)) {
    $errors[] = "Select a valid course.";
}

if (!in_array($year, ["1", "2", "3", "4"], true)) {
    $errors[] = "Select a valid year.";
}

if (!in_array($gender, ["Male", "Female", "Other"], true)) {
    $errors[] = "Select a valid gender.";
}

if (!empty($errors)) {
    showStudentErrors($errors, 422);
}

require_once __DIR__ . "/db_mysqli.php";
$transactionStarted = false;

try {
    // Only student accounts can be edited.
    if ($isEdit) {
        $checkStudent = $conn->prepare(
            "SELECT student_id
             FROM students
             WHERE student_id = ? AND role = 'student'"
        );

        $checkStudent->bind_param("i", $id);
        $checkStudent->execute();
        $checkStudent->store_result();

        $exists = $checkStudent->num_rows > 0;

        $checkStudent->close();

        if (!$exists) {
            showStudentErrors(["Student not found."], 404);
        }
    }

    // Check all accounts, including admin accounts, for duplicate email.
    $checkEmail = $conn->prepare(
        "SELECT student_id
         FROM students
         WHERE email = ? AND student_id <> ?"
    );

    $checkEmail->bind_param("si", $email, $id);
    $checkEmail->execute();
    $checkEmail->store_result();

    $duplicate = $checkEmail->num_rows > 0;

    $checkEmail->close();

    if ($duplicate) {
        showStudentErrors(
            ["This email is already registered."],
            409
        );
    }

    $yearNumber = (int) $year;
    $conn->begin_transaction();
$transactionStarted = true;

// Recheck and lock the student before updating.
if ($isEdit) {
    $lock = $conn->prepare(
        "SELECT student_id
         FROM students
         WHERE student_id = ? AND role = 'student'
         FOR UPDATE"
    );

    $lock->bind_param("i", $id);
    $lock->execute();
    $lock->store_result();

    $stillExists = $lock->num_rows > 0;

    $lock->close();

    if (!$stillExists) {
        $conn->rollback();
        $transactionStarted = false;

        showStudentErrors(["Student not found."], 404);
    }
}

    if (!$isEdit) {
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $conn->prepare(
            "INSERT INTO students
             (full_name, email, mobile, password_hash,
              course, year, gender, role)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'student')"
        );

        $stmt->bind_param(
            "sssssis",
            $fullName,
            $email,
            $mobile,
            $passwordHash,
            $course,
            $yearNumber,
            $gender
        );

        $status = "added";

    } elseif ($password === "") {
        // Keep the existing password hash.
        $stmt = $conn->prepare(
            "UPDATE students
             SET full_name = ?, email = ?, mobile = ?,
                 course = ?, year = ?, gender = ?
             WHERE student_id = ? AND role = 'student'"
        );

        $stmt->bind_param(
            "ssssisi",
            $fullName,
            $email,
            $mobile,
            $course,
            $yearNumber,
            $gender,
            $id
        );

        $status = "updated";

    } else {
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $conn->prepare(
            "UPDATE students
             SET full_name = ?, email = ?, mobile = ?,
                 password_hash = ?, course = ?,
                 year = ?, gender = ?
             WHERE student_id = ? AND role = 'student'"
        );

        $stmt->bind_param(
            "sssssisi",
            $fullName,
            $email,
            $mobile,
            $passwordHash,
            $course,
            $yearNumber,
            $gender,
            $id
        );

        $status = "updated";
    }

    $stmt->execute();

if (!$isEdit) {
    $id = (int) $conn->insert_id;
}

$stmt->close();

$action = "create";
$description = "Created student account.";

if ($isEdit) {
    $action = "update";
    $description = "Updated student account.";
}

recordAudit(
    $conn,
    $action,
    "student",
    $id,
    $description
);

$conn->commit();
$transactionStarted = false;

$conn->close();

    header(
        "Location: ../pages/students.php?status=" . $status,
        true,
        303
    );

    exit;

} catch (Throwable $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }

    error_log($e->getMessage());

    if (
        $e instanceof mysqli_sql_exception &&
        (int) $e->getCode() === 1062
    ) {
        showStudentErrors(
            ["This email is already registered."],
            409
        );
    }

    showStudentErrors(
        ["Unable to save the student. Please try again later."],
        500
    );
}