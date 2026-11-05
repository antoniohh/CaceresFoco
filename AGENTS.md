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
         ├─ config.json (Estado)         ├─ /inscripcion/ ➔ Qualifio Form
         ├─ assets/css/style.css         ├─ /subir/       ➔ Qualifio Upload
         └─ assets/js/main.js            └─ /galeria/     ➔ Qualifio Gallery
```

### Principio de Diseño
Las campañas en Qualifio se ejecutan dentro de su propio motor en `https://entretenimiento.hoy.es/quiz/...`. Dado que Qualifio restringe la inyección profunda de scripts y personalizaciones estructurales complejas:
1. Este repositorio actúa como la **fachada pública** en `https://servicios.hoy.es/rally/`.
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
- ✔️ **Cálculo dinámico de URLs base**: Respetar la lógica de detección de protocolo, host y prefijo de ruta para evitar rutas rotas en subcarpetas o CLI.

---

## 3. Máquina de Estados (`config.json`)

El flujo del concurso se controla desde [`config.json`](file:///home/antoniohh/Proyectos/qualifio_concurso_fotografico/rally/config.json). La estructura esperada es:

```json
{
  "estado": "INSCRIPCIONES_ABIERTAS",
  "mostrar_temporizador": true,
  "fecha_objetivo_temporizador": "2026-11-21T12:00:00+01:00"
}
```

### Matriz de Estados

| Estado | Badge Hero | Botón 1 (Inscripción) | Botón 2 (Subir Fotos) | Botón 3 (Galería) |
| :--- | :--- | :--- | :--- | :--- |
| `CERRADO` | 🟡 Próximamente (`badge-closed`) | Inactivo (`tag-soon`) | Inactivo (`tag-closed`) | Inactivo (`tag-closed`) |
| `INSCRIPCIONES_ABIERTAS` | 🟢 Inscripciones Abiertas (`badge-open`) | **Activo Primario** (`tag-open`) | Inactivo (`tag-closed`) | Inactivo (`tag-soon`) |
| `CONCURSO_ABIERTO` | 🔴 Rally en Marcha (`badge-live`) | Inactivo (`tag-done`) | **Activo Primario** (`tag-live`) | Inactivo (`tag-soon`) |
| `GALERIA_Y_GANADORES` | 🏆 Ganadores y Galería (`badge-results`) | Inactivo (`tag-done`) | Inactivo (`tag-closed`) | **Activo Primario** (`tag-open`) |

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
   php -l index.php
   ```
2. **Caché en Assets**: Al enlazar CSS o JS en `index.php`, mantén el parámetro de versión `?v=<?php echo time(); ?>` o control de versión semántica para forzar la actualización inmediata en navegadores de usuario.
3. **Rutas Relativas**: Los assets y enlaces internos deben mantener rutas relativas (`assets/...`, `inscripcion/`, `subir/`, `galeria/`) para funcionar independientemente del subdirectorio donde se aloje el proyecto.
