// Historial de valoraciones en localStorage (sin base de datos).
const CLAVE = 'bienestar_historial';

export function leer() {
    try {
        const historial = JSON.parse(localStorage.getItem(CLAVE));
        return Array.isArray(historial) ? historial : [];
    } catch {
        return [];
    }
}

function guardar(historial) {
    try {
        localStorage.setItem(CLAVE, JSON.stringify(historial));
    } catch {
        // Modo privado o almacenamiento lleno: la app sigue funcionando sin historial.
    }
}

export function agregar(entrada) {
    const historial = leer();
    if (!historial.some((e) => e.id === entrada.id)) guardar([...historial, entrada]);
}

export function eliminar(id) {
    guardar(leer().filter((e) => e.id !== id));
}

export function borrarTodo() {
    try {
        localStorage.removeItem(CLAVE);
    } catch {}
}
