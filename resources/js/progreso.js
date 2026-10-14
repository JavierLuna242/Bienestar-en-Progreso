import Chart, { chipCambio, chipPerfil, color, fecha, PERFILES } from './graficos';
import { borrarTodo, eliminar, leer } from './historial';

const { etiquetas } = JSON.parse(document.getElementById('datos-progreso').textContent);
const claves = Object.keys(etiquetas);
const perfilDe = (puntaje) => PERFILES.find(([min]) => puntaje >= min);
const ICONO_PAPELERA = '<svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
let grafico;

// Franjas de fondo con el perfil que corresponde a cada rango de puntaje.
const franjas = {
    id: 'franjas',
    beforeDatasetsDraw({ ctx, chartArea: a, scales: { y } }) {
        ctx.save();
        ctx.font = `500 11px ${Chart.defaults.font.family}`;
        ctx.textAlign = 'right';
        ctx.textBaseline = 'top';
        PERFILES.forEach(([min, nombre, tono], i) => {
            const arriba = y.getPixelForValue(i === 0 ? 100 : PERFILES[i - 1][0]);
            const abajo = y.getPixelForValue(min);
            ctx.fillStyle = color(tono) + '17';
            ctx.fillRect(a.left, arriba, a.right - a.left, abajo - arriba);
            ctx.fillStyle = '#64748b';
            ctx.fillText(nombre, a.right - 8, arriba + 6);
        });
        ctx.restore();
    },
    // Línea vertical de referencia al pasar el cursor.
    afterDatasetsDraw({ ctx, chartArea: a, tooltip }) {
        const activo = tooltip?.getActiveElements()[0];
        if (!activo) return;
        ctx.save();
        ctx.strokeStyle = '#94a3b8';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(activo.element.x, a.top);
        ctx.lineTo(activo.element.x, a.bottom);
        ctx.stroke();
        ctx.restore();
    },
};

function sparkline(valores) {
    const [w, h, m] = [160, 48, 5];
    const x = (i) => (valores.length === 1 ? w / 2 : m + (i * (w - 2 * m)) / (valores.length - 1));
    const y = (v) => h - m - ((Number(v) || 0) / 100) * (h - 2 * m);
    const puntos = valores.map((v, i) => `${x(i).toFixed(1)},${y(v).toFixed(1)}`).join(' ');
    return `<svg viewBox="0 0 ${w} ${h}" class="h-auto w-full overflow-visible" aria-hidden="true">
        <line x1="0" x2="${w}" y1="${y(70)}" y2="${y(70)}" stroke="#e2e8f0" stroke-width="1"/>
        <polyline points="${puntos}" fill="none" stroke="${color('petroleo')}" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
        <circle cx="${x(valores.length - 1)}" cy="${y(valores.at(-1))}" r="4" fill="${color('petroleo')}" stroke="#fff" stroke-width="2"/>
    </svg>`;
}

function stat(nombre, valor, detalle) {
    const tarjeta = document.querySelector(`[data-stat="${nombre}"]`);
    tarjeta.querySelector('[data-valor]').textContent = valor;
    tarjeta.querySelector('[data-detalle]').textContent = detalle;
    return tarjeta;
}

function celda(contenido, clase = '') {
    const td = document.createElement('td');
    td.className = `px-3 py-4 ${clase}`;
    td.append(...[contenido].flat());
    return td;
}

function render() {
    const h = leer().sort((a, b) => a.fecha.localeCompare(b.fecha));
    document.getElementById('vacio').hidden = h.length > 0;
    document.getElementById('con-datos').hidden = h.length === 0;
    grafico?.destroy();
    if (!h.length) return;

    const puntajes = h.map((e) => e.global);
    const primera = h[0];
    const ultima = h.at(-1);
    const mejor = Math.max(...puntajes);

    // Indicadores
    stat('ultima', ultima.global, fecha(ultima.fecha)).querySelector('[data-perfil]').replaceChildren(chipPerfil(ultima.perfil));
    stat('promedio', Math.round(puntajes.reduce((a, b) => a + b) / h.length), `de ${h.length} ${h.length === 1 ? 'valoración' : 'valoraciones'}`);
    stat('mejor', mejor, fecha(h.findLast((e) => e.global === mejor).fecha));
    const cambio = ultima.global - primera.global;
    const tarjetaCambio = stat('cambio', cambio > 0 ? `+${cambio}` : cambio < 0 ? `−${-cambio}` : '0', h.length > 1 ? `desde el ${fecha(primera.fecha)}` : 'necesitas 2 valoraciones');
    tarjetaCambio.querySelector('[data-valor]').classList.toggle('text-cafe', cambio < 0);
    const dias = Number(ultima.dias_actividad) || 0;
    stat('dias', `${dias}/7`, 'días de ejercicio por semana').querySelector('[data-dias]').replaceChildren(
        ...Array.from({ length: 7 }, (_, i) => Object.assign(document.createElement('span'), { className: `h-1.5 flex-1 rounded-full ${i < dias ? 'bg-petroleo' : 'bg-slate-200'}` })),
    );

    // Evolución del puntaje global (una sola serie sobre las franjas de perfil)
    const etiquetasX = h.map((e, i) => {
        const dia = fecha(e.fecha, true);
        const repetido = h.some((o, j) => j !== i && fecha(o.fecha, true) === dia);
        return repetido ? `${dia} ${new Date(e.fecha).toLocaleTimeString('es', { hour: '2-digit', minute: '2-digit' })}` : dia;
    });
    document.getElementById('nota-evolucion').textContent = h.length > 1
        ? 'Las franjas de fondo indican el perfil que corresponde a cada puntaje.'
        : 'Haz otra valoración para ver tu tendencia. Las franjas indican el perfil de cada rango.';
    grafico = new Chart(document.getElementById('evolucion'), {
        type: 'line',
        data: {
            labels: etiquetasX,
            datasets: [{
                label: 'Puntaje global',
                data: puntajes,
                borderColor: color('petroleo'),
                borderWidth: 2,
                pointBackgroundColor: color('petroleo'),
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointHitRadius: 16,
                tension: 0.3,
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: { padding: { top: 8, right: 8 } },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    displayColors: false,
                    callbacks: {
                        title: (items) => new Date(h[items[0].dataIndex].fecha).toLocaleString('es', { dateStyle: 'long', timeStyle: 'short' }),
                        label: (c) => `Puntaje: ${c.raw} · ${perfilDe(c.raw)[1]}`,
                        afterLabel: (c) => (c.dataIndex > 0 ? `Cambio: ${c.raw - puntajes[c.dataIndex - 1] >= 0 ? '+' : ''}${c.raw - puntajes[c.dataIndex - 1]} vs. anterior` : 'Primera valoración'),
                    },
                },
            },
            scales: {
                y: { min: 0, max: 100, ticks: { stepSize: 20 }, grid: { color: '#eef2f5' }, border: { display: false } },
                x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkipPadding: 16 } },
            },
        },
        plugins: [franjas],
    });

    // Áreas en la última valoración, ordenadas de mayor a menor; la más baja se resalta como foco.
    const orden = [...claves].sort((a, b) => ultima.areas[b] - ultima.areas[a]);
    orden.forEach((clave, i) => {
        const fila = document.querySelector(`#ranking [data-area="${clave}"]`);
        const valor = Number(ultima.areas[clave]) || 0;
        const esFoco = i === orden.length - 1;
        fila.style.order = i;
        fila.querySelector('[data-texto]').textContent = valor;
        fila.querySelector('[data-foco]').hidden = !esFoco;
        const barra = fila.querySelector('[data-barra]');
        barra.style.width = `${valor}%`;
        barra.classList.toggle('bg-naranja', esFoco);
        barra.classList.toggle('bg-petroleo', !esFoco);
    });

    // Múltiplos pequeños por área
    claves.forEach((clave) => {
        const tarjeta = document.querySelector(`[data-mini="${clave}"]`);
        const serie = h.map((e) => e.areas[clave]);
        tarjeta.querySelector('[data-valor]').textContent = serie.at(-1);
        tarjeta.querySelector('[data-delta]').replaceChildren(
            ...(h.length > 1 ? [chipCambio(serie.at(-1) - serie[0]), ' vs. primera'] : ['Primera valoración']),
        );
        tarjeta.querySelector('[data-delta]').className = 'mt-1 whitespace-nowrap text-xs text-slate-500';
        tarjeta.querySelector('[data-spark]').innerHTML = sparkline(serie);
    });

    // Tabla (más reciente primero), con el cambio respecto a la valoración anterior
    document.getElementById('conteo').textContent = `${h.length} ${h.length === 1 ? 'valoración guardada' : 'valoraciones guardadas'}`;
    document.getElementById('filas').replaceChildren(...h.map((e, i) => {
        const fila = document.createElement('tr');
        fila.className = 'hover:bg-fondo/60';

        const cuando = document.createElement('div');
        cuando.className = 'font-medium text-slate-900';
        cuando.textContent = fecha(e.fecha);
        const hora = document.createElement('div');
        hora.className = 'text-xs text-slate-500';
        hora.textContent = new Date(e.fecha).toLocaleTimeString('es', { hour: '2-digit', minute: '2-digit' });

        const puntaje = document.createElement('span');
        puntaje.className = 'text-base font-semibold tabular-nums text-slate-900';
        puntaje.textContent = e.global;

        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'rounded-lg p-2 text-slate-400 hover:bg-cafe/10 hover:text-cafe';
        boton.innerHTML = ICONO_PAPELERA;
        boton.setAttribute('aria-label', `Eliminar la valoración del ${fecha(e.fecha)}`);
        boton.title = 'Eliminar';
        boton.addEventListener('click', () => {
            if (confirm(`¿Eliminar la valoración del ${fecha(e.fecha)}?`)) {
                eliminar(e.id);
                render();
            }
        });

        fila.append(
            celda([cuando, hora], 'whitespace-nowrap pl-5 sm:pl-6'),
            celda(puntaje),
            celda(chipPerfil(e.perfil), 'whitespace-nowrap'),
            celda(i > 0 ? chipCambio(e.global - h[i - 1].global) : Object.assign(document.createElement('span'), { className: 'text-xs text-slate-400', textContent: 'Inicial' })),
            celda(e.imc != null ? Number(e.imc).toFixed(1).replace('.', ',') : '—', 'hidden tabular-nums text-slate-600 sm:table-cell'),
            celda(boton, 'pr-5 text-right sm:pr-6'),
        );
        return fila;
    }).reverse());
}

document.getElementById('borrar-todo').addEventListener('click', () => {
    if (confirm('¿Borrar todo tu historial? Esta acción no se puede deshacer.')) {
        borrarTodo();
        render();
    }
});

render();
