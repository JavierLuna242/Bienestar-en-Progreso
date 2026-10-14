// Cada página carga solo lo que necesita (Chart.js no se descarga en el formulario).
if (document.getElementById('datos-resultado')) import('./resultado');
if (document.getElementById('datos-progreso')) import('./progreso');

const form = document.querySelector('form[data-valoracion]');
if (form) {
    // Validación en el cliente con mensajes en español (el servidor valida de nuevo).
    const mensaje = (campo) => {
        const v = campo.validity;
        if (v.valueMissing) return campo.type === 'radio' ? 'Elige una opción.' : 'Este campo es obligatorio.';
        if (v.badInput) return 'Ingresa un número válido.';
        if (v.rangeUnderflow) return `El valor mínimo es ${campo.min}.`;
        if (v.rangeOverflow) return `El valor máximo es ${campo.max}.`;
        if (v.stepMismatch) return campo.step === '1' ? 'Ingresa un número entero.' : `Usa incrementos de ${campo.step.replace('.', ',')}.`;
        return '';
    };
    form.addEventListener('invalid', (e) => e.target.setCustomValidity(mensaje(e.target)), true);

    // Barra de avance (preguntas obligatorias respondidas) y pasos completos en el índice.
    const avance = form.querySelector('[data-avance]');
    const obligatorias = (raiz) => [...new Set([...raiz.querySelectorAll('[required]')].map((c) => c.name))];
    const respondida = (n) => form.elements[n].value !== '';
    const nombres = obligatorias(form);
    const actualizar = () => {
        const pct = Math.round((nombres.filter(respondida).length / nombres.length) * 100);
        avance.querySelector('[data-barra]').style.width = `${pct}%`;
        avance.querySelector('[data-texto]').textContent = `${pct}%`;
        avance.querySelector('[role=progressbar]').setAttribute('aria-valuenow', pct);
        form.querySelectorAll('[data-seccion]').forEach((s) => {
            form.querySelector(`[data-paso="${s.dataset.seccion}"]`)?.toggleAttribute('data-completo', obligatorias(s).every(respondida));
        });
    };

    form.addEventListener('input', (e) => {
        form.querySelectorAll(`[name="${e.target.name}"]`).forEach((c) => c.setCustomValidity(''));
        actualizar();
    });
    actualizar();
}
