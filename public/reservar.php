<?php
/**
 * Recibe el formulario de reserva (POST), lo valida y lo guarda.
 * Después redirige siempre a la portada (patrón Post/Redirect/Get), así recargar
 * la página no reenvía el formulario.
 */

require dirname(__DIR__) . '/src/bootstrap.php';

const DESTINO = 'index.php#reserva';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método no permitido.');
}

// 1. Token CSRF
if (!Csrf::esValido($_POST['csrf_token'] ?? null)) {
    guardarFlash([
        'tipo' => 'error',
        'mensaje' => 'La sesión ha caducado. Vuelve a enviar el formulario.',
        'antiguos' => $_POST,
    ]);
    header('Location: ' . DESTINO);
    exit;
}

// 2. Campo trampa: las personas no lo ven; si viene relleno, es un bot.
//    Respondemos igual que si todo fuera bien para no darle pistas.
if (($_POST['web'] ?? '') !== '') {
    guardarFlash(['tipo' => 'ok', 'mensaje' => 'Hemos recibido tu solicitud.']);
    header('Location: ' . DESTINO);
    exit;
}

// 3. Validación en el servidor
$horario = new Horario($configHorarios);
$resultado = (new ValidadorReserva($horario))->validar($_POST);

if ($resultado['errores'] !== []) {
    guardarFlash([
        'tipo' => 'error',
        'mensaje' => '¡Uy! Hay algo que revisar:',
        'errores' => $resultado['errores'],
        'antiguos' => $_POST,
    ]);
    header('Location: ' . DESTINO);
    exit;
}

// 4. Guardado en la base de datos
try {
    if ($configApp === null) {
        throw new RuntimeException('Falta config/config.php (copia config/config.example.php).');
    }
    $repositorio = new ReservaRepository(Database::conectar($configApp['db']));
    $repositorio->guardar($resultado['datos']);
} catch (Throwable $error) {
    // El detalle técnico va al log del servidor; al visitante, un mensaje genérico.
    error_log('[MacroFit] Error al guardar la reserva: ' . $error->getMessage());
    guardarFlash([
        'tipo' => 'error',
        'mensaje' => 'No hemos podido registrar la solicitud por un problema técnico. Inténtalo más tarde.',
        'antiguos' => $_POST,
    ]);
    header('Location: ' . DESTINO);
    exit;
}

// 5. Éxito: se ha registrado una SOLICITUD, todavía no está confirmada.
$fecha = $horario->crearFecha($resultado['datos']['fecha']);
guardarFlash([
    'tipo' => 'ok',
    'mensaje' => sprintf(
        'Hemos recibido tu solicitud para %d %s el %s a las %s. Está pendiente de confirmación: te escribiremos muy pronto.',
        $resultado['datos']['comensales'],
        $resultado['datos']['comensales'] === 1 ? 'persona' : 'personas',
        Horario::fechaLarga($fecha),
        $resultado['datos']['hora']
    ),
]);
header('Location: ' . DESTINO);
exit;
