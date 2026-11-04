<?php

/**
 * Protección CSRF: el formulario lleva un token secreto guardado en la sesión.
 * Una web ajena no puede conocerlo, así que no puede enviar reservas en nombre del visitante.
 */
class Csrf
{
    private const CLAVE_SESION = 'csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::CLAVE_SESION])) {
            $_SESSION[self::CLAVE_SESION] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::CLAVE_SESION];
    }

    public static function esValido(mixed $tokenRecibido): bool
    {
        return is_string($tokenRecibido)
            && isset($_SESSION[self::CLAVE_SESION])
            && hash_equals($_SESSION[self::CLAVE_SESION], $tokenRecibido);
    }
}
