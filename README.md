# 📸 Cáceres en el Foco — Portal del Concurso Fotográfico

[![PHP](https://img.shields.io/badge/PHP-5.3%20%7C%207.x%20%7C%208.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Responsive](https://img.shields.io/badge/Responsive-100%25-brightgreen?style=flat-square)](https://developer.mozilla.org/es/docs/Learn/CSS/CSS_layout/Responsive_Design)
[![SEO](https://img.shields.io/badge/SEO%20%26%20Social-Open%20Graph%20%2B%20JSON--LD-blue?style=flat-square)](https://schema.org)
[![Version](https://img.shields.io/badge/Version-1.2.1-red?style=flat-square)](https://github.com)

> Landing page interactiva y pasarela de redirección (proxy portal) para el concurso fotográfico **«Cáceres en el Foco»**, organizado por **Diario HOY** con motivo del **40º Aniversario de Cáceres como Patrimonio de la Humanidad por la UNESCO**.

---

## 🎯 Propósito del Proyecto

Las campañas del concurso están gestionadas mediante la plataforma **Qualifio**. Debido a las restricciones de personalización e iframe dentro de Qualifio, este proyecto actúa como:
1. **Página de aterrizaje principal (Landing)**: Visual, interactiva, rápida y responsive, con identidad visual idéntica a Diario HOY y al cartel impreso oficial.
2. **Proxy / Puerta de enlace**: Centraliza las URLs canónicas y redirige limpiamente a cada fase del concurso:
   - `/inscripcion/` ➔ Campaña de formulario y registro previo.
   - `/subir/` ➔ Campaña de carga y envío de fotografías.
   - `/galeria/` ➔ Campaña de galería pública y visualización de ganadores.
3. **Control dinámico de fases**: Permite cambiar el estado de la web desde un simple archivo JSON sin tocar código fuente.

---

## 🚀 Características Principales

- **Compatibilidad con PHP 5.3+**: Diseñado para servidores legacy y modernos (sin sintaxis corta de arrays, funciones obsoletas ni dependencias externas).
- **Gestión de Estados por JSON (`config.json`)**:
  - `CERRADO`: Anuncio del concurso, botones en espera.
  - `INSCRIPCIONES_ABIERTAS`: Botón de inscripción destacado en rojo con pulso; las demás fases en espera.
  - `CONCURSO_ABIERTO`: Rally en curso; botón de subida de fotos destacado como acción principal.
  - `GALERIA_Y_GANADORES`: Fallo del jurado publicado y galería visible.
- **Temporizador / Cuenta Atrás configurable**: Reloj dinámico en tiempo real que se puede activar o desactivar (`true`/`false`) y apuntar a cualquier fecha u hora ISO.
- **Identidad Visual Corporativa**:
  - Cabecera y pie nativos de **Diario HOY** (línea 1200px `#202020`).
  - Color corporativo rojo `#ce0000` con microinteracciones y elevaciones sutiles.
  - Intercambio inteligente de fondos: versión horizontal para escritorio (`concurso_fotos_caceres_bgh.jpg`) y vertical para móviles (`concurso_fotos_caceres_bgv.jpg`).
- **Showcase de Premios**:
  - 🥇 **1er Premio**: Samsung Galaxy S25 FE (con silueta PNG transparente en alta definición).
  - 🥈 **2º Premio**: Tarjeta regalo Amazon de 200€.
  - 🥉 **3er Premio**: Bono de desayuno para 2 personas y visita guiada a la bodega en **Atrio** (3 estrellas Michelin).
- **Guía de Participación (Acordeón accesible)**: Basada fielmente en las bases legales oficiales depositadas en el proyecto.
- **Viralidad y Redes**: Botones de un solo clic para WhatsApp, X (Twitter), Facebook y copia de enlace al portapapeles con alerta flotante (*toast*).
- **SEO & Metadatos Completos**:
  - Open Graph optimizado para compartir en redes (1200×627px).
  - Twitter Cards con formato `summary_large_image`.
  - Datos estructurados Schema.org (`Event` / `VisualArtsEvent`) para indexación rica en Google.

---

## 📂 Estructura del Directorio

```text
caceresenelfoco/
├── assets/
│   ├── bases/
│   │   └── Bases legales Rally Fotográfico .docx   # Bases legales oficiales
│   ├── css/
│   │   └── style.css                               # Estilos CSS responsive
│   ├── img/
│   │   ├── concurso_fotos_caceres.jpg              # Banner hero y Open Graph (1200x627)
│   │   ├── concurso_fotos_caceres_bgh.jpg          # Fondo escritorio (1920x1080)
│   │   ├── concurso_fotos_caceres_bgv.jpg          # Fondo móvil (1080x1920)
│   │   ├── patrocinadores.png                      # Logos oficiales transparentes
│   │   └── samsung-galaxy-s25-fe.png               # Render transparente del 1er premio
│   ├── js/
│   │   └── main.js                                 # Cuenta atrás, acordeón y compartir
│   └── pdf/
│       └── PAG_CONCURSO_FOTOS_CACERES.pdf          # Cartel impreso original
├── galeria/
│   └── index.php                                   # Proxy 301 a Qualifio (Galería)
├── inscripcion/
│   └── index.php                                   # Proxy 301 a Qualifio (Inscripción)
├── subir/
│   └── index.php                                   # Proxy 301 a Qualifio (Subida fotos)
├── panel/
│   ├── config.json                                 # Configuración de estados y temporizador
│   ├── config.json.bak                             # Copia de seguridad automática
│   └── index.php                                   # Panel de administración visual (PHP 5.3)
├── index.php                                       # Landing page pública principal
├── robots.txt                                      # Directivas para buscadores (bloquea /panel/)
├── sitemap.xml                                     # Mapa del sitio XML con imágenes para Google
├── AGENTS.md                                       # Guía técnica de arquitectura y estilos
└── README.md                                       # Documentación general
```

---

## ⚙️ Configuración (`panel/config.json`)

Edita el archivo [`panel/config.json`](panel/config.json) (o usa directamente la interfaz en [`panel/`](panel/)) para controlar el comportamiento del portal. Permite alternar entre modo manual y automatizado por calendario:

```json
{
  "control_por_fechas": false,
  "estado": "INSCRIPCIONES_ABIERTAS",
  "mostrar_temporizador": true,
  "fecha_objetivo_temporizador": "2026-11-21T12:00:00+01:00",
  "rotulo_temporizador": "Cuenta atrás para el inicio del rally fotográfico",
  "fases_fechas": [
    { "estado": "CERRADO",                "hasta": "2026-11-16T00:00:00+01:00" },
    { "estado": "INSCRIPCIONES_ABIERTAS", "hasta": "2026-11-21T12:00:00+01:00" },
    { "estado": "CONCURSO_ABIERTO",       "hasta": "2026-11-21T15:00:00+01:00" },
    { "estado": "GALERIA_Y_GANADORES",    "hasta": null }
  ]
}
```

| Parámetro | Valores posibles | Descripción |
| :--- | :--- | :--- |
| `control_por_fechas` | `true`, `false` | `true` para activar el avance automático según fechas; `false` para mandar manualmente con `estado`. |
| `estado` | `CERRADO`, `INSCRIPCIONES_ABIERTAS`, `CONCURSO_ABIERTO`, `GALERIA_Y_GANADORES` | Estado forzado en modo manual. |
| `mostrar_temporizador` | `true`, `false` | Muestra u oculta la caja de cuenta atrás. |
| `fecha_objetivo_temporizador` | String ISO 8601 (ej: `2026-11-21T12:00:00+01:00`) | Fecha y hora límite hacia la que cuenta el reloj. |
| `rotulo_temporizador` | String de texto | Título descriptivo sobre los dígitos de la cuenta atrás. |
| `fases_fechas` | Array de objetos | Cronograma ordenado con las fechas límite (`hasta`) de cada fase. |

---

## 🎛️ Panel de Administración (`panel/`)

El proyecto incluye un gestor administrativo mono-archivo autónomo ubicado en [`panel/`](panel/) (archivo [`panel/index.php`](panel/index.php)) compatible con **PHP 5.3+**:

- **Acceso protegido por contraseña**: Protegido mediante huella criptográfica SHA-256 (`ADMIN_PASSWORD_HASH`) para que la contraseña nunca esté expuesta en texto plano en el servidor ni en Git.
- **Protegido contra rastreadores**: Bloqueado en `robots.txt` y con cabeceras `X-Robots-Tag: noindex, nofollow` para evitar cualquier indexación no deseada.
- **Control integral de `config.json`**:
  - Selector interactivo entre **Modo Automático por Fechas** y **Modo Manual Fijo**.
  - Ajuste de estados (`CERRADO`, `INSCRIPCIONES_ABIERTAS`, `CONCURSO_ABIERTO`, `GALERIA_Y_GANADORES`).
  - Gestión visual de fases: añadir, reordenar (▲ / ▼), eliminar y definir fechas límite y cuenta atrás.
  - Editor directo de **JSON Raw** con validador de sintaxis.
  - Generación de **copia de seguridad automática** (`panel/config.json.bak`) con botón de restauración con 1 clic.
  - Monitor en tiempo real del estado activo en el servidor (`Europe/Madrid`).

---

## 💻 Puesta en Marcha en Local

Solo necesitas tener PHP instalado en tu máquina:

```bash
# 1. Iniciar el servidor local
php -S localhost:8000
```

Abre en tu navegador:
- Portal público: **[http://localhost:8000](http://localhost:8000)**
- Panel de administración: **[http://localhost:8000/panel/](http://localhost:8000/panel/)**

---

## 🌐 Despliegue en Producción

Sube todos los archivos a la carpeta asignada en el servidor (por ejemplo `https://servicios.hoy.es/caceresenelfoco/`).
- La aplicación autodetecta HTTPS, subdirectorios y dominios para generar las etiquetas canónicas y de Open Graph de forma automática.
- No requiere base de datos ni compiladores (CSS/JS nativos y ligeros).

---

## 🏛️ Organización y Colaboradores

- **Organiza**: [Diario HOY](https://www.hoy.es)
- **Colaboran**: 
  - Excmo. Ayuntamiento de Cáceres
  - Consorcio Cáceres Ciudad Histórica
  - Cáceres Capital Europea de la Cultura 2031

---

## 📋 Historial de Versiones

### [v1.2.1] - 2026-11-11
- **Corrección de detección HTTPS en Producción (Proxy / CDN)**: Soporte completo para terminación SSL y proxies inversos (Cloudflare, Akamai, Varnish, Nginx, balanceadores de Vocento) mediante la inspección de cabeceras `HTTP_X_FORWARDED_PROTO`, `HTTP_X_FORWARDED_SSL`, `HTTP_FRONT_END_HTTPS`, `HTTP_X_FORWARDED_PORT` y `SERVER_PORT`.
- **Forzado de HTTPS en Dominio Oficial**: Garantiza el protocolo `https://` en metadatos canónicos (`<link rel="canonical">`), Open Graph (`og:url`, `og:image`, `og:image:secure_url`), Twitter Cards y Schema.org cuando la petición se ejecuta bajo `servicios.hoy.es` o dominios corporativos `*.hoy.es`.
- **Preservación de Entorno Local**: Mantiene protocolo HTTP en entornos locales (`localhost`, `127.0.0.1`) sin romper flujos de desarrollo.

### [v1.2.0] - 2026-11-10
- **Panel de Administración Autónomo (`panel/`)**: Gestor visual mono-archivo (`index.php`) para controlar el 100% de los parámetros de `config.json` en tiempo real (modo manual, transiciones automáticas, temporizador y fases del rally). Compatible estrictamente con PHP 5.3+.
- **Autenticación Criptográfica Robusta**: Acceso protegido mediante hash SHA-256 (`ADMIN_PASSWORD_HASH`) y algoritmo de comparación segura resistente a ataques de temporización (*timing attacks*). La contraseña nunca se almacena ni expone en texto plano en el servidor ni en Git.
- **Aislamiento Arquitectónico del Panel**: Reubicación de la gestión administrativa y archivos JSON en la subcarpeta `panel/`, manteniendo la raíz del proyecto para una URL canónica pública limpia y corta (`https://servicios.hoy.es/caceresenelfoco/`).
- **Copia de Seguridad Automática**: Generación de backup atómico (`panel/config.json.bak`) previo a cada guardado, con mecanismo de restauración en 1 clic.
- **Optimización SEO y Rastreo Completo**: Creación de `robots.txt` (con exclusión de indexación para `/panel/`) y `sitemap.xml` enriquecido con metadatos de Google Images. Inclusión de directivas de geolocalización de Cáceres en la cabecera HTML de `index.php`.
- **Limpieza de Archivos**: Eliminación de ficheros obsoletos (`index_old.php`, etc.).
- **Autoría y Propiedad Intelectual**: Incorporación de bloques formales de autoría (Antonio Horrillo <ahorrillo@hoy.es>) y titularidad de Corporación de Medios de Extremadura, S.A. (Diario HOY / Grupo Vocento).

### [v1.1.0] - 2026-11-10
- **Transición automática por fechas**: Soporte para cambio dinámico de fases mediante calendario en `config.json` (`control_por_fechas` y array `fases_fechas`).
- **Retrocompatibilidad total (PHP 5.3)**: Mantenimiento del modo manual sin dependencias ni sintaxis moderna.
- **Zona Horaria Oficial**: Configuración explícita de `Europe/Madrid` en PHP para evitar desfases en servidores con hora UTC.
- **Temporizador adaptable**: Rótulos y objetivos de cuenta atrás individualizados por fase, y gestión elegante de expiración en `main.js`.
- **Ajustes de Interfaz**: Simplificación de la etiqueta del premio principal a «🏆 1er Premio».
- **Depuración silenciosa**: Comentario HTML al final del documento con la hora y modo de ejecución del servidor.

### [v1.0.1] - 2026-11-06
- **Favicon oficial**: Inclusión de favicon corporativo de Diario HOY (`hoy/favicon.ico`).
- **Alineación de Fondo (Paridad con Qualifio)**: Reubicación de fondos desktop y mobile a `html, body` con `background-position: center center` para evitar saltos visuales al navegar a las campañas de Qualifio.
- **Actualización de documentación**: Especificación técnica de fondos en `AGENTS.md`.

### [v1.0.0] - 2026-11-06
- Lanzamiento inicial del portal hub y pasarela de redirección 301 (`/inscripcion/`, `/subir/`, `/galeria/`).
- Integración de estados dinámicos (`config.json`), cuenta atrás, premios, acordeón de bases y botones sociales.
