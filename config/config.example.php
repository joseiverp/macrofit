<?php
/**
 * Plantilla de configuración local.
 *
 * 1. Copia este archivo como config/config.php
 * 2. Pon tus datos de conexión reales en config.php
 *
 * config.php está en .gitignore: tus credenciales nunca se suben a GitHub.
 */

return [
    'db' => [
        'host' => '127.0.0.1',
        'puerto' => 3306,
        'nombre' => 'macrofit',
        'usuario' => 'macrofit_app',
        'contrasena' => 'CAMBIA_ESTA_CONTRASENA',
    ],
    // true solo en tu ordenador para ver errores de PHP. En un servidor público: false.
    'debug' => false,
];
