<?php

// Quick local check: verify whether the local DB password hash matches the provided password.
// This avoids Laravel tinker quoting issues.

$email = 'admin@famledger.com';
$password = 'SuperAdmin123!';

$host = '127.0.0.1';
$port = 3306;
$db = 'famledger';
$user = 'root';
$pass = '';

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, 'DB connection failed: '.$e->getMessage().PHP_EOL);
    exit(1);
}

$stmt = $pdo->prepare('SELECT id, email, status, password FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
$row = $stmt->fetch();

if (! $row) {
    echo "NOT_FOUND\n";
    exit(0);
}

$hash = $row['password'];
$ok = password_verify($password, $hash);

echo "FOUND\n";
echo 'id='.$row['id']."\n";
echo 'status='.$row['status']."\n";
echo 'hash_ok='.($ok ? 'YES' : 'NO')."\n";
