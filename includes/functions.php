<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Protect information before displaying it on the website
function e(?string $value): string {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

// Create a security token for forms
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Check the security token when a form is submitted
function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $token)
    ) {
        http_response_code(419);
        exit('Invalid or expired form token. Go back and try again.');
    }
}

// Check whether a user is logged in
function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php?page=login');
        exit;
    }
}

// Get the ID of the logged-in user
function current_user_id(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

// Redirect to another page
function redirect(string $url): never {
    header("Location: $url");
    exit;
}

// Save a message to display later
function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Display saved messages
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];

        echo '<div class="alert ' . e($f['type']) . '">'
            . e($f['message'])
            . '</div>';

        unset($_SESSION['flash']);
    }
}

// Receive and clean text entered in forms
function post_string(string $key, int $max = 255): string {
    $value = trim((string)($_POST[$key] ?? ''));

    return mb_substr($value, 0, $max);
}