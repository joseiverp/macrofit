<?php

/**
 * Crea la conexión PDO a MySQL/MariaDB con opciones seguras.
 */
class Database
{
    public static function conectar(array $config): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['puerto'],
            $config['nombre']
        );

        return new PDO($dsn, $config['usuario'], $config['contrasena'], [
            // Si algo falla, PDO lanza una excepción en lugar de fallar en silencio.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Las filas se devuelven como arrays asociativos: $fila['nombre'].
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Consultas preparadas reales en el servidor de base de datos.
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
