<?php
declare(strict_types=1);

/**
 * Flor de Luna — conexión a MySQL (PDO).
 *
 * Funciona sin cambiar nada en:
 *   - Railway: lee las variables que entrega el servicio MySQL (MYSQL_URL, o MYSQLHOST,
 *              MYSQLPORT, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE).
 *   - XAMPP:   si no hay variables, usa 127.0.0.1, usuario root, sin contraseña, base flor_de_luna.
 *
 * Orden de prioridad: FDL_DB_*  >  MYSQL_URL  >  MYSQL*  >  valores de XAMPP.
 */

/** Lee una variable de entorno (vacía = no definida). */
function env_valor(string ...$nombres): ?string
{
    foreach ($nombres as $nombre) {
        $valor = getenv($nombre);
        if ($valor === false || $valor === '') {
            $valor = $_ENV[$nombre] ?? $_SERVER[$nombre] ?? false;
        }
        if ($valor !== false && $valor !== '') {
            return (string) $valor;
        }
    }
    return null;
}

/** Datos de conexión ya resueltos. */
function database_config(): array
{
    $cfg = [
        'host'     => '127.0.0.1',
        'port'     => '3306',
        'name'     => 'flor_de_luna',
        'user'     => 'root',
        'password' => '',
    ];

    // Railway también entrega todo junto en una URL: mysql://usuario:clave@host:puerto/base
    $url = env_valor('MYSQL_URL', 'DATABASE_URL');
    if ($url !== null && ($p = parse_url($url)) !== false && isset($p['host'])) {
        $cfg['host']     = $p['host'];
        $cfg['port']     = (string) ($p['port'] ?? '3306');
        $cfg['user']     = isset($p['user']) ? rawurldecode($p['user']) : $cfg['user'];
        $cfg['password'] = isset($p['pass']) ? rawurldecode($p['pass']) : $cfg['password'];
        $cfg['name']     = isset($p['path']) ? ltrim($p['path'], '/') : $cfg['name'];
    }

    // Variables sueltas de Railway
    $cfg['host']     = env_valor('FDL_DB_HOST', 'MYSQLHOST') ?? $cfg['host'];
    $cfg['port']     = env_valor('FDL_DB_PORT', 'MYSQLPORT') ?? $cfg['port'];
    $cfg['name']     = env_valor('FDL_DB_NAME', 'MYSQLDATABASE') ?? $cfg['name'];
    $cfg['user']     = env_valor('FDL_DB_USER', 'MYSQLUSER') ?? $cfg['user'];
    $cfg['password'] = env_valor('FDL_DB_PASSWORD', 'MYSQLPASSWORD', 'MYSQL_ROOT_PASSWORD') ?? $cfg['password'];

    return $cfg;
}

function database(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $c = database_config();
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $c['host'], $c['port'], $c['name']);
    $connection = new PDO($dsn, $c['user'], $c['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10,
    ]);
    $connection->exec("SET time_zone = '-06:00'");   // hora de El Salvador

    return $connection;
}
