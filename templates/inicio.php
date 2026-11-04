<?php
/**
 * Plantilla de la portada de MacroFit.
 * Variables que recibe de public/index.php: $horario, $reglas, $carta, $flash, $errores,
 * $antiguo, $textoCerrados y $datosFormulario.
 * Todo texto que viene de fuera (o del usuario) se escapa con e().
 */

/** Atributos ARIA de un campo según tenga o no error. */
$ariaCampo = function (string $campo, ?string $ayuda = null) use ($errores): string {
    $describe = array_filter([$ayuda, isset($errores[$campo]) ? "error-{$campo}" : null]);
    $html = $describe ? ' aria-describedby="' . e(implode(' ', $describe)) . '"' : '';
    return $html . (isset($errores[$campo]) ? ' aria-invalid="true"' : '');
};

/** Párrafo de error de un campo (vacío si no hay error, pero existe para que el JS lo use). */
$errorCampo = function (string $campo) use ($errores): string {
    $mensaje = $errores[$campo] ?? '';
    return '<p class="campo__error" id="error-' . e($campo) . '"' . ($mensaje === '' ? ' hidden' : '') . '>' . e($mensaje) . '</p>';
};

$nombresCampos = [
    'nombre' => 'Nombre y apellidos', 'email' => 'Correo electrónico', 'telefono' => 'Teléfono',
    'comensales' => 'Comensales', 'fecha' => 'Fecha', 'hora' => 'Hora',
    'comentarios' => 'Comentarios', 'aviso_demo' => 'Aviso de demostración',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MacroFit · Cocina fit en Lorca (restaurante ficticio)</title>
    <meta name="description" content="Proyecto de demostración: web de un restaurante fit ficticio en Lorca, con calorías y proteínas de cada plato y reservas en PHP y MySQL.">
    <meta name="robots" content="noindex">
    <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
    <link rel="preload" href="assets/fonts/archivo-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body id="inicio">

<p class="aviso-demo">
    <strong>¡Ojo!</strong> Esto es una web de demostración: MacroFit es un restaurante inventado y las reservas no son reales.
</p>

<header class="cabecera">
    <a class="cabecera__marca" href="#inicio">MacroFit</a>
    <nav aria-label="Principal">
        <ul class="cabecera__enlaces">
            <li><a href="#carta">Carta</a></li>
            <li><a href="#horarios">Horarios</a></li>
            <li><a href="#visita">Dónde estamos</a></li>
        </ul>
    </nav>
    <a class="boton boton--pequeno" href="#reserva">Reservar</a>
</header>

<main>
    <!-- PORTADA -->
    <section class="portada" aria-labelledby="titulo-principal">
        <img class="portada__fondo" src="assets/img/fotos/verduras.webp" alt="" width="1920" height="1080">
        <div class="portada__tarjeta">
            <h1 class="portada__titulo" id="titulo-principal">¡Bienvenido a MacroFit!</h1>
            <div class="portada__cuerpo">
                <p class="portada__lema">Cocina fit en Lorca. Cada plato con sus calorías y su proteína a la vista, para que comas como entrenas.</p>
                <p class="portada__hoy">
                    <?= e($horario->resumenDeHoy()) ?>
                </p>
            </div>
            <div class="portada__pie">
                <span class="portada__credito">Restaurante ficticio · demo</span>
                <a class="boton" href="#reserva">¡Reserva tu mesa!</a>
                <a class="portada__enlace" href="#carta">Ver la carta</a>
            </div>
        </div>
    </section>

    <!-- CONCEPTO -->
    <section class="concepto" aria-labelledby="titulo-concepto">
        <div class="concepto__fila">
            <h2 class="concepto__titulo" id="titulo-concepto">Aquí se come con números</h2>
            <p class="concepto__nota">
                MacroFit nace de algo muy simple: sales del gym muerto de hambre y no quieres elegir entre comer bien o comer rico.
                Pesamos cada ración y te ponemos los números delante. Tú decides.
            </p>
        </div>
        <ul class="concepto__datos">
            <li><strong>Macros en todo</strong><span>Calorías, proteína, hidratos y grasas en cada plato.</span></li>
            <li><strong>+30 g de proteína</strong><span>En todos los bowls y platos a la plancha.</span></li>
            <li><strong>Cero fritos</strong><span>Plancha, horno y vapor. Así de fácil.</span></li>
            <li><strong>Opciones veganas</strong><span>Marcadas en la carta para que no tengas que preguntar.</span></li>
        </ul>
    </section>

    <!-- CARTA -->
    <section class="carta" id="carta" aria-labelledby="titulo-carta">
        <div class="carta__lateral">
            <h2 class="carta__titulo" id="titulo-carta">Carta</h2>
            <p class="carta__nota">Valores aproximados por ración. Las calorías salen de los macros, sin trampas. Precios con IVA.</p>
            <figure class="carta__foto">
                <img src="assets/img/fotos/poke-atun.webp" alt="Bowl de atún marinado con cebolleta, pimiento y sésamo" width="1200" height="900" loading="lazy">
                <figcaption>Foto ilustrativa.</figcaption>
            </figure>
        </div>

        <div class="carta__contenido">
            <div class="filtros" role="group" aria-label="Filtrar la carta">
                <button type="button" class="filtro" data-filtro="todo" aria-pressed="true">Todo</button>
                <button type="button" class="filtro" data-filtro="proteina" aria-pressed="false">Más de <?= PROTEINA_ALTA_GRAMOS ?> g de proteína</button>
                <button type="button" class="filtro" data-filtro="ligero" aria-pressed="false">Menos de <?= KCAL_LIGERO ?> kcal</button>
                <button type="button" class="filtro" data-filtro="vegano" aria-pressed="false">Veganos</button>
            </div>
            <p class="filtros__resultado" id="resultado-filtro" aria-live="polite"></p>

            <div class="carta__lista">
                <?php foreach ($carta as $indice => $categoria): ?>
                    <details class="carta__categoria" <?= $indice === 0 ? 'open' : '' ?>>
                        <summary><?= e($categoria['categoria']) ?></summary>
                        <div class="carta__panel">
                            <?php if ($categoria['nota']): ?>
                                <p class="carta__aviso"><?= e($categoria['nota']) ?></p>
                            <?php endif; ?>
                            <ul class="carta__platos">
                                <?php foreach ($categoria['platos'] as $plato): ?>
                                    <?php $etiquetas = etiquetasPlato($plato); ?>
                                    <li class="plato" data-etiquetas="<?= e(implode(' ', $etiquetas)) ?>">
                                        <span class="plato__nombre"><?= e($plato['nombre']) ?></span>
                                        <span class="plato__precio"><?= e(precio($plato['precio'])) ?></span>
                                        <?php if ($plato['macros']): ?>
                                            <span class="plato__principal">
                                                <strong><?= kcal($plato['macros']) ?> kcal</strong>
                                                <strong><?= e($plato['macros']['proteina']) ?> g proteína</strong>
                                            </span>
                                            <span class="plato__macros">
                                                <?= e($plato['macros']['hidratos']) ?> g hidratos · <?= e($plato['macros']['grasas']) ?> g grasas
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($plato['descripcion']): ?>
                                            <span class="plato__descripcion"><?= e($plato['descripcion']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($etiquetas): ?>
                                            <span class="plato__etiquetas">
                                                <?php foreach ($etiquetas as $etiqueta): ?>
                                                    <span class="etiqueta etiqueta--<?= e($etiqueta) ?>"><?= e(nombreEtiqueta($etiqueta)) ?></span>
                                                <?php endforeach; ?>
                                            </span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- GALERÍA -->
    <section class="galeria" aria-labelledby="titulo-galeria">
        <h2 class="galeria__titulo" id="titulo-galeria">Así pinta la cosa</h2>
        <div class="galeria__fotos">
            <figure class="galeria__grande">
                <img src="assets/img/fotos/salmon-arroz.webp" alt="Salmón a la plancha con espárragos sobre arroz" width="1200" height="900" loading="lazy">
            </figure>
            <figure>
                <img src="assets/img/fotos/poke-tofu.webp" alt="Bowl de tofu con edamame, aguacate y algas" width="1200" height="900" loading="lazy">
            </figure>
            <figure>
                <img src="assets/img/fotos/tostada-aguacate.webp" alt="Tostadas de aguacate con huevos cocidos, tomates cherry y rúcula" width="1200" height="900" loading="lazy">
            </figure>
            <figure>
                <img src="assets/img/fotos/tortitas-avena.webp" alt="Tortitas con fresas y arándanos" width="1200" height="900" loading="lazy">
            </figure>
            <figure>
                <img src="assets/img/fotos/batido-verde.webp" alt="Batido verde de kiwi con hojas de menta" width="1200" height="900" loading="lazy">
            </figure>
        </div>
        <p class="galeria__nota">Fotos ilustrativas de platos parecidos a los nuestros.</p>
    </section>

    <!-- HORARIOS: se generan desde config/horarios.php -->
    <section class="horarios" id="horarios" aria-labelledby="titulo-horarios">
        <div class="horarios__texto">
            <h2 class="horarios__titulo" id="titulo-horarios">Horarios</h2>
            <p>Comida casi todos los días y cena cuando llega el finde. <?= e($textoCerrados) ?> Los músculos también crecen descansando (vosotros id a hacer pierna).</p>
            <p>Puedes llegar cada <?= e($reglas['intervalo_minutos']) ?> minutos y la última mesa se sienta <?= e($reglas['ultima_reserva_antes_cierre_minutos']) ?> minutos antes de cerrar. Reservas desde hoy hasta <?= e($reglas['dias_maximos_antelacion']) ?> días vista; para hoy, con al menos <?= e($reglas['antelacion_minima_minutos']) ?> minutos de margen.</p>
        </div>
        <table class="horarios__tabla">
            <caption class="visualmente-oculto">Horario de apertura por días de la semana</caption>
            <thead>
                <tr><th scope="col">Día</th><th scope="col">Comida</th><th scope="col">Cena</th></tr>
            </thead>
            <tbody>
                <?php foreach ($horario->semanaParaMostrar() as $dia): ?>
                    <tr class="<?= $dia['cerrado'] ? 'es-cerrado' : '' ?> <?= $dia['es_hoy'] ? 'es-hoy' : '' ?>">
                        <th scope="row">
                            <?= e(ucfirst($dia['nombre'])) ?>
                            <?php if ($dia['es_hoy']): ?><span class="horarios__hoy">hoy</span><?php endif; ?>
                        </th>
                        <?php if ($dia['cerrado']): ?>
                            <td colspan="2">Cerrado. ¡Día de descanso!</td>
                        <?php else: ?>
                            <?php foreach (['comida', 'cena'] as $servicio): ?>
                                <td>
                                    <?= $dia[$servicio]
                                        ? e($dia[$servicio]['apertura'] . '–' . $dia[$servicio]['cierre'])
                                        : '<span aria-hidden="true">—</span><span class="visualmente-oculto">Sin servicio</span>' ?>
                                </td>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- RESERVA -->
    <section class="reserva" id="reserva" aria-labelledby="titulo-reserva">
        <div class="reserva__boleto">
            <div class="reserva__talon">
                <h2 class="reserva__titulo" id="titulo-reserva">¡Pide mesa!</h2>
                <p>Rellena esto y te escribimos para confirmarte. De momento es una <strong>solicitud</strong>: la mesa no está cerrada hasta que te contestemos.</p>
                <ul class="reserva__reglas">
                    <li>De <?= e($reglas['comensales_minimo']) ?> a <?= e($reglas['comensales_maximo']) ?> personas. ¿Venís con todo el box? Llámanos.</li>
                    <li>Las horas cambian según el día que elijas.</li>
                    <li><?= e($textoCerrados) ?></li>
                </ul>
            </div>

            <form class="formulario" action="reservar.php" method="post" id="formulario-reserva">
                <?php if ($flash && $flash['tipo'] === 'ok'): ?>
                    <div class="mensaje mensaje--ok" role="status" tabindex="-1" id="mensaje-formulario">
                        <p class="mensaje__titulo">¡Solicitud recibida!</p>
                        <p><?= e($flash['mensaje']) ?></p>
                    </div>
                <?php elseif ($flash && $flash['tipo'] === 'error'): ?>
                    <div class="mensaje mensaje--error" role="alert" tabindex="-1" id="mensaje-formulario">
                        <p class="mensaje__titulo"><?= e($flash['mensaje']) ?></p>
                        <?php if ($errores): ?>
                            <ul>
                                <?php foreach ($errores as $campo => $mensaje): ?>
                                    <li><a href="#campo-<?= e($campo) ?>"><?= e($nombresCampos[$campo] ?? $campo) ?>: <?= e($mensaje) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">

                <!-- Campo trampa para bots: oculto a las personas -->
                <div class="trampa" aria-hidden="true">
                    <label for="campo-web">Deja este campo vacío</label>
                    <input type="text" id="campo-web" name="web" tabindex="-1" autocomplete="off">
                </div>

                <div class="campo campo--ancho">
                    <label for="campo-nombre">Nombre y apellidos</label>
                    <input type="text" id="campo-nombre" name="nombre" required minlength="3" maxlength="100"
                           autocomplete="name" value="<?= e($antiguo('nombre')) ?>"<?= $ariaCampo('nombre') ?>>
                    <?= $errorCampo('nombre') ?>
                </div>

                <div class="campo">
                    <label for="campo-email">Correo electrónico</label>
                    <input type="email" id="campo-email" name="email" required maxlength="150"
                           autocomplete="email" value="<?= e($antiguo('email')) ?>"<?= $ariaCampo('email') ?>>
                    <?= $errorCampo('email') ?>
                </div>

                <div class="campo">
                    <label for="campo-telefono">Teléfono</label>
                    <input type="tel" id="campo-telefono" name="telefono" required maxlength="20"
                           autocomplete="tel" inputmode="tel" value="<?= e($antiguo('telefono')) ?>"<?= $ariaCampo('telefono') ?>>
                    <?= $errorCampo('telefono') ?>
                </div>

                <div class="campo">
                    <label for="campo-comensales">¿Cuántos venís?</label>
                    <select id="campo-comensales" name="comensales" required<?= $ariaCampo('comensales') ?>>
                        <option value="">Elige</option>
                        <?php for ($n = $reglas['comensales_minimo']; $n <= $reglas['comensales_maximo']; $n++): ?>
                            <option value="<?= $n ?>" <?= $antiguo('comensales') === (string) $n ? 'selected' : '' ?>>
                                <?= $n ?> <?= $n === 1 ? 'persona' : 'personas' ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <?= $errorCampo('comensales') ?>
                </div>

                <div class="campo">
                    <label for="campo-fecha">Fecha</label>
                    <input type="date" id="campo-fecha" name="fecha" required
                           min="<?= e($horario->fechaMinima()->format('Y-m-d')) ?>"
                           max="<?= e($horario->fechaMaxima()->format('Y-m-d')) ?>"
                           value="<?= e($antiguo('fecha')) ?>"<?= $ariaCampo('fecha', 'ayuda-fecha') ?>>
                    <p class="campo__ayuda" id="ayuda-fecha"><?= e($textoCerrados) ?></p>
                    <?= $errorCampo('fecha') ?>
                </div>

                <div class="campo">
                    <label for="campo-hora">Hora de llegada</label>
                    <select id="campo-hora" name="hora" required<?= $ariaCampo('hora') ?>>
                        <option value="">Elige</option>
                        <?php foreach ($horario->todasLasHorasPosibles() as $hora): ?>
                            <option value="<?= e($hora) ?>" <?= $antiguo('hora') === $hora ? 'selected' : '' ?>><?= e($hora) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= $errorCampo('hora') ?>
                </div>

                <div class="campo campo--ancho">
                    <label for="campo-comentarios">¿Algo que debamos saber? <span class="campo__opcional">(opcional)</span></label>
                    <textarea id="campo-comentarios" name="comentarios" rows="3" maxlength="500"
                              placeholder="Alergias, una trona, que venís justo de entrenar…"
                              <?= $ariaCampo('comentarios') ?>><?= e($antiguo('comentarios')) ?></textarea>
                    <?= $errorCampo('comentarios') ?>
                </div>

                <div class="campo campo--ancho campo--casilla">
                    <input type="checkbox" id="campo-aviso_demo" name="aviso_demo" value="1" required
                           <?= $antiguo('aviso_demo') === '1' ? 'checked' : '' ?><?= $ariaCampo('aviso_demo') ?>>
                    <label for="campo-aviso_demo">Sé que MacroFit es un restaurante inventado y he usado datos de prueba.</label>
                    <?= $errorCampo('aviso_demo') ?>
                </div>

                <div class="campo--ancho">
                    <button class="boton" type="submit">¡Enviar solicitud!</button>
                </div>
            </form>
        </div>
    </section>

    <!-- DÓNDE ESTAMOS -->
    <section class="visita" id="visita" aria-labelledby="titulo-visita">
        <img class="visita__mapa" src="assets/img/mapa.svg" alt="Plano dibujado e inventado de la zona, con la avenida donde está el restaurante." width="1600" height="1000" loading="lazy">
        <div class="visita__tarjeta">
            <h2 class="visita__titulo" id="titulo-visita">¿Dónde estamos?</h2>
            <p>Avenida del Batido, 1<br>30800 Lorca, Murcia <span class="visita__nota">(dirección inventada)</span></p>
            <p><?= e($horario->resumenDeHoy()) ?> <a href="#horarios">Ver horarios</a></p>
            <p>
                <a href="tel:+34968000000">968 00 00 00</a><br>
                <a href="mailto:hola@macrofit.example">hola@macrofit.example</a>
            </p>
            <a class="boton boton--bloque" href="#reserva">¡Reserva tu mesa!</a>
        </div>
    </section>
</main>

<footer class="pie">
    <div class="pie__reverso">
        <div class="pie__mensaje">
            <h2 class="pie__titulo">¡Adiós! <span class="pie__subtitulo">Nos vemos después del entreno</span></h2>
            <p>MacroFit no existe (todavía): es un restaurante inventado para mi portfolio de Desarrollo de Aplicaciones Web. Las reservas que hagas aquí no llegan a ninguna cocina.</p>
        </div>
        <nav class="pie__direccion" aria-label="Pie de página">
            <ul>
                <li><a href="#carta">Carta</a></li>
                <li><a href="#horarios">Horarios</a></li>
                <li><a href="#reserva">Reservar</a></li>
                <li><a href="#visita">Dónde estamos</a></li>
            </ul>
        </nav>
        <p class="pie__imprenta">© <?= e($horario->ahora()->format('Y')) ?> MacroFit · Restaurante ficticio</p>
    </div>
</footer>

<a class="subir" href="#inicio" aria-label="Volver arriba">
    <svg aria-hidden="true" viewBox="0 0 24 24" width="22" height="22"><path d="M12 5l-7 7h4.5v7h5v-7H19z" fill="currentColor"/></svg>
    <span>Arriba</span>
</a>

<script type="application/json" id="datos-reserva"><?= json_encode($datosFormulario, JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR) ?></script>
</body>
</html>
