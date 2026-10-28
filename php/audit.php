<?php

function recordAudit(
    mysqli $conn,
    string $action,
    string $targetType,
    ?int $targetId,
    string $description
): void {
    if (
        !isset($_SESSION["student_id"], $_SESSION["role"]) ||
        $_SESSION["role"] !== "admin"
    ) {
        throw new RuntimeException(
            "An admin session is required for audit logging."
        );
    }

    $actorId = (int) $_SESSION["student_id"];

    $stmt = $conn->prepare(
        "INSERT INTO audit_logs
         (actor_id, action, target_type, target_id, description)
         VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "issis",
        $actorId,
        $action,
        $targetType,
        $targetId,
        $description
    );

    $stmt->execute();
    $stmt->close();
}