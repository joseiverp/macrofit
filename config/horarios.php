<?php
/**
 * Horarios y reglas de reserva de MacroFit.
 *
 * Este archivo es la ÚNICA fuente de verdad de los horarios. Se usa para:
 *  - pintar la tabla de horarios de la web,
 *  - calcular las horas que ofrece el formulario,
 *  - validar las reservas en el servidor.
 * Si cambias un horario aquí, cambia en toda la aplicación.
 *
 * Días de la semana en formato ISO-8601: 1 = lunes ... 7 = domingo.
 * Un día sin servicios (array vacío) significa que el restaurante cierra.
 * Las horas van en formato 24 h "HH:MM" y ningún servicio cruza la medianoche.
 */

return [
    'zona_horaria' => 'Europe/Madrid',

    'semana' => [
        1 => [], // lunes: cerrado
        2 => [
            ['nombre' => 'Comida', 'apertura' => '13:00', 'cierre' => '16:00'],
        ],
        3 => [
            ['nombre' => 'Comida', 'apertura' => '13:00', 'cierre' => '16:00'],
        ],
        4 => [
            ['nombre' => 'Comida', 'apertura' => '13:00', 'cierre' => '16:00'],
            ['nombre' => 'Cena',   'apertura' => '20:30', 'cierre' => '23:00'],
        ],
        5 => [
            ['nombre' => 'Comida', 'apertura' => '13:00', 'cierre' => '16:00'],
            ['nombre' => 'Cena',   'apertura' => '20:30', 'cierre' => '23:30'],
        ],
        6 => [
            ['nombre' => 'Comida', 'apertura' => '13:00', 'cierre' => '16:30'],
            ['nombre' => 'Cena',   'apertura' => '20:30', 'cierre' => '23:30'],
        ],
        7 => [
            ['nombre' => 'Comida', 'apertura' => '13:00', 'cierre' => '16:30'],
        ],
    ],

    'reservas' => [
        // Cada cuántos minutos se ofrece una hora de llegada (13:00, 13:30...).
        'intervalo_minutos' => 30,
        // La última reserva de cada servicio es X minutos antes del cierre.
        'ultima_reserva_antes_cierre_minutos' => 60,
        // Para reservar el mismo día, la hora debe estar al menos a X minutos vista.
        'antelacion_minima_minutos' => 60,
        // Hasta cuántos días en el futuro se puede reservar.
        'dias_maximos_antelacion' => 60,
        'comensales_minimo' => 1,
        'comensales_maximo' => 10,
    ],
];
