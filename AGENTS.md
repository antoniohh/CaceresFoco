# 🤖 AGENTS.md — Arquitectura, Sistema de Diseño y Guía de Desarrollo

Este documento define la arquitectura técnica, las restricciones de infraestructura y las directrices de diseño para desarrolladores y agentes de IA que trabajen en este repositorio.

---

## 1. Arquitectura del Sistema

El proyecto opera bajo el patrón **Landing Hub & Proxy Portal**:

```
                       [ Usuario Final ]
                              │
               ┌──────────────┴──────────────┐
               ▼                             ▼
        [ index.php ]               [ Proxy Folders ]
   (Landing interactiva)           (Redirecciones 301)
         │                               │
         ├─ panel/config.json            ├─ /inscripcion/ ➔ Qualifio Form
         ├─ assets/css/style.css         ├─ /subir/       ➔ Qualifio Upload
         └─ assets/js/main.js            └─ /galeria/     ➔ Qualifio Gallery
         │
    [ Gestor Panel ]
         └─ /panel/ (index.php)
```

### Principio de Diseño
Las campañas en Qualifio se ejecutan dentro de su propio motor en `https://entretenimiento.hoy.es/quiz/...`. Dado que Qualifio restringe la inyección profunda de scripts y personalizaciones estructurales complejas:
1. Este repositorio actúa como la **fachada pública** en `https://servicios.hoy.es/caceresenelfoco/`.
2. Las subcarpetas [`inscripcion/index.php`](file:///home/antoniohh/Proyectos/qualifio_concurso_fotografico/rally/inscripcion/index.php), [`subir/index.php`](file:///home/antoniohh/Proyectos/qualifio_concurso_fotografico/rally/subir/index.php) y [`galeria/index.php`](file:///home/antoniohh/Proyectos/qualifio_concurso_fotografico/rally/galeria/index.php) ejecutan redirecciones HTTP 301 limpias a los quizzes correspondientes.
3. El frontend de la landing replica con exactitud los componentes globales inyectados en Qualifio (header, footer, fondo slate y tarjeta centrada) para que el salto entre páginas sea visualmente continuo.

---

## 2. Restricciones Críticas de Servidor (PHP 5.3)

> [!CAUTION]
> El servidor en producción corre bajo **PHP 5.3**. Cualquier modificación en código PHP debe respetar estrictamente esta versión.

### Reglas obligatorias para PHP:
- ❌ **NO usar sintaxis corta de arrays** `['a' => 1]`. Usar siempre `array('a' => 1)`.
- ❌ **NO usar operador null coalescing** `??`. Usar `isset($var) ? $var : $default`.
- ❌ **NO usar namespaces o traits**.
- ❌ **NO usar funciones anónimas complejas o funciones añadidas en PHP 5.4+** (como `http_response_code()`, generadores, etc.).
- ✔️ **Usar `json_decode()` nativo** (soportado desde PHP 5.2).
- ✔️ **Sanitización de entradas crudas en el Panel**: Al recibir texto JSON en bruto (`save_raw`), neutralizar `magic_quotes_gpc` con `stripslashes()` si estuviera activo en servidores heredados, depurar caracteres invisibles (BOM `\xEF\xBB\xBF` y espacios no separables `\xC2\xA0`) y reportar diagnósticos con `json_last_error()` / `json_last_error_msg()` con fallbacks seguros.
- ✔️ **Cálculo dinámico de URLs base y detección de HTTPS**: Respetar la lógica de detección de protocolo compatible con proxies inversos y balanceadores (`HTTP_X_FORWARDED_PROTO`, `HTTP_X_FORWARDED_SSL`, `SERVER_PORT: 443`, etc.), forzando HTTPS en dominios de producción (`servicios.hoy.es` o `*.hoy.es`) para evitar URLs no seguras en canonicals o metadatos de redes.

---

## 3. Máquina de Estados (`panel/config.json`)

El flujo del concurso se controla desde [`panel/config.json`](panel/config.json). Admite modo manual y modo automatizado por calendario:

```json
{
  "control_por_fechas": false,
  "estado": "CERRADO",
  "mostrar_temporizador": true,
  "fecha_objetivo_temporizador": "2026-11-21T12:00:00+01:00",
  "rotulo_temporizador": "Cuenta atrás para el inicio del rally fotográfico",
  "fases_fechas": [
    {
      "estado": "CERRADO",
      "desde": null,
      "hasta": "2026-11-16T00:00:00+01:00",
      "mostrar_temporizador": true,
      "fecha_objetivo_temporizador": "2026-11-16T00:00:00+01:00",
      "rotulo_temporizador": "Cuenta atrás para la apertura de inscripciones"
    },
    {
      "estado": "INSCRIPCIONES_ABIERTAS",
      "desde": "2026-11-16T00:00:00+01:00",
      "hasta": "2026-11-21T12:00:00+01:00",
      "mostrar_temporizador": true,
      "fecha_objetivo_temporizador": "2026-11-21T12:00:00+01:00",
      "rotulo_temporizador": "Cuenta atrás para el inicio del rally fotográfico"
    },
    {
      "estado": "CONCURSO_ABIERTO",
      "desde": "2026-11-21T12:00:00+01:00",
      "hasta": "2026-11-21T15:00:00+01:00",
      "mostrar_temporizador": true,
      "fecha_objetivo_temporizador": "2026-11-21T15:00:00+01:00",
      "rotulo_temporizador": "Tiempo restante para subir tus fotografías (hasta las 15:00h)"
    },
    {
      "estado": "GALERIA_ABIERTA",
      "desde": "2026-11-21T15:00:00+01:00",
      "hasta": "2026-12-01T10:00:00+01:00",
      "mostrar_temporizador": true,
      "fecha_objetivo_temporizador": "2026-12-01T10:00:00+01:00",
      "rotulo_temporizador": "Cuenta atrás para el anuncio oficial de ganadores (1 de diciembre)"
    },
    {
      "estado": "GANADORES_PUBLICADOS",
      "desde": "2026-12-01T10:00:00+01:00",
      "hasta": null,
      "mostrar_temporizador": false,
      "fecha_objetivo_temporizador": null,
      "rotulo_temporizador": ""
    }
  ]
}
```

- **`control_por_fechas: false`**: Modo manual (retrocompatible al 100%). El portal obedece a `"estado"`.
- **`control_por_fechas: true`**: Modo automático. Las fases y la cuenta atrás transicionan solas según la hora oficial de Extremadura (`Europe/Madrid`).

### Matriz de Estados (Fases 0 a 4)

| Fase | Estado | Badge Hero | Botón 1 (Inscripción) | Botón 2 (Subir Fotos) | Botón 3 (Galería) |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Fase 0: Previo** | `CERRADO` | 🟡 Próximamente (`badge-closed`) | Inactivo (`tag-soon`) | Inactivo (`tag-closed`) | Inactivo (`tag-soon`) |
| **Fase 1: Registro** | `INSCRIPCIONES_ABIERTAS` | 🟢 Inscripciones Abiertas (`badge-open`) | **Activo Primario** (`tag-open`) | Inactivo (`tag-closed`) | Inactivo (`tag-soon`) |
| **Fase 2: Concurso** | `CONCURSO_ABIERTO` | 🔴 Rally en Marcha (`badge-live`) | Inactivo (`tag-done`) | **Activo Primario** (`tag-live`) | Inactivo (`tag-soon`) |
| **Fase 3: Galería** | `GALERIA_ABIERTA` | 🖼️ Galería Abierta (`badge-gallery`) | Inactivo (`tag-done`) | Inactivo (`tag-closed`) | **Activo Primario** (`tag-open`) |
| **Fase 4: Ganadores** | `GANADORES_PUBLICADOS`<br>*(alias `GALERIA_Y_GANADORES`)* | 🏆 Ganadores Oficiales (`badge-results`) | Inactivo (`tag-done`) | Inactivo (`tag-closed`) | **Activo Primario** (`tag-open`) |

> [!NOTE]
> En Qualifio, la campaña de galería (`/galeria/`) es continua desde el cierre de subida el 21 de noviembre; el 1 de diciembre simplemente se actualiza en Qualifio para destacar las fotos premiadas. La separación entre **Fase 3** (`GALERIA_ABIERTA`) y **Fase 4** (`GANADORES_PUBLICADOS`) en este portal hub permite mostrar la cuenta atrás hacia el fallo del jurado y ajustar los titulares editoriales de la landing.

#### Comportamiento de Botones Inactivos
Cuando un botón no está activo según el estado, se renderiza con la clase `.card-disabled`, cursor `not-allowed`, enlace no interactivo y una etiqueta textual descriptiva (*Próximamente*, *Cerrado* o *Finalizado*).

---

## 4. Sistema de Diseño (Design Tokens)

### Paleta de Colores
```css
/* Primarios y Marca */
--color-brand-red:        #ce0000;   /* Rojo distintivo Diario HOY */
--color-brand-red-dark:   #b80000;   /* Rojo hover / gradientes */
--color-brand-red-deep:   #990000;   /* Borde activo */

/* Fondos y Neutros */
--color-bg-canvas:        #c9d5d3;   /* Fondo salvia/grisáceo original del cartel */
--color-card-bg:          #ffffff;   /* Superficie de la tarjeta */
--color-text-main:        #0f172a;   /* Texto principal (slate-900) */
--color-text-body:        #475569;   /* Párrafos y descripciones (slate-600) */
--color-text-muted:       #94a3b8;   /* Metadatos y bordes (slate-400) */
--color-border-light:     #e2e8f0;   /* Divisores suaves */

/* Fondo Footer Hoy */
--color-footer-line:      #202020;   /* Línea de 1200px del footer oficial */

/* Estados de Pulso */
--color-state-open:       #22c55e;   /* Verde esmeralda */
--color-state-live:       #ef4444;   /* Rojo alerta */
--color-state-closed:     #f59e0b;   /* Ámbar aviso */
```

### Tipografía
- **Fuente de Interfaz**: `-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif`
- **Claim Editorial**: `Georgia, "Times New Roman", serif` (en cursiva, para reflejar el cartel impreso).

### Sombras y Elevación
- **Tarjeta Principal**: `0 1px 3px rgba(0,0,0,0.08), 0 8px 24px -4px rgba(15,23,42,0.16), 0 24px 48px -8px rgba(15,23,42,0.22)`
- **Botón Primario**: `0 6px 16px rgba(206, 0, 0, 0.3)` con micro-elevación al hover `translateY(-2px)`.

### Responsividad y Puntos de Ruptura (*Breakpoints*)
- **Desktop (> 768px)**:
  - Fondo: Aplicado en `html, body`, `concurso_fotos_caceres_bgh.jpg` (1920×1080), `background-position: center center`, `background-attachment: fixed`.
  - Ancho de tarjeta: `max-width: 620px`.
  - Grid de datos: 2 columnas.
- **Mobile (<= 767px)**:
  - Fondo: Aplicado en `html, body`, `concurso_fotos_caceres_bgv.jpg` (1080×1920), `background-position: center center`, `background-attachment: scroll`.
  - Ancho de tarjeta: `calc(100% - 24px)`, paddings reducidos.
  - Grid de datos: 1 columna.

---

## 5. Componentes Clave

1. **Header Oficial**: SVG corporativo de Diario HOY con fallback en PNG a 24px de altura y borde inferior de 1px `#e9e9e9`.
2. **Hero Badge con `.pulse-dot`**: Punto de 8px con animación `@keyframes` adaptada al color del estado actual.
3. **Sección Editorial**: Texto literal extraído del cartel oficial ([`PAG_CONCURSO_FOTOS_CACERES.pdf`](file:///home/antoniohh/Proyectos/qualifio_concurso_fotografico/rally/assets/pdf/PAG_CONCURSO_FOTOS_CACERES.pdf)).
4. **Reloj de Cuenta Atrás**: Implementado en [`assets/js/main.js`](file:///home/antoniohh/Proyectos/qualifio_concurso_fotografico/rally/assets/js/main.js), lee el atributo `data-target-date` inyectado dinámicamente desde [`config.json`](file:///home/antoniohh/Proyectos/qualifio_concurso_fotografico/rally/config.json).
5. **Showcase del Gran Premio**: Incluye recorte en alta resolución con fondo transparente de [`samsung-galaxy-s25-fe.png`](file:///home/antoniohh/Proyectos/qualifio_concurso_fotografico/rally/assets/img/samsung-galaxy-s25-fe.png) y desglose de 2º y 3er premio según las bases.
6. **Bases y Preguntas (Acordeón)**: Componente accesible mediante vanilla JS que permite abrir/cerrar secciones sin dependencias de frameworks.
7. **Patrocinadores**: Logotipos en alta definición con fondo transparente [`patrocinadores.png`](file:///home/antoniohh/Proyectos/qualifio_concurso_fotografico/rally/assets/img/patrocinadores.png), eliminando recuadros o fondos rígidos.

---

## 6. Guía de Modificación para Futuros Agentes

1. **Nunca romper compatibilidad con PHP 5.3**: Si agregas nuevas variables o lógica de servidor, verifica la sintaxis con:
   ```bash
   php -l index.php && php -l panel/index.php
   ```
2. **Caché en Assets**: Al enlazar CSS o JS en `index.php`, mantén el parámetro de versión `?v=<?php echo time(); ?>` o control de versión semántica para forzar la actualización inmediata en navegadores de usuario.
3. **Rutas Relativas**: Los assets y enlaces internos deben mantener rutas relativas (`assets/...`, `inscripcion/`, `subir/`, `galeria/`) para funcionar independientemente del subdirectorio donde se aloje el proyecto.
