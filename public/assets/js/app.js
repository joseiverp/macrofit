/*
 * MacroFit · JavaScript de la web: formulario de reserva, filtros de la carta,
 * cabecera fija, botón de subir y animaciones.
 *
 * La web funciona sin este archivo: solo mejora la experiencia. Las horas de cada
 * día no se calculan aquí, llegan de PHP en JSON (clase Horario), así el navegador
 * y el servidor siempre usan los mismos horarios.
 */

document.documentElement.classList.add('js');

document.addEventListener('DOMContentLoaded', () => {
    iniciarFormulario();
    iniciarFiltrosCarta();
    iniciarCabecera();
    iniciarBotonSubir();
    iniciarAnimaciones();
});

/* ---------------------------------------------------------------- */
/* Formulario                                                        */
/* ---------------------------------------------------------------- */

function iniciarFormulario() {
    const formulario = document.getElementById('formulario-reserva');
    const datos = document.getElementById('datos-reserva');
    if (!formulario || !datos) return;

    const { disponibilidad, fechasCerradas } = JSON.parse(datos.textContent);
    const campoFecha = formulario.elements.fecha;
    const campoHora = formulario.elements.hora;
    const ayudaFecha = document.getElementById('ayuda-fecha');
    const textoAyudaInicial = ayudaFecha.textContent;

    // Usamos nuestros propios mensajes de error en lugar de los del navegador.
    formulario.noValidate = true;

    // Si el servidor devolvió un mensaje (éxito o error), llevamos el foco a él.
    const mensaje = document.getElementById('mensaje-formulario');
    if (mensaje) mensaje.focus();

    function actualizarHoras() {
        const fecha = campoFecha.value;
        const horaElegida = campoHora.value;
        const horas = disponibilidad[fecha];

        campoHora.replaceChildren();

        if (!fecha) {
            campoHora.append(new Option('Elige primero una fecha', ''));
            campoHora.disabled = true;
            ayudaFecha.textContent = textoAyudaInicial;
            return;
        }

        if (!horas || horas.length === 0) {
            campoHora.append(new Option('Sin horas ese día', ''));
            campoHora.disabled = true;
            ayudaFecha.textContent = explicarDiaSinHoras(fecha, horas, fechasCerradas);
            return;
        }

        campoHora.disabled = false;
        campoHora.append(new Option('Elige', ''));
        for (const hora of horas) {
            // Mantener la hora elegida si sigue disponible en la nueva fecha.
            campoHora.append(new Option(hora, hora, false, hora === horaElegida));
        }
        ayudaFecha.textContent = `${formatearFecha(fecha)}: ${horas.length} horas disponibles.`;
    }

    function explicarDiaSinHoras(fecha, horas, cerradas) {
        if (horas === undefined) return 'Esa fecha está fuera del periodo de reservas.';
        if (cerradas.includes(fecha)) return 'Ese día el restaurante está cerrado. Elige otro día.';
        return 'Ese día ya no quedan horas. Elige otro día.';
    }

    campoFecha.addEventListener('change', () => {
        actualizarHoras();
        limpiarError('fecha');
        limpiarError('hora');
    });
    actualizarHoras();

    // Al corregir un campo, su error desaparece.
    formulario.addEventListener('input', (evento) => {
        if (evento.target.name) limpiarError(evento.target.name);
    });

    formulario.addEventListener('submit', (evento) => {
        const errores = validar(formulario, disponibilidad);
        Object.keys(errores).forEach((campo) => mostrarError(campo, errores[campo]));

        if (Object.keys(errores).length > 0) {
            evento.preventDefault();
            const primero = Object.keys(errores)[0];
            document.getElementById(`campo-${primero}`).focus();
            return;
        }

        // Evitar envíos dobles y dar feedback mientras el servidor responde.
        const boton = formulario.querySelector('[type="submit"]');
        boton.disabled = true;
        boton.textContent = 'Enviando…';
    });
}

/** Mismas reglas que ValidadorReserva.php (el servidor vuelve a comprobarlas todas). */
function validar(formulario, disponibilidad) {
    const valor = (nombre) => formulario.elements[nombre].value.trim();
    const errores = {};

    const nombre = valor('nombre');
    if (nombre === '') errores.nombre = 'Escribe tu nombre y apellidos.';
    else if (nombre.length < 3 || nombre.length > 100) errores.nombre = 'El nombre debe tener entre 3 y 100 caracteres.';
    else if (!/^\p{L}[\p{L} .'-]*$/u.test(nombre)) {
        errores.nombre = 'El nombre solo puede contener letras, espacios, puntos, guiones y apóstrofos.';
    }

    const email = valor('email');
    if (email === '') errores.email = 'Escribe tu correo electrónico.';
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
        errores.email = 'El correo no tiene un formato válido (ejemplo: nombre@dominio.es).';
    }

    const telefono = valor('telefono').replace(/[\s.-]/g, '');
    if (telefono === '') errores.telefono = 'Escribe un teléfono de contacto.';
    else if (!/^\+?\d{9,15}$/.test(telefono)) {
        errores.telefono = 'El teléfono debe tener entre 9 y 15 cifras (puede empezar por +).';
    }

    if (valor('comensales') === '') errores.comensales = 'Indica cuántas personas venís.';

    const fecha = valor('fecha');
    if (fecha === '') errores.fecha = 'Elige una fecha.';
    else if (!(fecha in disponibilidad)) errores.fecha = 'Esa fecha está fuera del periodo de reservas.';
    else if (disponibilidad[fecha].length === 0) errores.fecha = 'Ese día no se puede reservar. Elige otra fecha.';

    const hora = valor('hora');
    if (!errores.fecha) {
        if (hora === '') errores.hora = 'Elige una hora.';
        else if (!disponibilidad[fecha].includes(hora)) errores.hora = 'Esa hora no está disponible el día elegido.';
    }

    if (!formulario.elements.aviso_demo.checked) {
        errores.aviso_demo = 'Marca la casilla para confirmar que sabes que es una demo y que usas datos inventados.';
    }
    return errores;
}

function mostrarError(campo, texto) {
    const parrafo = document.getElementById(`error-${campo}`);
    const control = document.getElementById(`campo-${campo}`);
    if (!parrafo || !control) return;
    parrafo.textContent = texto;
    parrafo.hidden = false;
    control.setAttribute('aria-invalid', 'true');
    const describe = new Set((control.getAttribute('aria-describedby') || '').split(' ').filter(Boolean));
    describe.add(parrafo.id);
    control.setAttribute('aria-describedby', [...describe].join(' '));
}

function limpiarError(campo) {
    const parrafo = document.getElementById(`error-${campo}`);
    const control = document.getElementById(`campo-${campo}`);
    if (!parrafo || !control || parrafo.hidden) return;
    parrafo.hidden = true;
    parrafo.textContent = '';
    control.removeAttribute('aria-invalid');
}

function formatearFecha(textoFecha) {
    const [anio, mes, dia] = textoFecha.split('-').map(Number);
    const texto = new Date(anio, mes - 1, dia).toLocaleDateString('es-ES', {
        weekday: 'long', day: 'numeric', month: 'long',
    });
    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

/* ---------------------------------------------------------------- */
/* Animaciones de entrada (discretas)                                */
/* ---------------------------------------------------------------- */

function iniciarAnimaciones() {
    const reducir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reducir || !('IntersectionObserver' in window)) return;

    const grupos = [
        '.concepto__titulo, .concepto__nota, .concepto__datos li',
        '.carta__lateral, .carta__categoria',
        '.galeria__titulo, .galeria__fotos figure',
        '.horarios__texto, .horarios__tabla',
        '.reserva__talon, .formulario',
        '.visita__tarjeta',
        '.pie__mensaje, .pie__direccion',
    ];

    const observador = new IntersectionObserver((entradas) => {
        for (const entrada of entradas) {
            if (entrada.isIntersecting) {
                entrada.target.classList.add('es-visible');
                observador.unobserve(entrada.target);
            }
        }
    }, { threshold: 0.15 });

    for (const selector of grupos) {
        document.querySelectorAll(selector).forEach((elemento, indice) => {
            // Lo que ya se ve al cargar no se anima (evita parpadeos).
            if (elemento.getBoundingClientRect().top < window.innerHeight) return;
            elemento.classList.add('revelar');
            elemento.style.setProperty('--retraso', `${Math.min(indice, 5) * 80}ms`);
            observador.observe(elemento);
        });
    }
}

/* ---------------------------------------------------------------- */
/* Filtros de la carta                                               */
/* ---------------------------------------------------------------- */

function iniciarFiltrosCarta() {
    const botones = document.querySelectorAll('.filtro');
    const categorias = document.querySelectorAll('.carta__categoria');
    const resultado = document.getElementById('resultado-filtro');
    if (botones.length === 0) return;

    botones.forEach((boton) => boton.addEventListener('click', () => {
        const filtro = boton.dataset.filtro;
        botones.forEach((b) => b.setAttribute('aria-pressed', String(b === boton)));

        let visibles = 0;
        categorias.forEach((categoria, indice) => {
            let platosVisibles = 0;
            categoria.querySelectorAll('.plato').forEach((plato) => {
                const etiquetas = plato.dataset.etiquetas.split(' ');
                const encaja = filtro === 'todo' || etiquetas.includes(filtro);
                plato.hidden = !encaja;
                if (encaja) platosVisibles++;
            });
            visibles += platosVisibles;
            // Con un filtro activo se abren todas las categorías que tengan platos;
            // con "Todo" se vuelve a dejar abierta solo la primera.
            categoria.hidden = platosVisibles === 0;
            categoria.open = filtro === 'todo' ? indice === 0 : platosVisibles > 0;
        });

        resultado.textContent = filtro === 'todo'
            ? ''
            : `${visibles} ${visibles === 1 ? 'plato encaja' : 'platos encajan'} con tu filtro.`;
    }));
}

/* ---------------------------------------------------------------- */
/* Cabecera fija: sombra solo cuando la página se ha desplazado      */
/* ---------------------------------------------------------------- */

function iniciarCabecera() {
    const cabecera = document.querySelector('.cabecera');
    if (!cabecera) return;
    const actualizar = () => cabecera.classList.toggle('esta-flotando', window.scrollY > 40);
    window.addEventListener('scroll', actualizar, { passive: true });
    actualizar();
}

/* ---------------------------------------------------------------- */
/* Botón "volver arriba": aparece al llegar al final de la página    */
/* ---------------------------------------------------------------- */

function iniciarBotonSubir() {
    const boton = document.querySelector('.subir');
    const pie = document.querySelector('.pie');
    if (!boton || !pie || !('IntersectionObserver' in window)) {
        if (boton) boton.classList.add('es-visible');
        return;
    }
    new IntersectionObserver(([entrada]) => {
        boton.classList.toggle('es-visible', entrada.isIntersecting);
    }).observe(pie);
}
