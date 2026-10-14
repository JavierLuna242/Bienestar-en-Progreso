import Chart, { chipCambio, color, fecha } from './graficos';
import { agregar, leer } from './historial';

const { resultado: r, etiquetas } = JSON.parse(document.getElementById('datos-resultado').textContent);
const claves = Object.keys(etiquetas);

// Valoración anterior (para comparar) antes de guardar la actual.
const anterior = leer()
    .filter((e) => e.id !== r.id && e.fecha < r.fecha)
    .sort((a, b) => a.fecha.localeCompare(b.fecha))
    .at(-1);

// Guarda la valoración actual (el id evita duplicarla si se recarga la página).
agregar({
    id: r.id,
    fecha: r.fecha,
    global: r.global,
    areas: r.areas,
    perfil: r.perfil.nombre,
    imc: r.imc.valor,
    dias_actividad: r.dias_actividad,
});

if (anterior) {
    const comparacion = document.getElementById('comparacion');
    comparacion.hidden = false;
    comparacion.replaceChildren(chipCambio(r.global - anterior.global), ` frente a tu valoración del ${fecha(anterior.fecha)}`);
    document.getElementById('radar-nota').textContent = 'Tu resultado actual comparado con tu valoración anterior (en gris).';
}

const datasets = [{
    label: 'Actual',
    data: claves.map((k) => r.areas[k]),
    borderColor: color('petroleo'),
    backgroundColor: color('cielo') + '40',
    pointBackgroundColor: color('petroleo'),
    pointBorderColor: '#fff',
    pointBorderWidth: 2,
    pointRadius: 4,
    borderWidth: 2,
}];
if (anterior) {
    datasets.push({
        label: `Anterior (${fecha(anterior.fecha, true)})`,
        data: claves.map((k) => anterior.areas[k]),
        borderColor: '#94a3b8',
        backgroundColor: 'transparent',
        pointBackgroundColor: '#94a3b8',
        pointRadius: 2,
        borderWidth: 1.5,
    });
}

new Chart(document.getElementById('radar'), {
    type: 'radar',
    data: { labels: Object.values(etiquetas), datasets },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: { display: !!anterior, position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 16 } },
            tooltip: { callbacks: { label: (c) => ` ${c.dataset.label}: ${c.raw}` } },
        },
        scales: {
            r: {
                min: 0,
                max: 100,
                ticks: { stepSize: 20, backdropColor: 'transparent', font: { size: 10 }, color: '#94a3b8' },
                grid: { color: '#e2e8f0' },
                angleLines: { color: '#e2e8f0' },
                pointLabels: { font: { size: 12, weight: '500' }, color: '#334155' },
            },
        },
    },
});
