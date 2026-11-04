<?php

/**
 * Lógica de horarios: qué días abre el restaurante y qué horas se pueden reservar.
 *
 * Recibe la configuración de config/horarios.php y, opcionalmente, la fecha y hora
 * "actuales". Poder indicar el "ahora" permite probar la clase con fechas fijas.
 */
class Horario
{
    private const NOMBRES_DIAS = [
        1 => 'lunes', 2 => 'martes', 3 => 'miércoles', 4 => 'jueves',
        5 => 'viernes', 6 => 'sábado', 7 => 'domingo',
    ];

    private const NOMBRES_MESES = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
        7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];

    private array $semana;
    private array $reglas;
    private DateTimeZone $zona;
    private DateTimeImmutable $ahora;

    public function __construct(array $config, ?DateTimeImmutable $ahora = null)
    {
        $this->semana = $config['semana'];
        $this->reglas = $config['reservas'];
        $this->zona = new DateTimeZone($config['zona_horaria']);
        $this->ahora = $ahora ?? new DateTimeImmutable('now', $this->zona);
    }

    public function reglas(): array
    {
        return $this->reglas;
    }

    public function ahora(): DateTimeImmutable
    {
        return $this->ahora;
    }

    /** Servicios (comida, cena...) de un día concreto. Vacío = cerrado. */
    public function serviciosDelDia(DateTimeInterface $fecha): array
    {
        return $this->semana[(int) $fecha->format('N')] ?? [];
    }

    public function estaAbierto(DateTimeInterface $fecha): bool
    {
        return $this->serviciosDelDia($fecha) !== [];
    }

    /**
     * Convierte un texto "AAAA-MM-DD" en fecha (en la zona horaria del restaurante).
     * Devuelve null si el texto no es una fecha real (por ejemplo, 2026-02-30).
     */
    public function crearFecha(string $texto): ?DateTimeImmutable
    {
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $texto, $this->zona);
        if ($fecha === false || $fecha->format('Y-m-d') !== $texto) {
            return null;
        }
        return $fecha;
    }

    /** Nombres de los días de la semana que el restaurante no abre, p. ej. ['lunes']. */
    public function diasCerrados(): array
    {
        $cerrados = [];
        foreach (self::NOMBRES_DIAS as $numero => $nombre) {
            if (($this->semana[$numero] ?? []) === []) {
                $cerrados[] = $nombre;
            }
        }
        return $cerrados;
    }

    /** Primer día que se puede reservar: hoy. */
    public function fechaMinima(): DateTimeImmutable
    {
        return $this->ahora->setTime(0, 0);
    }

    /** Último día que se puede reservar. */
    public function fechaMaxima(): DateTimeImmutable
    {
        return $this->fechaMinima()->modify('+' . $this->reglas['dias_maximos_antelacion'] . ' days');
    }

    public function estaEnRangoDeReserva(DateTimeImmutable $fecha): bool
    {
        $dia = $fecha->setTime(0, 0);
        return $dia >= $this->fechaMinima() && $dia <= $this->fechaMaxima();
    }

    /**
     * Horas de llegada que se pueden reservar en una fecha, en formato "HH:MM".
     * Devuelve un array vacío si ese día cierra o está fuera del rango permitido.
     * Si la fecha es hoy, descarta las horas que ya han pasado (con margen).
     */
    public function horasDisponibles(DateTimeImmutable $fecha): array
    {
        $dia = $fecha->setTime(0, 0);
        if (!$this->estaEnRangoDeReserva($dia)) {
            return [];
        }

        $intervalo = $this->reglas['intervalo_minutos'];
        $margenCierre = $this->reglas['ultima_reserva_antes_cierre_minutos'];
        $primeraHoraPosible = $this->ahora->modify('+' . $this->reglas['antelacion_minima_minutos'] . ' minutes');

        $horas = [];
        foreach ($this->serviciosDelDia($dia) as $servicio) {
            $hora = $this->combinar($dia, $servicio['apertura']);
            $ultima = $this->combinar($dia, $servicio['cierre'])->modify("-{$margenCierre} minutes");

            while ($hora <= $ultima) {
                if ($hora >= $primeraHoraPosible) {
                    $horas[] = $hora->format('H:i');
                }
                $hora = $hora->modify("+{$intervalo} minutes");
            }
        }
        return $horas;
    }

    public function esHoraDisponible(DateTimeImmutable $fecha, string $hora): bool
    {
        return in_array($hora, $this->horasDisponibles($fecha), true);
    }

    /**
     * Disponibilidad de todos los días reservables: ['2026-11-03' => ['13:00', ...], ...].
     * Se envía al navegador en JSON para que el formulario no tenga que repetir esta lógica.
     */
    public function disponibilidadCompleta(): array
    {
        $resultado = [];
        $dia = $this->fechaMinima();
        while ($dia <= $this->fechaMaxima()) {
            $resultado[$dia->format('Y-m-d')] = $this->horasDisponibles($dia);
            $dia = $dia->modify('+1 day');
        }
        return $resultado;
    }

    /** Todas las horas posibles de la semana, ordenadas (para el desplegable sin JavaScript). */
    public function todasLasHorasPosibles(): array
    {
        $horas = [];
        $intervalo = $this->reglas['intervalo_minutos'];
        $margenCierre = $this->reglas['ultima_reserva_antes_cierre_minutos'];
        $base = new DateTimeImmutable('2000-01-01', $this->zona);

        foreach ($this->semana as $servicios) {
            foreach ($servicios as $servicio) {
                $hora = $this->combinar($base, $servicio['apertura']);
                $ultima = $this->combinar($base, $servicio['cierre'])->modify("-{$margenCierre} minutes");
                while ($hora <= $ultima) {
                    $horas[$hora->format('H:i')] = true;
                    $hora = $hora->modify("+{$intervalo} minutes");
                }
            }
        }
        $horas = array_keys($horas);
        sort($horas);
        return $horas;
    }

    /** La semana de lunes a domingo, lista para pintarla en la tabla de horarios. */
    public function semanaParaMostrar(): array
    {
        $hoy = (int) $this->ahora->format('N');
        $dias = [];
        foreach (self::NOMBRES_DIAS as $numero => $nombre) {
            $servicios = $this->semana[$numero] ?? [];
            $dias[] = [
                'nombre' => $nombre,
                'es_hoy' => $numero === $hoy,
                'comida' => $this->buscarServicio($servicios, 'Comida'),
                'cena' => $this->buscarServicio($servicios, 'Cena'),
                'cerrado' => $servicios === [],
            ];
        }
        return $dias;
    }

    /**
     * Frase sobre el día de hoy, p. ej.
     * "Hoy, domingo: comida de 13:00 a 16:30. ¡Te esperamos!" o
     * "Hoy descansamos. ¡Volvemos el martes a las 13:00!"
     */
    public function resumenDeHoy(): string
    {
        $hoy = $this->ahora;
        $nombreHoy = self::NOMBRES_DIAS[(int) $hoy->format('N')];
        $servicios = $this->serviciosDelDia($hoy);

        if ($servicios !== []) {
            $partes = array_map(
                fn ($s) => strtolower($s['nombre']) . " de {$s['apertura']} a {$s['cierre']}",
                $servicios
            );
            return "Hoy, {$nombreHoy}: " . implode(' y ', $partes) . '. ¡Te esperamos!';
        }

        for ($i = 1; $i <= 7; $i++) {
            $dia = $hoy->modify("+{$i} days");
            $siguientes = $this->serviciosDelDia($dia);
            if ($siguientes !== []) {
                $nombre = self::NOMBRES_DIAS[(int) $dia->format('N')];
                return "Hoy descansamos. ¡Volvemos el {$nombre} a las {$siguientes[0]['apertura']}!";
            }
        }
        return 'Ahora mismo no tenemos servicios programados.';
    }

    /** "martes, 3 de noviembre" sin depender de la extensión intl. */
    public static function fechaLarga(DateTimeInterface $fecha): string
    {
        return self::NOMBRES_DIAS[(int) $fecha->format('N')] . ', '
            . $fecha->format('j') . ' de ' . self::NOMBRES_MESES[(int) $fecha->format('n')];
    }

    private function combinar(DateTimeImmutable $dia, string $hora): DateTimeImmutable
    {
        [$h, $m] = array_map('intval', explode(':', $hora));
        return $dia->setTime($h, $m);
    }

    private function buscarServicio(array $servicios, string $nombre): ?array
    {
        foreach ($servicios as $servicio) {
            if ($servicio['nombre'] === $nombre) {
                return $servicio;
            }
        }
        return null;
    }
}
