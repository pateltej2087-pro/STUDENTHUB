<?php

require_once __DIR__ . "/../php/auth.php";
requireRole("admin");

if (!isset($_SESSION["student_csrf"])) {
    $_SESSION["student_csrf"] = bin2hex(random_bytes(32));
}

require_once __DIR__ . "/../php/db_mysqli.php";

function escapeText($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}

function getQueryText($name)
{
    if (isset($_GET[$name]) && is_string($_GET[$name])) {
        return trim($_GET[$name]);
    }

    return "";
}

$search = substr(getQueryText("search"), 0, 254);
$course = getQueryText("course");
$year = getQueryText("year");

if (!in_array($course, ["AIML", "CE", "IT"], true)) {
    $course = "";
}

if (!in_array($year, ["1", "2", "3", "4"], true)) {
    $year = "";
}

$page = max(1, (int) getQueryText("page"));

$perPage = 10;
$students = [];
$totalStudents = 0;
$totalPages = 1;
$error = "";

$searchValue = "%" . $search . "%";

$where = "
    WHERE role = 'student'
    AND (
        full_name LIKE ?
        OR email LIKE ?
        OR course LIKE ?
    )
    AND (? = '' OR course = ?)
    AND (? = '' OR year = ?)
";

try {
    $count = $conn->prepare(
        "SELECT COUNT(*) FROM students " . $where
    );

    $count->bind_param(
        "sssssss",
        $searchValue,
        $searchValue,
        $searchValue,
        $course,
        $course,
        $year,
        $year
    );

    $count->execute();
    $count->bind_result($totalStudents);
    $count->fetch();
    $count->close();

    $totalPages = max(
        1,
        (int) ceil($totalStudents / $perPage)
    );

    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $list = $conn->prepare(
        "SELECT student_id, full_name, email,
                mobile, course, year, gender
         FROM students "
        . $where .
        " ORDER BY student_id DESC
          LIMIT ? OFFSET ?"
    );

    $list->bind_param(
        "sssssssii",
        $searchValue,
        $searchValue,
        $searchValue,
        $course,
        $course,
        $year,
        $year,
        $perPage,
        $offset
    );

    $list->execute();

    $result = $list->get_result();

    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    $list->close();

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    $error = "Unable to load student records. Please try again.";
}

$conn->close();

function pageLink($number, $search, $course, $year)
{
    return "students.php?" . http_build_query([
        "search" => $search,
        "course" => $course,
        "year" => $year,
        "page" => $number
    ]);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>StudentHub - Student Management</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<header>
    <h1>StudentHub Admin</h1>

    <nav aria-label="Admin Navigation">
        <a href="admin.php">Admin Dashboard</a>
        <a href="students.php">Manage Students</a>
        <a href="events.html">Events</a>

        <form action="../php/logout.php" method="POST">
            <input
                type="hidden"
                name="logout_token"
                value="<?= escapeText(
                    $_SESSION['logout_token']
                ) ?>"
            >

            <button type="submit">Logout</button>
        </form>
    </nav>
</header>

<main>
    <section>
        <h2>Student Management</h2>

        <?php

$statusMessages = [
    "added" => "Student added successfully.",
    "updated" => "Student updated successfully.",
    "deleted" => "Student deleted successfully.",
    "linked" => "Cannot delete this student because related records exist.",
    "missing" => "Student not found.",
    "delete_error" => "Unable to delete the student. Please try again."
];

$status = getQueryText("status");
?>

<?php if (isset($statusMessages[$status])) { ?>
    <p class="success-message" role="status">
        <?= escapeText($statusMessages[$status]) ?>
    </p>
<?php } ?>

        <p>
    <a href="student_form.php">Add Student</a>
</p>

        <form action="students.php" method="GET">

            <div class="form-group">
                <label for="search">
                    Search by name, email or course
                </label>

                <input
                    type="search"
                    id="search"
                    name="search"
                    maxlength="254"
                    placeholder="Enter name, email or course"
                    value="<?= escapeText($search) ?>"
                >
            </div>

            <div class="form-group">
                <label for="courseFilter">Course</label>

                <select id="courseFilter" name="course">
                    <option value="">All Courses</option>

                    <?php foreach (["AIML", "CE", "IT"] as $option) { ?>
                        <option
                            value="<?= escapeText($option) ?>"
                            <?= $course === $option ? "selected" : "" ?>
                        >
                            <?= escapeText($option) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="yearFilter">Year</label>

                <select id="yearFilter" name="year">
                    <option value="">All Years</option>

                    <?php foreach (["1", "2", "3", "4"] as $option) { ?>
                        <option
                            value="<?= escapeText($option) ?>"
                            <?= $year === $option ? "selected" : "" ?>
                        >
                            Year <?= escapeText($option) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <button type="submit">Search / Filter</button>

            <a href="students.php">Reset</a>
        </form>

        <?php if ($error !== "") { ?>

            <p role="alert"><?= escapeText($error) ?></p>

        <?php } else { ?>

            <p>
                Matching students:
                <?= escapeText($totalStudents) ?>
            </p>

            <?php if (empty($students)) { ?>

                <p>No students found.</p>

            <?php } else { ?>

                <div style="overflow-x: auto;">
                    <table>
                        <caption>Student Records</caption>

                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Mobile</th>
                                <th scope="col">Course</th>
                                <th scope="col">Year</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($students as $student) { ?>
                                <tr>
                                    <td>
                                        <?= escapeText($student["student_id"]) ?>
                                    </td>
                                    <td>
                                        <?= escapeText($student["full_name"]) ?>
                                    </td>
                                    <td>
                                        <?= escapeText($student["email"]) ?>
                                    </td>
                                    <td>
                                        <?= escapeText($student["mobile"]) ?>
                                    </td>
                                    <td>
                                        <?= escapeText($student["course"]) ?>
                                    </td>
                                    <td>
                                        <?= escapeText($student["year"]) ?>
                                    </td>
                                    <td>
                                        <?= escapeText($student["gender"]) ?>
                                    </td>

                                    <td>
    <a href="student_form.php?id=<?= escapeText(
        $student['student_id']
    ) ?>">
        Edit
    </a>

    <form action="../php/delete_student.php" method="POST">
        <input
            type="hidden"
            name="student_id"
            value="<?= escapeText($student['student_id']) ?>"
        >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= escapeText($_SESSION['student_csrf']) ?>"
        >

        <button type="submit">Delete</button>
    </form>
</td>


                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Student list pagination">

                    <?php if ($page > 1) { ?>
                        <a href="<?= escapeText(
                            pageLink(
                                $page - 1,
                                $search,
                                $course,
                                $year
                            )
                        ) ?>">
                            Previous
                        </a>
                    <?php } ?>

                    <span>
                        Page <?= escapeText($page) ?>
                        of <?= escapeText($totalPages) ?>
                    </span>

                    <?php if ($page < $totalPages) { ?>
                        <a href="<?= escapeText(
                            pageLink(
                                $page + 1,
                                $search,
                                $course,
                                $year
                            )
                        ) ?>">
                            Next
                        </a>
                    <?php } ?>

                </nav>

            <?php } ?>

        <?php } ?>
    </section>
</main>

<footer>
    <p>StudentHub &copy; 2026</p>
</footer>

<script src="../js/script.js"></script>

</body>
</html>