<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Predis\Client;

// -------------------------------
// Configuración Base de Datos (Eloquent ORM)
// -------------------------------
$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => getenv('DB_HOST') ?: '127.0.0.1',
    'database'  => getenv('DB_DATABASE') ?: 'empresa_db',
    'username'  => getenv('DB_USERNAME') ?: 'root',
    'password'  => getenv('DB_PASSWORD') ?: '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// -------------------------------
// Configuración Redis (Predis)
// -------------------------------
$redis = new Client([
    'scheme' => 'tcp',
    'host'   => getenv('REDIS_HOST') ?: 'redis', // en docker-compose se llama "redis"
    'port'   => getenv('REDIS_PORT') ?: 6379,
]);

// Hacer Redis accesible en todo el proyecto
$GLOBALS['redis_client'] = $redis;
