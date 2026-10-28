<?php

function showResult($title, $messages, $status)
{
    http_response_code($status);

    function escapeText($text)
    {
        return htmlspecialchars(
            $text,
            ENT_QUOTES,
            "UTF-8"
        );
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, initial-scale=1.0">
        <title>StudentHub - Registration</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body>
        <header>
            <h1>StudentHub</h1>
        </header>

        <main>
            <section>
                <h2><?= escapeText($title) ?></h2>

                <?php foreach ($messages as $message) { ?>
                    <p><?= escapeText($message) ?></p>
                <?php } ?>

                <a href="../pages/register.html">
                    Back to Registration
                </a>
            </section>
        </main>
    </body>
    </html>
    <?php
    exit;
}

function readField($name)
{
    if (
        !isset($_POST[$name]) ||
        !is_string($_POST[$name])
    ) {
        return "";
    }

    return $_POST[$name];
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");

    showResult(
        "Invalid Request",
        ["Please submit the registration form."],
        405
    );
}

$fullName = trim(readField("fullName"));
$email = trim(readField("email"));
$mobile = trim(readField("mobile"));
$password = readField("password");
$confirmPassword = readField("confirmPassword");
$course = readField("course");
$year = readField("year");
$gender = readField("gender");
$terms = readField("terms");

$errors = [];

$namePattern = '/^[A-Za-z ]{3,50}$/';

$emailPattern =
    '/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/';

$passwordPattern =
    '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,72}$/';

if (!preg_match($namePattern, $fullName)) {
    $errors[] =
        "Name must contain 3–50 characters using letters and spaces.";
}

if (
    strlen($email) > 254 ||
    !preg_match($emailPattern, $email)
) {
    $errors[] = "Enter a valid email address of up to 254 characters.";
}

if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
    $errors[] =
        "Enter a 10-digit mobile number starting with 6, 7, 8 or 9.";
}

if (!preg_match($passwordPattern, $password)) {
    $errors[] =
        "Password must contain 8–72 characters, including uppercase, lowercase, a number and a special character from @$!%*?&.";
}

if ($confirmPassword === "" || $password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

if (!in_array($course, ["AIML", "CE", "IT"], true)) {
    $errors[] = "Please select a valid course.";
}

if (!in_array($year, ["1", "2", "3", "4"], true)) {
    $errors[] = "Please select a valid year.";
}

if (!in_array($gender, ["Male", "Female", "Other"], true)) {
    $errors[] = "Please select a valid gender.";
}

if ($terms !== "accepted") {
    $errors[] = "You must accept the terms and conditions.";
}

if (!empty($errors)) {
    showResult("Registration Failed", $errors, 422);
}

require_once __DIR__ . "/db_mysqli.php";

try {
    // Check whether the email already exists.
    $check = $conn->prepare(
        "SELECT student_id FROM students WHERE email = ?"
    );

    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    $emailExists = $check->num_rows > 0;

    $check->close();

    if ($emailExists) {
        showResult(
            "Registration Failed",
            ["This email is already registered. Use another email."],
            409
        );
    }

    // Store a hash instead of the original password.
    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $yearNumber = (int) $year;

    $insert = $conn->prepare(
        "INSERT INTO students
        (full_name, email, mobile, password_hash, course, year, gender)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $insert->bind_param(
        "sssssis",
        $fullName,
        $email,
        $mobile,
        $passwordHash,
        $course,
        $yearNumber,
        $gender
    );

    $insert->execute();
    $insert->close();
    $conn->close();

    showResult(
        "Registration Successful",
        ["Your student account has been created successfully."],
        201
    );

} catch (mysqli_sql_exception $e) {
    // The UNIQUE email constraint also prevents simultaneous duplicates.
    if ((int) $e->getCode() === 1062) {
        showResult(
            "Registration Failed",
            ["This email is already registered. Use another email."],
            409
        );
    }

    error_log($e->getMessage());

    showResult(
        "Registration Failed",
        ["Unable to save your registration. Please try again later."],
        500
    );

} catch (Throwable $e) {
    error_log($e->getMessage());

    showResult(
        "Registration Failed",
        ["Unable to complete registration. Please try again later."],
        500
    );
}