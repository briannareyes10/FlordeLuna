<?php
declare(strict_types=1);

/**
 * Prueba rápida de la conexión:  https://TU-SITIO/Conection/salud.php
 * Responde {"ok":true,...} si PHP puede hablar con MySQL. No muestra claves.
 */
require_once __DIR__ . '/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $db = database();
    $cfg = database_config();
    echo json_encode([
        'ok' => true,
        'base' => $cfg['name'],
        'host' => $cfg['host'],
        'tablas' => (int) $db->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetchColumn(),
        'productos' => (int) $db->query('SELECT COUNT(*) FROM productos')->fetchColumn(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Flor de Luna salud: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'Sin conexión con la base de datos.']);
}
