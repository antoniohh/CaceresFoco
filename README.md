# 📸 Cáceres en el Foco — Portal del Concurso Fotográfico

[![PHP](https://img.shields.io/badge/PHP-5.3%20%7C%207.x%20%7C%208.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Responsive](https://img.shields.io/badge/Responsive-100%25-brightgreen?style=flat-square)](https://developer.mozilla.org/es/docs/Learn/CSS/CSS_layout/Responsive_Design)
[![SEO](https://img.shields.io/badge/SEO%20%26%20Social-Open%20Graph%20%2B%20JSON--LD-blue?style=flat-square)](https://schema.org)
[![Version](https://img.shields.io/badge/Version-1.0.0-red?style=flat-square)](https://github.com)

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
rally/
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
├── config.json                                     # Configuración de estados y temporizador
├── index.php                                       # Landing page principal
├── index_old.php                                   # Respaldo de la versión original
├── AGENTS.md                                       # Guía técnica de arquitectura y estilos
└── README.md                                       # Documentación general
```

---

## ⚙️ Configuración (`config.json`)

Edita el archivo [`config.json`](config.json) para controlar el comportamiento del portal en cualquier momento:

```json
{
  "estado": "INSCRIPCIONES_ABIERTAS",
  "mostrar_temporizador": true,
  "fecha_objetivo_temporizador": "2026-11-21T12:00:00+01:00"
}
```

| Parámetro | Valores posibles | Descripción |
| :--- | :--- | :--- |
| `estado` | `CERRADO`, `INSCRIPCIONES_ABIERTAS`, `CONCURSO_ABIERTO`, `GALERIA_Y_GANADORES` | Define la fase actual del certamen y la interacción de los botones. |
| `mostrar_temporizador` | `true`, `false` | Muestra u oculta la caja de cuenta atrás. |
| `fecha_objetivo_temporizador` | String ISO 8601 (ej: `2026-11-21T12:00:00+01:00`) | Fecha y hora límite hacia la que cuenta el reloj. |

---

## 💻 Puesta en Marcha en Local

Solo necesitas tener PHP instalado en tu máquina:

```bash
# 1. Clonar el repositorio
git clone <URL_DEL_REPOSITORIO>
cd rally

# 2. Iniciar el servidor local
php -S localhost:8000
```

Abre en tu navegador:
- Portal actual: **[http://localhost:8000](http://localhost:8000)**
- Comparativa versión anterior: **[http://localhost:8000/index_old.php](http://localhost:8000/index_old.php)**

---

## 🌐 Despliegue en Producción

Sube todos los archivos a la carpeta asignada en el servidor (por ejemplo `https://servicios.hoy.es/rally/`).
- La aplicación autodetecta HTTPS, subdirectorios y dominios para generar las etiquetas canónicas y de Open Graph de forma automática.
- No requiere base de datos ni compiladores (CSS/JS nativos y ligeros).

---

## 🏛️ Organización y Colaboradores

- **Organiza**: [Diario HOY](https://www.hoy.es)
- **Colaboran**: 
  - Excmo. Ayuntamiento de Cáceres
  - Consorcio Cáceres Ciudad Histórica
  - Cáceres Capital Europea de la Cultura 2031
