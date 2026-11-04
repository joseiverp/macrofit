<?php
/**
 * Pruebas automáticas sin dependencias externas.
 * Ejecutar desde la raíz del proyecto:  php tests/run.php
 *
 * Usan una fecha "actual" fija para que el resultado no dependa del día en que se ejecuten,
 * y una base de datos SQLite en memoria para probar el repositorio sin necesitar MySQL.
 */

require __DIR__ . '/../src/Horario.php';
require __DIR__ . '/../src/ValidadorReserva.php';
require __DIR__ . '/../src/ReservaRepository.php';
require __DIR__ . '/../src/Csrf.php';
require __DIR__ . '/../src/helpers.php';

$config = require __DIR__ . '/../config/horarios.php';
$zona = new DateTimeZone($config['zona_horaria']);

$pruebas = 0;
$fallos = 0;

function comprobar(string $descripcion, bool $condicion): void
{
    global $pruebas, $fallos;
    $pruebas++;
    if ($condicion) {
        echo "  ok   {$descripcion}\n";
    } else {
        $fallos++;
        echo "  FALLO {$descripcion}\n";
    }
}

/** Crea un Horario cuyo "ahora" es la fecha y hora indicadas. */
function horarioEn(string $momento): Horario
{
    global $config, $zona;
    return new Horario($config, new DateTimeImmutable($momento, $zona));
}

// "Ahora" = martes 3 de noviembre de 2026, 10:00
$horario = horarioEn('2026-11-03 10:00');

echo "\nHorario\n";
comprobar('el lunes está cerrado', !$horario->estaAbierto($horario->crearFecha('2026-11-09')));
comprobar('un lunes no ofrece horas', $horario->horasDisponibles($horario->crearFecha('2026-11-09')) === []);
comprobar('el martes abre', $horario->estaAbierto($horario->crearFecha('2026-11-10')));
comprobar(
    'martes: de 13:00 a 15:00 cada 30 min (cierra 16:00, última reserva 1 h antes)',
    $horario->horasDisponibles($horario->crearFecha('2026-11-10')) === ['13:00', '13:30', '14:00', '14:30', '15:00']
);
comprobar(
    'viernes: comida y cena',
    $horario->horasDisponibles($horario->crearFecha('2026-11-06'))
        === ['13:00', '13:30', '14:00', '14:30', '15:00', '20:30', '21:00', '21:30', '22:00', '22:30']
);
comprobar('una fecha pasada no ofrece horas', $horario->horasDisponibles($horario->crearFecha('2026-11-01')) === []);
comprobar('más de 60 días vista no ofrece horas', $horario->horasDisponibles($horario->crearFecha('2027-01-05')) === []);
comprobar('el día 60 sí se puede reservar', $horario->horasDisponibles($horario->crearFecha('2027-01-01')) !== []);
comprobar('crearFecha rechaza el 30 de febrero', $horario->crearFecha('2027-02-30') === null);
comprobar('crearFecha rechaza texto sin formato', $horario->crearFecha('03/11/2026') === null);

$aMediodia = horarioEn('2026-11-03 12:30');
comprobar(
    'hoy a las 12:30: solo horas con 1 h de antelación (desde 13:30)',
    $aMediodia->horasDisponibles($aMediodia->crearFecha('2026-11-03')) === ['13:30', '14:00', '14:30', '15:00']
);
$porLaTarde = horarioEn('2026-11-03 15:30');
comprobar('hoy a las 15:30 ya no quedan horas', $porLaTarde->horasDisponibles($porLaTarde->crearFecha('2026-11-03')) === []);

$disponibilidad = $horario->disponibilidadCompleta();
comprobar('la disponibilidad cubre de hoy a +60 días (61 días)', count($disponibilidad) === 61);
comprobar('la disponibilidad marca el lunes sin horas', $disponibilidad['2026-11-09'] === []);
comprobar('todas las horas posibles empiezan a las 13:00', $horario->todasLasHorasPosibles()[0] === '13:00');
comprobar('resumen de un martes', $horario->resumenDeHoy() === 'Hoy, martes: comida de 13:00 a 16:00. ¡Te esperamos!');
comprobar(
    'resumen de un lunes (cerrado)',
    horarioEn('2026-11-09 10:00')->resumenDeHoy() === 'Hoy descansamos. ¡Volvemos el martes a las 13:00!'
);
comprobar('los días cerrados salen de la configuración', $horario->diasCerrados() === ['lunes']);
comprobar('fecha larga en español', Horario::fechaLarga($horario->crearFecha('2026-11-06')) === 'viernes, 6 de noviembre');

echo "\nValidadorReserva\n";
$validador = new ValidadorReserva($horario);
$valida = [
    'nombre' => 'Lucía Martínez',
    'email' => 'lucia@ejemplo.es',
    'telefono' => '600 12 34 56',
    'comensales' => '4',
    'fecha' => '2026-11-06',
    'hora' => '21:00',
    'comentarios' => '',
    'aviso_demo' => '1',
];

$resultado = $validador->validar($valida);
comprobar('una reserva correcta no tiene errores', $resultado['errores'] === []);
comprobar('el teléfono se guarda sin espacios', $resultado['datos']['telefono'] === '600123456');
comprobar('los comensales se convierten a número', $resultado['datos']['comensales'] === 4);

/** Valida la reserva correcta cambiando algunos campos y devuelve los errores. */
function erroresCon(array $cambios): array
{
    global $validador, $valida;
    return $validador->validar(array_merge($valida, $cambios))['errores'];
}

comprobar('campos vacíos dan error', count($validador->validar([])['errores']) === 7);
comprobar('email sin formato válido', isset(erroresCon(['email' => 'lucia@'])['email']));
comprobar('nombre con etiquetas HTML', isset(erroresCon(['nombre' => '<script>alert(1)</script>'])['nombre']));
comprobar('teléfono demasiado corto', isset(erroresCon(['telefono' => '1234'])['telefono']));
comprobar('11 comensales no se aceptan', isset(erroresCon(['comensales' => '11'])['comensales']));
comprobar('0 comensales no se aceptan', isset(erroresCon(['comensales' => '0'])['comensales']));
comprobar('fecha pasada', (erroresCon(['fecha' => '2026-11-02'])['fecha'] ?? '') === 'No se puede reservar en una fecha pasada.');
comprobar('día cerrado (lunes)', (erroresCon(['fecha' => '2026-11-09', 'hora' => '13:00'])['fecha'] ?? '') === 'Ese día el restaurante está cerrado. Elige otra fecha.');
comprobar('fecha imposible', isset(erroresCon(['fecha' => '2026-02-30'])['fecha']));
comprobar('fecha a más de 60 días', isset(erroresCon(['fecha' => '2027-03-01'])['fecha']));
comprobar('hora fuera de horario (17:00)', isset(erroresCon(['hora' => '17:00'])['hora']));
comprobar('cena un martes (solo abre a mediodía)', isset(erroresCon(['fecha' => '2026-11-10', 'hora' => '21:00'])['hora']));
comprobar('hora posterior a la última reserva (15:30)', isset(erroresCon(['hora' => '15:30'])['hora']));
comprobar('hora fuera del intervalo (13:15)', isset(erroresCon(['hora' => '13:15'])['hora']));
comprobar('comentarios de más de 500 caracteres', isset(erroresCon(['comentarios' => str_repeat('a', 501)])['comentarios']));
comprobar('sin marcar el aviso de demo', isset(erroresCon(['aviso_demo' => ''])['aviso_demo']));
comprobar('un campo enviado como array no rompe la validación', isset(erroresCon(['email' => ['x']])['email']));

echo "\nReservaRepository (SQLite en memoria)\n";
$pdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
// Misma estructura que database/schema.sql, adaptada a la sintaxis de SQLite.
$pdo->exec("CREATE TABLE reservas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL, email TEXT NOT NULL, telefono TEXT NOT NULL,
    comensales INTEGER NOT NULL, fecha TEXT NOT NULL, hora TEXT NOT NULL,
    comentarios TEXT NULL,
    estado TEXT NOT NULL DEFAULT 'pendiente',
    creada_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)");
$repositorio = new ReservaRepository($pdo);

$id = $repositorio->guardar($resultado['datos']);
$guardada = $repositorio->buscarPorId($id);
comprobar('guarda la reserva y devuelve su id', $id === 1);
comprobar('la reserva se guarda como "pendiente"', $guardada['estado'] === 'pendiente');
comprobar('los comentarios vacíos se guardan como NULL', $guardada['comentarios'] === null);

$malicioso = array_merge($resultado['datos'], ['nombre' => "Robert'); DROP TABLE reservas;--"]);
$idMalicioso = $repositorio->buscarPorId($repositorio->guardar($malicioso));
comprobar('un intento de inyección SQL se guarda como texto normal', $idMalicioso['nombre'] === "Robert'); DROP TABLE reservas;--");
comprobar('la tabla sigue existiendo', (int) $pdo->query('SELECT COUNT(*) FROM reservas')->fetchColumn() === 2);
comprobar('buscar un id inexistente devuelve null', $repositorio->buscarPorId(999) === null);

echo "\nCsrf\n";
$_SESSION = [];
$token = Csrf::token();
comprobar('genera un token de 64 caracteres', strlen($token) === 64);
comprobar('el mismo token se reutiliza en la sesión', Csrf::token() === $token);
comprobar('acepta el token correcto', Csrf::esValido($token));
comprobar('rechaza un token falso', !Csrf::esValido('falso'));
comprobar('rechaza un token ausente', !Csrf::esValido(null));

echo "\nCarta (macros y etiquetas)\n";
comprobar('kcal: 48 g prot + 72 g hidr + 14 g grasa = 606 kcal', kcal(['proteina' => 48, 'hidratos' => 72, 'grasas' => 14]) === 606);
comprobar('kcal: sin macros = 0', kcal(['proteina' => 0, 'hidratos' => 0, 'grasas' => 0]) === 0);
$platoPrueba = ['vegano' => true, 'macros' => ['proteina' => 30, 'hidratos' => 20, 'grasas' => 10]];
comprobar('30 g de proteína cuenta como "alto en proteína"', in_array('proteina', etiquetasPlato($platoPrueba), true));
comprobar('290 kcal cuenta como "ligero"', in_array('ligero', etiquetasPlato($platoPrueba), true));
comprobar('la etiqueta vegano sale del dato del plato', in_array('vegano', etiquetasPlato($platoPrueba), true));
comprobar('una bebida sin macros no lleva etiquetas de macros', etiquetasPlato(['vegano' => false, 'macros' => null]) === []);
$carta = require __DIR__ . '/../config/carta.php';
$platosValidos = true;
foreach ($carta as $categoria) {
    foreach ($categoria['platos'] as $plato) {
        $platosValidos = $platosValidos && $plato['precio'] > 0 && isset($plato['nombre'], $plato['vegano'])
            && ($plato['macros'] === null || isset($plato['macros']['proteina'], $plato['macros']['hidratos'], $plato['macros']['grasas']));
    }
}
comprobar('todos los platos de la carta tienen nombre, precio y macros bien formados', $platosValidos);

echo "\n{$pruebas} pruebas, {$fallos} fallos\n";
exit($fallos === 0 ? 0 : 1);
