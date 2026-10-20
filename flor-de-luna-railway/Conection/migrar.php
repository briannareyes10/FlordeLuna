<?php
declare(strict_types=1);

/**
 * Crea las tablas y los datos iniciales ejecutando Base/flor_de_luna.sql.
 * Es seguro correrlo muchas veces (el script usa IF NOT EXISTS / INSERT IGNORE).
 * Se ejecuta solo al arrancar en Railway (ver start.sh) y no se puede llamar desde el navegador.
 *
 * Uso manual:  php Conection/migrar.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/database.php';

$archivo = __DIR__ . '/../Base/flor_de_luna.sql';
$sql = file_get_contents($archivo);
if ($sql === false) {
    fwrite(STDERR, "No se encontró $archivo\n");
    exit(1);
}

// Quita comentarios de línea (-- ...) y separa las sentencias por ';'
$sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
$sentencias = array_filter(array_map('trim', explode(';', $sql)), static fn(string $s): bool => $s !== '');

// En Railway la base ya existe (se llama "railway"): se ignoran CREATE DATABASE y USE.
$sentencias = array_filter($sentencias, static fn(string $s): bool => !preg_match('/^(CREATE\s+DATABASE|USE)\b/i', $s));

// Espera hasta ~60 s a que MySQL esté listo (en Railway ambos servicios arrancan a la vez)
$db = null;
for ($intento = 1; $intento <= 30; $intento++) {
    try {
        $db = database();
        break;
    } catch (Throwable $e) {
        fwrite(STDERR, "[migrar] MySQL aún no responde ($intento/30): " . $e->getMessage() . "\n");
        sleep(2);
    }
}
if (!$db) {
    fwrite(STDERR, "[migrar] No se pudo conectar a MySQL. Revisa las variables de Railway.\n");
    exit(1);
}

foreach ($sentencias as $sentencia) {
    $db->exec($sentencia);
}

$tablas = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$productos = (int) $db->query('SELECT COUNT(*) FROM productos')->fetchColumn();
fwrite(STDOUT, '[migrar] Listo. Tablas: ' . count($tablas) . ' (' . implode(', ', $tablas) . "). Productos: $productos\n");
