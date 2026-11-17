<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $query = trim((string)($_GET['q'] ?? ''));

    if (strlen($query) > 100) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Search query is too long.'
        ]);
        exit;
    }

    if ($query === '') {
        $stmt = $pdo->query(
            'SELECT vendor_id, name, service_type, price
             FROM vendors ORDER BY name LIMIT 50'
        );
    } else {
        $stmt = $pdo->prepare(
            'SELECT vendor_id, name, service_type, price
             FROM vendors
             WHERE name LIKE ? OR service_type LIKE ?
             ORDER BY name LIMIT 50'
        );

        $search = '%' . $query . '%';
        $stmt->execute([$search, $search]);
    }

    echo json_encode(
        $stmt->fetchAll(),
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    );

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Unable to retrieve vendors.'
    ]);
}