<?php

// Minimal local helper to reset a user's password.
// Usage: php scripts/set_user_password.php
//
// WARNING: This updates the local dev database only.

$email = 'admin@famledger.com';
$password = 'SuperAdmin123!';

$host = '127.0.0.1';
$port = 3306;
$db = 'famledger';
$user = 'root';
$pass = '';

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('UPDATE users SET password = :hash WHERE email = :email LIMIT 1');
$stmt->execute(['hash' => $hash, 'email' => $email]);

echo "UPDATED\n";
