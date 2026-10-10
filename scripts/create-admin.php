<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../config/database.php';

$email    = $argv[1] ?? '';
$password = getenv('ADMIN_PASSWORD') ?: '';

if (
    !filter_var($email, FILTER_VALIDATE_EMAIL)
    || strlen($password) < 12
    || strlen($password) > 72
) {
    fwrite(STDERR, "Usage: ADMIN_PASSWORD=<12+ character password> php scripts/create-admin.php email@example.com\n");
    exit(1);
}

try {
    $db->beginTransaction();

    $q = $db->prepare(
        'INSERT INTO users(full_name,username,email,password) VALUES (?,?,?,?) RETURNING id'
    );
    $q->execute([
        'Store Administrator',
        'admin_' . bin2hex(random_bytes(4)),
        strtolower($email),
        password_hash($password, PASSWORD_DEFAULT),
    ]);

    $db->prepare('INSERT INTO admins(user_id) VALUES (?)')->execute([$q->fetchColumn()]);

    $db->commit();
    echo "Administrator created.\n";
} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(
        STDERR,
        $e->getCode() === '23505'
            ? "That email is already registered.\n"
            : "Unable to create administrator.\n"
    );
    exit(1);
}