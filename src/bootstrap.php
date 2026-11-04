<?php
/**
 * Arranque común de todas las páginas públicas:
 * carga de clases, configuración, sesión y cabeceras de seguridad.
 */

define('RAIZ', dirname(__DIR__));

// Carga automática de clases: "Horario" -> src/Horario.php
spl_autoload_register(function (string $clase): void {
    $archivo = RAIZ . '/src/' . $clase . '.php';
    if (is_file($archivo)) {
        require $archivo;
    }
});

require RAIZ . '/src/helpers.php';

$configHorarios = require RAIZ . '/config/horarios.php';
date_default_timezone_set($configHorarios['zona_horaria']);

// Configuración local (credenciales). No está en Git: se crea copiando config.example.php.
$configApp = is_file(RAIZ . '/config/config.php')
    ? require RAIZ . '/config/config.php'
    : null;

// En producción no se muestran errores al visitante: se registran en el log.
$modoDebug = (bool) ($configApp['debug'] ?? false);
ini_set('display_errors', $modoDebug ? '1' : '0');
error_reporting(E_ALL);

// Sesión con cookie protegida (no accesible desde JavaScript y no enviada desde otras webs).
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

// Cabeceras de seguridad básicas.
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; font-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
