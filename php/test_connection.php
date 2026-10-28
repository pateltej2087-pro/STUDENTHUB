<?php

require_once __DIR__ . "/db.php";

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM students WHERE course = :course"
);

$stmt->execute(["course" => "AIML"]);

$studentCount = $stmt->fetchColumn();

echo "<h2>Database connected successfully!</h2>";
echo "<p>Prepared statement executed successfully.</p>";
echo "<p>AIML students: " . (int) $studentCount . "</p>";
