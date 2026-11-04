<?php

/**
 * Valida y limpia los datos de una solicitud de reserva.
 *
 * Es la validación que manda: la del navegador (HTML + JavaScript) solo ayuda al
 * usuario, pero cualquiera puede saltársela enviando la petición a mano.
 */
class ValidadorReserva
{
    private Horario $horario;

    public function __construct(Horario $horario)
    {
        $this->horario = $horario;
    }

    /**
     * @return array{datos: array, errores: array<string, string>}
     *         'datos' contiene los valores limpios; 'errores' un mensaje por campo.
     */
    public function validar(array $entrada): array
    {
        $reglas = $this->horario->reglas();
        $errores = [];

        $datos = [
            'nombre' => $this->texto($entrada, 'nombre'),
            'email' => $this->texto($entrada, 'email'),
            'telefono' => $this->texto($entrada, 'telefono'),
            'comensales' => $this->texto($entrada, 'comensales'),
            'fecha' => $this->texto($entrada, 'fecha'),
            'hora' => $this->texto($entrada, 'hora'),
            'comentarios' => $this->texto($entrada, 'comentarios'),
        ];

        // Nombre y apellidos
        $largoNombre = mb_strlen($datos['nombre']);
        if ($datos['nombre'] === '') {
            $errores['nombre'] = 'Escribe tu nombre y apellidos.';
        } elseif ($largoNombre < 3 || $largoNombre > 100) {
            $errores['nombre'] = 'El nombre debe tener entre 3 y 100 caracteres.';
        } elseif (!preg_match("/^[\p{L}][\p{L} .'-]*$/u", $datos['nombre'])) {
            $errores['nombre'] = 'El nombre solo puede contener letras, espacios, puntos, guiones y apóstrofos.';
        }

        // Correo electrónico
        if ($datos['email'] === '') {
            $errores['email'] = 'Escribe tu correo electrónico.';
        } elseif (mb_strlen($datos['email']) > 150 || filter_var($datos['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errores['email'] = 'El correo no tiene un formato válido (ejemplo: nombre@dominio.es).';
        }

        // Teléfono: se admiten espacios, guiones y prefijo +; se guardan solo los dígitos (y el +).
        $telefonoLimpio = preg_replace('/[\s.-]/', '', $datos['telefono']);
        if ($datos['telefono'] === '') {
            $errores['telefono'] = 'Escribe un teléfono de contacto.';
        } elseif (!preg_match('/^\+?\d{9,15}$/', $telefonoLimpio)) {
            $errores['telefono'] = 'El teléfono debe tener entre 9 y 15 cifras (puede empezar por +).';
        } else {
            $datos['telefono'] = $telefonoLimpio;
        }

        // Comensales
        $comensales = filter_var($datos['comensales'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $reglas['comensales_minimo'], 'max_range' => $reglas['comensales_maximo']],
        ]);
        if ($datos['comensales'] === '') {
            $errores['comensales'] = 'Indica cuántas personas venís.';
        } elseif ($comensales === false) {
            $errores['comensales'] = "Las reservas online son de {$reglas['comensales_minimo']} a {$reglas['comensales_maximo']} personas.";
        } else {
            $datos['comensales'] = $comensales;
        }

        // Fecha
        $fecha = null;
        if ($datos['fecha'] === '') {
            $errores['fecha'] = 'Elige una fecha.';
        } elseif (($fecha = $this->horario->crearFecha($datos['fecha'])) === null) {
            $errores['fecha'] = 'La fecha no es válida.';
        } elseif ($fecha < $this->horario->fechaMinima()) {
            $errores['fecha'] = 'No se puede reservar en una fecha pasada.';
        } elseif ($fecha > $this->horario->fechaMaxima()) {
            $errores['fecha'] = "Solo se puede reservar con {$reglas['dias_maximos_antelacion']} días de antelación como máximo.";
        } elseif (!$this->horario->estaAbierto($fecha)) {
            $errores['fecha'] = 'Ese día el restaurante está cerrado. Elige otra fecha.';
        } elseif ($this->horario->horasDisponibles($fecha) === []) {
            $errores['fecha'] = 'Ya no quedan horas disponibles ese día. Elige otra fecha.';
        }

        // Hora (solo tiene sentido comprobarla si la fecha es correcta)
        if ($datos['hora'] === '') {
            $errores['hora'] = 'Elige una hora.';
        } elseif (!preg_match('/^\d{2}:\d{2}$/', $datos['hora'])) {
            $errores['hora'] = 'La hora no es válida.';
        } elseif (!isset($errores['fecha']) && !$this->horario->esHoraDisponible($fecha, $datos['hora'])) {
            $errores['hora'] = 'Esa hora no está disponible el día elegido.';
        }

        // Comentarios (opcional)
        if (mb_strlen($datos['comentarios']) > 500) {
            $errores['comentarios'] = 'Los comentarios no pueden superar los 500 caracteres.';
        }

        // Aviso de demostración: el usuario confirma que no usa datos reales.
        if (($entrada['aviso_demo'] ?? '') !== '1') {
            $errores['aviso_demo'] = 'Marca la casilla para confirmar que sabes que es una demo y que usas datos inventados.';
        }

        return ['datos' => $datos, 'errores' => $errores];
    }

    /** Lee un campo como texto sin espacios sobrantes; si no existe o no es texto, devuelve ''. */
    private function texto(array $entrada, string $campo): string
    {
        $valor = $entrada[$campo] ?? '';
        return is_string($valor) ? trim($valor) : '';
    }
}
