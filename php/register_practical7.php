<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}

$file = __DIR__ . "/registrations.csv";

$name = trim($_POST["fullName"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$course = trim($_POST["course"] ?? "");
$year = trim($_POST["year"] ?? "");
$gender = trim($_POST["gender"] ?? "");

$handle = fopen($file, "a");

if ($handle === false) {
    die("ERROR: Cannot open CSV file.");
}

if (filesize($file) === 0) {
    fputcsv($handle, [
        "Name",
        "Email",
        "Mobile",
        "Course",
        "Year",
        "Gender"
    ]);
}

fputcsv($handle, [
    $name,
    $email,
    $mobile,
    $course,
    $year,
    $gender
]);

fclose($handle);

echo "<h2>Registration Successful</h2>";
echo "<p>Data saved successfully.</p>";
echo "<p>File location:</p>";
echo "<p>" . htmlspecialchars($file) . "</p>";

?>