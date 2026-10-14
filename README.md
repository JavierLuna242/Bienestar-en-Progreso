<h1 align="center">Bienestar en Progreso</h1>

<p align="center">
  Valoración de salud y ejercicio con puntaje por áreas, perfil de salud, recomendaciones personalizadas y seguimiento del progreso.
</p>

<p align="center">
  <a href="https://bienestar.javier.life"><strong>Ver la aplicación »</strong></a>
</p>

<p align="center">
  <img alt="Laravel 12" src="https://img.shields.io/badge/Laravel-12-2f6a87?logo=laravel&logoColor=white">
  <img alt="PHP 8.2" src="https://img.shields.io/badge/PHP-8.2-2f6a87?logo=php&logoColor=white">
  <img alt="Tailwind CSS 4" src="https://img.shields.io/badge/Tailwind_CSS-4-6eb2cc?logo=tailwindcss&logoColor=white">
  <img alt="Chart.js 4" src="https://img.shields.io/badge/Chart.js-4-ea6c36?logo=chartdotjs&logoColor=white">
  <img alt="Licencia MIT" src="https://img.shields.io/badge/licencia-MIT-9a5131">
</p>

---

## Descripción

**Bienestar en Progreso** es una aplicación web que evalúa el estado de salud y actividad física de una persona a partir de un test breve (unos 5 minutos). Con las respuestas calcula un puntaje de 0 a 100 en seis áreas, un puntaje global ponderado, el índice de masa corporal y un perfil de salud con recomendaciones concretas.

La aplicación **no usa base de datos ni cuentas de usuario**: el resultado del test viaja en la sesión de Laravel y el historial de valoraciones se guarda únicamente en el navegador del usuario (`localStorage`). Está diseñada primero para móvil, ya que la mayoría de las personas hará el test desde el celular.

## Funcionalidades

- **Test por secciones** con 7 bloques: datos básicos, actividad física, cardiovascular, fuerza y movilidad, nutrición, descanso y bienestar mental. Incluye barra de avance e índice de pasos completados.
- **Validación doble**: en el navegador y en el servidor (Form Request), con mensajes en español.
- **Reporte de resultados**:
  - puntaje global con indicador circular;
  - gráfico radar de las 6 áreas, comparado con la valoración anterior si existe;
  - IMC con su categoría y escala visual;
  - perfil de salud, área más fuerte y área de foco;
  - recomendaciones personalizadas y un plan de 3 pasos según el perfil;
  - impresión o exportación a PDF desde el navegador.
- **Panel de progreso**:
  - indicadores de última valoración, promedio, mejor puntaje, cambio total y días de actividad;
  - evolución del puntaje global sobre franjas que marcan cada perfil;
  - ranking de áreas de la última valoración y tendencia de cada área;
  - historial con opción para eliminar una entrada o borrarlo todo.
- **Privacidad por diseño**: ningún dato personal se almacena en el servidor.

## Tecnologías

| Capa | Tecnología |
|------|------------|
| Backend | Laravel 12 · PHP 8.2 |
| Vistas | Blade con componentes reutilizables |
| Estilos | Tailwind CSS 4 con tokens de color en variables CSS |
| Gráficos | Chart.js 4 |
| Compilación de assets | Vite 7 (los assets compilados se incluyen en `public/build`) |
| Pruebas | PHPUnit 11 |
| Persistencia | Sesión de Laravel (archivo) y `localStorage` del navegador |

## Arquitectura

```
Navegador                           Laravel
─────────                           ───────
GET  /valoracion  ───────────────►  Vista del test
POST /valoracion  ───────────────►  ValoracionRequest  →  valida las respuestas
                                    WellnessScoreService  →  calcula puntajes, IMC y perfil
                  ◄─────────────── Redirección con el resultado en la sesión (flash)
GET  /resultado   ───────────────►  Vista del reporte
                                    └─ resultado.js guarda la valoración en localStorage
GET  /progreso    ───────────────►  Vista del panel
                                    └─ progreso.js lee localStorage y dibuja los gráficos
```

Toda la lógica de puntuación está en `App\Services\WellnessScoreService`, separada de los controladores y cubierta por pruebas unitarias.

## Metodología de puntuación

Cada área se puntúa de 0 a 100 como promedio ponderado de sus preguntas. Las respuestas de selección se convierten a una escala lineal, de la peor a la mejor opción.

| Área | Peso global | Componentes |
|------|:-----------:|-------------|
| Cardiovascular | 25 % | Cansancio al subir escaleras (30 %), recuperación (25 %), volumen de ejercicio (25 %), frecuencia cardíaca en reposo (20 %) |
| Fuerza | 15 % | Flexiones (40 %), sentadillas (40 %), volumen de entrenamiento de fuerza (20 %) |
| Movilidad | 15 % | Rango de movimiento (50 %), dolores frecuentes (30 %), volumen de flexibilidad (20 %) |
| Nutrición | 15 % | Frutas y verduras (40 %), agua (30 %), comida procesada (30 %) |
| Descanso | 15 % | Horas de sueño (40 %), calidad del sueño (40 %), días de descanso activo (20 %) |
| Bienestar mental | 15 % | Estrés (40 %), motivación (30 %), constancia (30 %) |

**Ajustes:**

- **Volumen de ejercicio:** se calcula como días × duración, ajustado por el esfuerzo percibido. El tipo de ejercicio orienta a qué área aporta más (por ejemplo, el cardio suma más en Cardiovascular y la fuerza en Fuerza).
- **Frecuencia cardíaca:** es opcional. Si no se conoce, su peso se reparte entre los demás componentes del área. En mujeres se ajusta 3 lpm.
- **Edad:** a partir de los 50 años, el área de Fuerza suma 10 puntos, porque las marcas esperadas bajan con la edad.

**Perfiles de salud** según el puntaje global:

| Puntaje global | Perfil |
|:--------------:|--------|
| 0 – 39 | Sedentario en riesgo |
| 40 – 59 | En construcción |
| 60 – 79 | Activo en desarrollo |
| 80 – 100 | Rendimiento equilibrado |

**IMC:** bajo peso (< 18,5), normal (18,5 – 24,9), sobrepeso (25 – 29,9) y obesidad (≥ 30).

## Requisitos

- PHP 8.2 o superior con las extensiones `mbstring`, `xml`, `ctype`, `fileinfo` y `tokenizer`
- Composer 2
- Node.js 20 o superior, **solo** si vas a modificar estilos o JavaScript

## Instalación

```bash
git clone <url-del-repositorio> bienestar
cd bienestar
composer setup        # instala dependencias, crea .env y genera APP_KEY
php artisan serve     # http://127.0.0.1:8000
```

Los estilos y el JavaScript ya están compilados en `public/build/`, así que la aplicación funciona sin Node.

### Variables de entorno

| Variable | Valor | Descripción |
|----------|-------|-------------|
| `APP_LOCALE` | `es` | Idioma de la interfaz y de las fechas |
| `SESSION_DRIVER` | `file` | Sesión en archivos, sin base de datos |
| `CACHE_STORE` | `file` | Caché en archivos |
| `QUEUE_CONNECTION` | `sync` | Sin colas en segundo plano |

La configuración usa los valores por defecto de Laravel, así que no hay carpeta `config/` y todo se ajusta desde `.env`.

## Desarrollo

Para modificar estilos o JavaScript, recompila los assets:

```bash
npm install
npm run dev      # recarga en caliente mientras desarrollas
npm run build    # compila para producción en public/build
```

Puedes borrar `node_modules` después de compilar.

### Paleta de colores

Los colores se definen como variables CSS en `resources/css/app.css` y Tailwind los expone como utilidades (`bg-petroleo`, `text-cafe`, etc.):

| Token | Color | Uso |
|-------|-------|-----|
| `--petroleo` | `#2f6a87` | Color principal, encabezados, gráficos |
| `--cielo` | `#6eb2cc` | Acentos secundarios, estados informativos |
| `--gris` | `#e7e7e7` | Separadores |
| `--naranja` | `#ea6c36` | Llamadas a la acción y resaltados |
| `--cafe` | `#9a5131` | Advertencias y perfiles de riesgo |

## Pruebas

```bash
php artisan test
```

La suite incluye:

- **pruebas unitarias del servicio de puntuación:** puntajes extremos, ponderación global, categorías de IMC, umbrales de perfil, frecuencia cardíaca opcional, ajustes por sexo y edad, y recomendaciones;
- **pruebas de funcionalidad del flujo completo:** páginas, validación en español y generación del reporte.

## Estructura del proyecto

```
app/
├── Http/
│   ├── Controllers/ValoracionController.php   Recibe el test y entrega el reporte
│   └── Requests/ValoracionRequest.php         Reglas y mensajes de validación
└── Services/WellnessScoreService.php          Lógica de puntuación, IMC, perfiles y recomendaciones
bootstrap/app.php                              Arranque de la aplicación y proxies de confianza
public/build/                                  Assets compilados por Vite
resources/
├── css/app.css                                Tokens de color, componentes y estilos de impresión
├── js/
│   ├── app.js                                 Validación del formulario y carga por página
│   ├── historial.js                           Lectura y escritura en localStorage
│   ├── graficos.js                            Configuración común de Chart.js
│   ├── resultado.js                           Radar y guardado de la valoración
│   └── progreso.js                            Panel de progreso
└── views/
    ├── components/                            layout, navbar, encabezado, boton, icono,
    │                                          tarjeta-estadistica, barra-progreso
    ├── inicio.blade.php
    ├── valoracion.blade.php
    ├── resultado.blade.php
    └── progreso.blade.php
routes/web.php                                 Rutas: /, /valoracion, /resultado, /progreso
tests/
├── Unit/WellnessScoreServiceTest.php
└── Feature/ValoracionTest.php
```

## Despliegue

La aplicación en producción corre con **nginx + PHP-FPM** en un servidor Debian y se publica mediante un **túnel de Cloudflare** (Cloudflare Tunnel), sin abrir puertos al exterior.

1. Copia el proyecto al servidor (sin `vendor/` ni `.env`).
2. Instala las dependencias y prepara el entorno:
   ```bash
   composer install --no-dev --optimize-autoloader
   cp .env.example .env    # APP_ENV=production, APP_DEBUG=false, APP_URL=https://tu-dominio
   php artisan key:generate --force
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo -u www-data php artisan optimize
   ```
3. Configura un sitio de nginx que escuche solo en localhost:
   ```nginx
   server {
     listen 127.0.0.1:8084;
     root /ruta/al/proyecto/public;
     index index.php;
     location / { try_files $uri $uri/ /index.php?$query_string; }
     location /build/ { expires 1y; add_header Cache-Control "public, immutable"; }
     location ~ \.php$ { include snippets/fastcgi-php.conf; fastcgi_pass unix:/run/php/php-fpm.sock; }
     location ~ /\.(?!well-known) { deny all; }
   }
   ```
4. En Cloudflare Zero Trust, publica el hostname apuntando a `http://localhost:8084`.

La aplicación confía en las cabeceras del proxy (`trustProxies` en `bootstrap/app.php`) para generar URLs `https` detrás del túnel.

Para actualizar una instalación existente, copia los archivos modificados y ejecuta `sudo -u www-data php artisan optimize`.

## Aviso

Esta herramienta es **orientativa** y no reemplaza la valoración de un profesional de la salud. Ante cualquier condición médica, consulta con un especialista antes de iniciar un programa de ejercicio.

## Licencia

Distribuido bajo la licencia MIT.
