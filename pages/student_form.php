<?php

require_once __DIR__ . "/../php/auth.php";
requireRole("admin");

require_once __DIR__ . "/../php/db_mysqli.php";

function escapeText($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}

if (!isset($_SESSION["student_csrf"])) {
    $_SESSION["student_csrf"] = bin2hex(random_bytes(32));
}

$student = [
    "student_id" => "",
    "full_name" => "",
    "email" => "",
    "mobile" => "",
    "course" => "",
    "year" => "",
    "gender" => ""
];

$isEdit = false;

if (isset($_GET["id"])) {
    $id = filter_var($_GET["id"], FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        http_response_code(400);
        exit("Invalid student ID.");
    }

    try {
        $stmt = $conn->prepare(
            "SELECT student_id, full_name, email, mobile,
                    course, year, gender
             FROM students
             WHERE student_id = ? AND role = 'student'"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $record = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if (!$record) {
            http_response_code(404);
            exit("Student not found.");
        }

        $student = $record;
        $isEdit = true;

    } catch (mysqli_sql_exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        exit("Unable to load student details.");
    }
}

$conn->close();

$title = "Add Student";

if ($isEdit) {
    $title = "Edit Student";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>StudentHub - <?= escapeText($title) ?></title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<header>
    <h1>StudentHub Admin</h1>

    <nav aria-label="Admin Navigation">
        <a href="admin.php">Admin Dashboard</a>
        <a href="students.php">Manage Students</a>
    </nav>
</header>

<main>
    <section>
        <h2><?= escapeText($title) ?></h2>

        <form action="../php/save_student.php" method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= escapeText($_SESSION['student_csrf']) ?>"
            >

            <input
                type="hidden"
                name="student_id"
                value="<?= escapeText($student['student_id']) ?>"
            >

            <div class="form-group">
                <label for="fullName">Full Name</label>

                <input
                    type="text"
                    id="fullName"
                    name="fullName"
                    minlength="3"
                    maxlength="50"
                    pattern="[A-Za-z ]{3,50}"
                    value="<?= escapeText($student['full_name']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    maxlength="254"
                    value="<?= escapeText($student['email']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="mobile">Mobile Number</label>

                <input
                    type="tel"
                    id="mobile"
                    name="mobile"
                    inputmode="numeric"
                    maxlength="10"
                    pattern="[6-9][0-9]{9}"
                    value="<?= escapeText($student['mobile']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    minlength="8"
                    maxlength="72"
                    pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[@$!%*?&])[A-Za-z0-9@$!%*?&]{8,72}"
                    <?php if (!$isEdit) { echo "required"; } ?>
                >

                <small>
                    Use 8–72 characters with uppercase, lowercase,
                    a number and a special character from @$!%*?&.
                    <?php if ($isEdit) { ?>
                        Leave blank to keep the current password.
                    <?php } ?>
                </small>
            </div>

            <div class="form-group">
                <label for="course">Course</label>

                <select id="course" name="course" required>
                    <option value="">Select Course</option>

                    <?php foreach (["AIML", "CE", "IT"] as $option) { ?>
                        <option
                            value="<?= escapeText($option) ?>"
                            <?php
                            if ($student["course"] === $option) {
                                echo "selected";
                            }
                            ?>
                        >
                            <?= escapeText($option) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="year">Year</label>

                <select id="year" name="year" required>
                    <option value="">Select Year</option>

                    <?php foreach (["1", "2", "3", "4"] as $option) { ?>
                        <option
                            value="<?= escapeText($option) ?>"
                            <?php
                            if ((string) $student["year"] === $option) {
                                echo "selected";
                            }
                            ?>
                        >
                            Year <?= escapeText($option) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>

                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>

                    <?php foreach (["Male", "Female", "Other"] as $option) { ?>
                        <option
                            value="<?= escapeText($option) ?>"
                            <?php
                            if ($student["gender"] === $option) {
                                echo "selected";
                            }
                            ?>
                        >
                            <?= escapeText($option) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <button type="submit">Save Student</button>

            <a href="students.php">Cancel</a>
        </form>
    </section>
</main>

<footer>
    <p>StudentHub &copy; 2026</p>
</footer>

<script src="../js/script.js"></script>

</body>
</html>