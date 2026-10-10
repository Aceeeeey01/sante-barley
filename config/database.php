<?php
declare(strict_types=1);

// Supabase: use the direct or session-pooler PostgreSQL connection,
// never a browser key.
$config = is_file(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : [];

$dsn = getenv('DATABASE_DSN') ?: ($config['dsn'] ?? '');

if (!$dsn) {
    http_response_code(503);
    exit('Database configuration required. See README.md.');
}

try {
    $db = new PDO(
        $dsn,
        getenv('DATABASE_USER') ?: ($config['user'] ?? ''),
        getenv('DATABASE_PASSWORD') ?: ($config['password'] ?? ''),
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('Database connection failed');
    http_response_code(503);
    exit('The store is temporarily unavailable. Please try again shortly.');
}