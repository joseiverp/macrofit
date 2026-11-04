<?php
/**
 * Portada de MacroFit. Prepara los datos y carga la plantilla.
 * Aquí no hay HTML: la presentación está en templates/inicio.php.
 */

require dirname(__DIR__) . '/src/bootstrap.php';

$horario = new Horario($configHorarios);
$reglas = $horario->reglas();
$carta = require RAIZ . '/config/carta.php';

// Mensaje y datos que deja reservar.php tras redirigir (si los hay).
$flash = leerFlash();
$errores = $flash['errores'] ?? [];
$antiguos = $flash['antiguos'] ?? [];

/** Valor anterior de un campo, para no obligar a escribirlo todo otra vez. */
$antiguo = fn (string $campo): string => is_string($antiguos[$campo] ?? null) ? $antiguos[$campo] : '';

// Datos para el JavaScript del formulario: salen de la misma clase que valida en el servidor.
$disponibilidad = $horario->disponibilidadCompleta();
$fechasCerradas = array_values(array_filter(
    array_keys($disponibilidad),
    fn (string $fecha) => !$horario->estaAbierto($horario->crearFecha($fecha))
));

$datosFormulario = [
    'disponibilidad' => $disponibilidad,
    'fechasCerradas' => $fechasCerradas,
];

$diasCerrados = $horario->diasCerrados();
$textoCerrados = $diasCerrados === []
    ? 'Abrimos todos los días.'
    : 'Cerramos los ' . implode(' y los ', $diasCerrados) . '.';

require RAIZ . '/templates/inicio.php';
