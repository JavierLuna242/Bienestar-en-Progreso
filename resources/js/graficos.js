import Chart from 'chart.js/auto';

// Lee los tokens de color definidos en :root (resources/css/app.css).
export const color = (nombre) => getComputedStyle(document.documentElement).getPropertyValue(`--${nombre}`).trim();

export const fecha = (iso, corto = false) =>
    new Date(iso).toLocaleDateString('es', corto ? { day: 'numeric', month: 'short' } : { day: 'numeric', month: 'short', year: 'numeric' });

// Perfiles por puntaje mínimo: [mínimo, nombre, token de color]
export const PERFILES = [
    [80, 'Rendimiento equilibrado', 'petroleo'],
    [60, 'Activo en desarrollo', 'cielo'],
    [40, 'En construcción', 'naranja'],
    [0, 'Sedentario en riesgo', 'cafe'],
];

/** Insignia de cambio con flecha y signo (nunca solo color). */
export function chipCambio(n) {
    const chip = document.createElement('span');
    chip.className = 'chip ' + (n > 0 ? 'bg-petroleo/10 text-petroleo' : n < 0 ? 'bg-cafe/10 text-cafe' : 'bg-slate-100 text-slate-500');
    chip.textContent = n > 0 ? `▲ +${n}` : n < 0 ? `▼ −${-n}` : '= 0';
    return chip;
}

/** Insignia de perfil: punto de color + nombre en texto. */
export function chipPerfil(nombre) {
    const tono = (PERFILES.find((p) => p[1] === nombre) ?? PERFILES[3])[2];
    const chip = document.createElement('span');
    chip.className = 'chip bg-slate-100 font-medium text-slate-700';
    const punto = document.createElement('span');
    punto.className = 'size-2 rounded-full';
    punto.style.background = `var(--${tono})`;
    chip.append(punto, nombre);
    return chip;
}

Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
Chart.defaults.font.size = 12;
Chart.defaults.color = '#64748b';
Chart.defaults.animation.duration = matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 800;
Object.assign(Chart.defaults.plugins.tooltip, {
    backgroundColor: '#132f3d',
    padding: 12,
    cornerRadius: 10,
    titleFont: { weight: '600' },
    bodySpacing: 4,
    boxPadding: 4,
});

export default Chart;
