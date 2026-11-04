<?php
/**
 * Carta de MacroFit (restaurante ficticio). Platos, precios y macros de ejemplo.
 *
 * Cada plato guarda sus macronutrientes por ración en gramos:
 *   proteina, hidratos, grasas
 * Las calorías NO se escriben a mano: se calculan con la función kcal() de
 * src/helpers.php (4 kcal/g de proteína e hidratos, 9 kcal/g de grasa), así
 * nunca pueden contradecir a los macros.
 *
 * Bebidas sin valor nutricional relevante llevan 'macros' => null.
 */

return [
    [
        'categoria' => 'Para abrir boca',
        'nota' => null,
        'platos' => [
            ['nombre' => 'Hummus de edamame con crudités', 'descripcion' => 'Zanahoria, pepino y apio para mojar sin culpa.', 'precio' => 6.50, 'vegano' => true,
             'macros' => ['proteina' => 14, 'hidratos' => 22, 'grasas' => 14]],
            ['nombre' => 'Ensalada murciana fit', 'descripcion' => 'La de toda la vida, con bonito, huevo y el aceite justo.', 'precio' => 8.50, 'vegano' => false,
             'macros' => ['proteina' => 28, 'hidratos' => 12, 'grasas' => 17]],
            ['nombre' => 'Tartar de salmón y aguacate', 'descripcion' => 'Con sésamo tostado y lima.', 'precio' => 11.00, 'vegano' => false,
             'macros' => ['proteina' => 26, 'hidratos' => 8, 'grasas' => 28]],
            ['nombre' => 'Tostada de aguacate y huevos', 'descripcion' => 'Pan de masa madre, dos huevos cocidos y rúcula.', 'precio' => 7.50, 'vegano' => false,
             'macros' => ['proteina' => 20, 'hidratos' => 34, 'grasas' => 22]],
        ],
    ],
    [
        'categoria' => 'Bowls',
        'nota' => 'Todos con base de arroz, quinoa o boniato. Tú eliges.',
        'platos' => [
            ['nombre' => 'Bowl de pollo teriyaki', 'descripcion' => 'Arroz basmati, brócoli al vapor y salsa teriyaki casera.', 'precio' => 12.50, 'vegano' => false,
             'macros' => ['proteina' => 48, 'hidratos' => 72, 'grasas' => 14]],
            ['nombre' => 'Poke de atún', 'descripcion' => 'Atún marinado, quinoa, edamame y cebolleta.', 'precio' => 13.50, 'vegano' => false,
             'macros' => ['proteina' => 42, 'hidratos' => 55, 'grasas' => 20]],
            ['nombre' => 'Bowl de tofu y boniato', 'descripcion' => 'Tofu crujiente al horno, garbanzos y aguacate.', 'precio' => 11.50, 'vegano' => true,
             'macros' => ['proteina' => 30, 'hidratos' => 62, 'grasas' => 18]],
        ],
    ],
    [
        'categoria' => 'A la plancha',
        'nota' => null,
        'platos' => [
            ['nombre' => 'Pechuga de pavo con verduras asadas', 'descripcion' => 'El clásico que nunca falla.', 'precio' => 12.00, 'vegano' => false,
             'macros' => ['proteina' => 52, 'hidratos' => 20, 'grasas' => 16]],
            ['nombre' => 'Salmón con arroz integral y espárragos', 'descripcion' => 'Omega 3 del bueno.', 'precio' => 15.50, 'vegano' => false,
             'macros' => ['proteina' => 40, 'hidratos' => 48, 'grasas' => 28]],
            ['nombre' => 'Ternera magra con patata cocida', 'descripcion' => 'Para los días de pierna.', 'precio' => 16.00, 'vegano' => false,
             'macros' => ['proteina' => 50, 'hidratos' => 45, 'grasas' => 18]],
            ['nombre' => 'Arroz con pollo y verduras', 'descripcion' => 'Nuestro guiño a Murcia, en versión fit. Por persona, mínimo dos.', 'precio' => 13.00, 'vegano' => false,
             'macros' => ['proteina' => 38, 'hidratos' => 78, 'grasas' => 12]],
        ],
    ],
    [
        'categoria' => 'Postres (sí, también fit)',
        'nota' => null,
        'platos' => [
            ['nombre' => 'Tortitas de avena con frutos rojos', 'descripcion' => 'Sin azúcar añadido.', 'precio' => 5.50, 'vegano' => false,
             'macros' => ['proteina' => 22, 'hidratos' => 40, 'grasas' => 8]],
            ['nombre' => 'Yogur griego 0 % con miel y nueces', 'descripcion' => null, 'precio' => 4.50, 'vegano' => false,
             'macros' => ['proteina' => 20, 'hidratos' => 22, 'grasas' => 10]],
            ['nombre' => 'Mousse de chocolate proteica', 'descripcion' => 'Sabe a postre de verdad, palabra.', 'precio' => 5.00, 'vegano' => false,
             'macros' => ['proteina' => 24, 'hidratos' => 14, 'grasas' => 6]],
        ],
    ],
    [
        'categoria' => 'Batidos y bebidas',
        'nota' => null,
        'platos' => [
            ['nombre' => 'Batido de proteína', 'descripcion' => 'Vainilla o chocolate, con leche o bebida de avena.', 'precio' => 4.50, 'vegano' => false,
             'macros' => ['proteina' => 30, 'hidratos' => 18, 'grasas' => 5]],
            ['nombre' => 'Batido verde', 'descripcion' => 'Kiwi, espinaca, plátano y manzana.', 'precio' => 4.00, 'vegano' => true,
             'macros' => ['proteina' => 4, 'hidratos' => 38, 'grasas' => 1]],
            ['nombre' => 'Agua, café o kombucha', 'descripcion' => null, 'precio' => 2.00, 'vegano' => true,
             'macros' => null],
        ],
    ],
];
