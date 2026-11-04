<?php
/**
 * Funciones pequeñas que se usan en las plantillas.
 */

// Umbrales para las etiquetas y filtros de la carta.
const PROTEINA_ALTA_GRAMOS = 30;
const KCAL_LIGERO = 500;

/** Escapa un texto antes de mostrarlo en HTML (evita XSS). */
function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Formatea un precio en euros al estilo español: 15,50 €. */
function precio(float $importe): string
{
    return number_format($importe, 2, ',', '.') . ' €';
}

/**
 * Calorías de una ración a partir de sus macros (factores de Atwater):
 * 4 kcal por gramo de proteína e hidratos, 9 kcal por gramo de grasa.
 */
function kcal(array $macros): int
{
    return (int) round($macros['proteina'] * 4 + $macros['hidratos'] * 4 + $macros['grasas'] * 9);
}

/** Etiquetas de un plato para mostrarlas y filtrar la carta. */
function etiquetasPlato(array $plato): array
{
    $etiquetas = [];
    if ($plato['macros'] !== null && $plato['macros']['proteina'] >= PROTEINA_ALTA_GRAMOS) {
        $etiquetas[] = 'proteina';
    }
    if ($plato['macros'] !== null && kcal($plato['macros']) < KCAL_LIGERO) {
        $etiquetas[] = 'ligero';
    }
    if ($plato['vegano']) {
        $etiquetas[] = 'vegano';
    }
    return $etiquetas;
}

/** Texto que se muestra para cada etiqueta de la carta. */
function nombreEtiqueta(string $etiqueta): string
{
    $nombres = ['proteina' => 'Alto en proteína', 'ligero' => 'Ligero', 'vegano' => 'Vegano'];
    return $nombres[$etiqueta] ?? $etiqueta;
}

/** Guarda un mensaje para mostrarlo tras la siguiente redirección (y solo una vez). */
function guardarFlash(array $datos): void
{
    $_SESSION['flash'] = $datos;
}

function leerFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
