<?php

/**
 * Acceso a la tabla `reservas`. Todo el SQL de las reservas vive aquí.
 * Siempre con consultas preparadas: los datos del usuario nunca se concatenan en el SQL.
 */
class ReservaRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Guarda una solicitud (estado "pendiente" por defecto) y devuelve su id. */
    public function guardar(array $datos): int
    {
        $sql = 'INSERT INTO reservas (nombre, email, telefono, comensales, fecha, hora, comentarios)
                VALUES (:nombre, :email, :telefono, :comensales, :fecha, :hora, :comentarios)';

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute([
            ':nombre' => $datos['nombre'],
            ':email' => $datos['email'],
            ':telefono' => $datos['telefono'],
            ':comensales' => $datos['comensales'],
            ':fecha' => $datos['fecha'],
            ':hora' => $datos['hora'],
            ':comentarios' => $datos['comentarios'] !== '' ? $datos['comentarios'] : null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function buscarPorId(int $id): ?array
    {
        $consulta = $this->pdo->prepare('SELECT * FROM reservas WHERE id = :id');
        $consulta->execute([':id' => $id]);
        $fila = $consulta->fetch();
        return $fila === false ? null : $fila;
    }
}
