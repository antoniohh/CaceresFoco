<?php
/**
 * Concurso Fotográfico: Cáceres en el Foco
 * Diario HOY - 40º Aniversario Patrimonio de la Humanidad
 * Compatible con PHP 5.3+
 */

// 1. CARGA DE CONFIGURACIÓN DINÁMICA (config.json)
$configFile = dirname(__FILE__) . '/config.json';
$config = array(
    'estado' => 'INSCRIPCIONES_ABIERTAS',
    'mostrar_temporizador' => true,
    'fecha_objetivo_temporizador' => '2026-11-21T12:00:00+01:00'
);

if (file_exists($configFile)) {
    $fileContent = @file_get_contents($configFile);
    if ($fileContent) {
        $loadedConfig = json_decode($fileContent, true);
        if (is_array($loadedConfig)) {
            if (isset($loadedConfig['estado'])) {
                $config['estado'] = strtoupper(trim($loadedConfig['estado']));
            }
            if (isset($loadedConfig['mostrar_temporizador'])) {
                $config['mostrar_temporizador'] = (bool)$loadedConfig['mostrar_temporizador'];
            }
            if (isset($loadedConfig['fecha_objetivo_temporizador'])) {
                $config['fecha_objetivo_temporizador'] = $loadedConfig['fecha_objetivo_temporizador'];
            }
        }
    }
}

// 2. DEFINICIÓN DE ESTADOS Y CONTROL DE BOTONES
$estado = $config['estado'];

// Botones por defecto
$btn1 = array(
    'activo' => false,
    'destacado' => false,
    'url' => 'inscripcion/',
    'titulo' => '1. Inscríbete al Concurso',
    'subtexto' => 'Asegura tu plaza oficial previa · 100% Gratis',
    'badge' => 'Cerrado',
    'badge_class' => 'tag-closed',
    'icono' => '➔'
);

$btn2 = array(
    'activo' => false,
    'destacado' => false,
    'url' => 'subir/',
    'titulo' => '2. Subir tus Fotografías',
    'subtexto' => 'Carga las instantáneas que tomes durante el rally',
    'badge' => 'Cerrado',
    'badge_class' => 'tag-closed',
    'icono' => '📷'
);

$btn3 = array(
    'activo' => false,
    'destacado' => false,
    'url' => 'galeria/',
    'titulo' => '3. Ver Galería y Ganadores',
    'subtexto' => 'Descubre las mejores fotografías y el fallo del jurado',
    'badge' => 'Próximamente',
    'badge_class' => 'tag-soon',
    'icono' => '🏆'
);

switch ($estado) {
    case 'CERRADO':
        $badgeTexto = 'Próximamente';
        $badgeClase = 'badge-closed';
        $btn1['subtexto'] = 'Inscripciones próximamente';
        $btn1['badge'] = 'Próximamente';
        $btn1['badge_class'] = 'tag-soon';
        $btn2['subtexto'] = 'Disponible el 21 de noviembre';
        $btn3['subtexto'] = 'Disponible tras el concurso';
        break;

    case 'INSCRIPCIONES_ABIERTAS':
    default:
        $badgeTexto = 'Inscripciones Abiertas';
        $badgeClase = 'badge-open';
        $btn1['activo'] = true;
        $btn1['destacado'] = true;
        $btn1['subtexto'] = 'Asegura tu plaza oficial previa · 100% Gratis';
        $btn1['badge'] = '¡Abierto ahora!';
        $btn1['badge_class'] = 'tag-open';

        $btn2['subtexto'] = 'Habilitado el 21 de noviembre de 12:00 a 15:00h';
        $btn2['badge'] = 'Cerrado';
        $btn2['badge_class'] = 'tag-closed';

        $btn3['subtexto'] = 'Disponible tras el fallo del jurado';
        $btn3['badge'] = 'Próximamente';
        $btn3['badge_class'] = 'tag-soon';
        break;

    case 'CONCURSO_ABIERTO':
        $badgeTexto = '¡Rally en Marcha! Sube tus Fotos';
        $badgeClase = 'badge-live';

        $btn1['activo'] = false;
        $btn1['destacado'] = false;
        $btn1['subtexto'] = 'Plazo de inscripción online completado';
        $btn1['badge'] = 'Finalizado';
        $btn1['badge_class'] = 'tag-done';

        $btn2['activo'] = true;
        $btn2['destacado'] = true;
        $btn2['subtexto'] = '¡Sube tus fotos! Plazo abierto hasta las 15:00h';
        $btn2['badge'] = '¡Abierto ahora!';
        $btn2['badge_class'] = 'tag-live';

        $btn3['subtexto'] = 'El jurado deliberará tras el cierre del plazo';
        $btn3['badge'] = 'Próximamente';
        $btn3['badge_class'] = 'tag-soon';
        break;

    case 'GALERIA_Y_GANADORES':
        $badgeTexto = 'Ganadores y Galería Publicada';
        $badgeClase = 'badge-results';

        $btn1['activo'] = false;
        $btn1['destacado'] = false;
        $btn1['subtexto'] = 'Edición finalizada';
        $btn1['badge'] = 'Finalizado';
        $btn1['badge_class'] = 'tag-done';

        $btn2['activo'] = false;
        $btn2['destacado'] = false;
        $btn2['subtexto'] = 'Plazo de recepción cerrado a las 15:00h';
        $btn2['badge'] = 'Cerrado';
        $btn2['badge_class'] = 'tag-closed';

        $btn3['activo'] = true;
        $btn3['destacado'] = true;
        $btn3['subtexto'] = 'Explora las fotos premiadas y la selección oficial';
        $btn3['badge'] = '¡Visible ahora!';
        $btn3['badge_class'] = 'tag-open';
        break;
}

// 3. DETECCIÓN DE PROTOCOLO Y DOMINIO PARA SEO Y REDES
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'servicios.hoy.es';

if (isset($_SERVER['PHP_SELF']) && $_SERVER['PHP_SELF'] !== '') {
    $scriptDir = dirname($_SERVER['PHP_SELF']);
    if ($scriptDir === '.' || $scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '') {
        $pathPrefix = '/rally/';
    } else {
        $pathPrefix = '/' . trim($scriptDir, '/\\') . '/';
    }
} else {
    $pathPrefix = '/rally/';
}
$baseUrl = $protocol . $host . $pathPrefix;

// Metadatos para SEO y Redes Sociales
$siteTitle = 'Cáceres en el Foco - Concurso Fotográfico 40º Aniversario | Diario HOY';
$siteDesc = 'Una ciudad patrimonio. Dos horas para mirarla a tu manera. Cáceres celebra su 40 aniversario como ciudad Patrimonio de la Humanidad y en HOY queremos celebrarlo contigo con un concurso fotográfico familiar. El sábado 21 de noviembre te esperamos en la Plaza Mayor.';
$ogImage = $baseUrl . 'assets/img/concurso_fotos_caceres.jpg';
$shareText = '¡Participa en Cáceres en el Foco! Concurso fotográfico de Diario HOY en la Plaza Mayor. ¡Podrás ganar un Samsung Galaxy S25 FE!';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  
  <title><?php echo htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  
  <!-- SEO BÁSICO -->
  <meta name="description" content="<?php echo htmlspecialchars($siteDesc, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="keywords" content="Cáceres en el Foco, concurso fotográfico, Diario HOY, rally fotográfico Cáceres, Plaza Mayor Cáceres, Patrimonio de la Humanidad, fotos Cáceres, Samsung Galaxy S25 FE, Vocento">
  <meta name="author" content="Diario HOY">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="shortcut icon" href="https://static.vocstatic.com/voc26/latest/assets/images/favicon/hoy/favicon.ico" type="image/x-icon" />
  <link rel="icon" href="https://static.vocstatic.com/voc26/latest/assets/images/favicon/hoy/favicon.ico" type="image/x-icon" />
  <meta name="theme-color" content="#ce0000">

  <!-- OPEN GRAPH / FACEBOOK / WHATSAPP / LINKEDIN -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Diario HOY">
  <meta property="og:locale" content="es_ES">
  <meta property="og:url" content="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($siteDesc, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:image:type" content="image/jpeg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="627">
  <meta property="og:image:alt" content="Concurso fotográfico Cáceres en el Foco - Diario HOY">

  <!-- TWITTER CARDS -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="@hoyextremadura">
  <meta name="twitter:creator" content="@hoyextremadura">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($siteDesc, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>">

  <!-- DATOS ESTRUCTURADOS SCHEMA.ORG (JSON-LD) PARA GOOGLE -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Event",
    "name": "Cáceres en el Foco - Concurso Fotográfico 40º Aniversario",
    "description": "Una ciudad patrimonio. Dos horas para mirarla a tu manera. Concurso fotográfico para todos organizado por Diario HOY con motivo del 40º Aniversario de Cáceres como Patrimonio de la Humanidad.",
    "startDate": "2026-11-21T12:00:00+01:00",
    "endDate": "2026-11-21T14:00:00+01:00",
    "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
    "eventStatus": "https://schema.org/EventScheduled",
    "location": {
      "@type": "Place",
      "name": "Casco Antiguo y Plaza Mayor de Cáceres",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Plaza Mayor",
        "addressLocality": "Cáceres",
        "postalCode": "10003",
        "addressCountry": "ES"
      }
    },
    "image": [
      "<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>"
    ],
    "organizer": {
      "@type": "Organization",
      "name": "Diario HOY",
      "url": "https://www.hoy.es"
    },
    "isAccessibleForFree": true,
    "offers": {
      "@type": "Offer",
      "price": "0",
      "priceCurrency": "EUR",
      "availability": "https://schema.org/InStock",
      "url": "<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>inscripcion/"
    }
  }
  </script>

  <!-- ESTILOS CSS PRINCIPALES -->
  <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>

  <!-- 1. ENCABEZADO OFICIAL DIARIO HOY (COHERENCIA CON QUALIFIO) -->
  <header class="site-header" role="banner">
    <div class="header-inner">
      <nav aria-label="Navegación principal">
        <a class="logo-link" href="https://www.hoy.es" target="_blank" rel="noopener" title="Diario HOY - Extremadura">
          <img src="https://s1.ppllstatics.com/starfish/latest/assets/images/logos/logo-hoy.svg" 
               alt="Diario HOY" 
               onerror="this.src='https://servicios.hoy.es/datos/imagen/logo-hoy.png'">
        </a>
      </nav>
    </div>
  </header>

  <!-- 2. CONTENEDOR PRINCIPAL CON FONDO RETRATO/PAISAJE DE CÁCERES -->
  <main class="site-wrapper" role="main">
    <article class="landing-card">

      <!-- BANNER ILUSTRADO OFICIAL (CARTELES Y REDES) -->
      <div class="hero-visual">
        <img src="assets/img/concurso_fotos_caceres.jpg" 
             alt="Cáceres en el Foco - Concurso fotográfico para todos" 
             width="1200" 
             height="627">
        <span class="hero-overlay-badge <?php echo htmlspecialchars($badgeClase, ENT_QUOTES, 'UTF-8'); ?>">
          <span class="pulse-dot"></span>
          <?php echo htmlspecialchars($badgeTexto, ENT_QUOTES, 'UTF-8'); ?>
        </span>
      </div>

      <!-- CUERPO DE LA TARJETA -->
      <div class="card-body">

        <!-- INSIGNIA DE ANIVERSARIO -->
        <div style="text-align: center; margin-bottom: 12px;">
          <span class="tag-anniversary">📸 40º Aniversario Patrimonio de la Humanidad</span>
        </div>

        <!-- CLAIM Y TEXTO EDITORIAL EXACTO DEL CARTEL (PDF OFICIAL) -->
        <div class="editorial-claim">
          <p class="quote-text">
            Una ciudad patrimonio.<br>
            Dos horas para mirarla a tu manera.
          </p>
          <p class="desc-text">
            Cáceres celebra su 40 aniversario como ciudad Patrimonio de la Humanidad y en HOY queremos celebrarlo contigo con un concurso fotográfico familiar.
          </p>
          <p class="desc-subtext">
            El sábado 21 de noviembre te esperamos en la Plaza Mayor para fotografiar Cáceres.
          </p>
        </div>

        <!-- DATOS CLAVE DEL RALLY FOTOGRÁFICO (OFICIALES SEGÚN BASES) -->
        <div class="event-pills-grid">
          <div class="event-pill">
            <span class="pill-icon">📅</span>
            <div class="pill-content">
              <span class="pill-label">Fecha del Rally</span>
              <span class="pill-value">Sábado 21 Noviembre</span>
            </div>
          </div>
          <div class="event-pill">
            <span class="pill-icon">⏱️</span>
            <div class="pill-content">
              <span class="pill-label">Horario del Tour</span>
              <span class="pill-value">12:00 a 14:00 horas</span>
            </div>
          </div>
          <div class="event-pill">
            <span class="pill-icon">📍</span>
            <div class="pill-content">
              <span class="pill-label">Recorrido</span>
              <span class="pill-value">Casco Antiguo de Cáceres</span>
            </div>
          </div>
          <div class="event-pill">
            <span class="pill-icon">🎟️</span>
            <div class="pill-content">
              <span class="pill-label">Participación</span>
              <span class="pill-value">100% Gratuita · Móviles</span>
            </div>
          </div>
        </div>

        <!-- CUENTA ATRÁS DINÁMICA (CONTROLABLE DESDE CONFIG.JSON) -->
        <?php if ($config['mostrar_temporizador']): ?>
        <div class="countdown-wrapper" id="event-countdown" data-target-date="<?php echo htmlspecialchars($config['fecha_objetivo_temporizador'], ENT_QUOTES, 'UTF-8'); ?>">
          <div class="countdown-title">
            <span>⏱️ Cuenta atrás para el inicio del rally fotográfico</span>
          </div>
          <div class="countdown-digits-grid">
            <div class="countdown-item">
              <span class="digit-number" id="count-days">--</span>
              <span class="digit-unit">Días</span>
            </div>
            <span class="countdown-separator">:</span>
            <div class="countdown-item">
              <span class="digit-number" id="count-hours">--</span>
              <span class="digit-unit">Horas</span>
            </div>
            <span class="countdown-separator">:</span>
            <div class="countdown-item">
              <span class="digit-number" id="count-mins">--</span>
              <span class="digit-unit">Min</span>
            </div>
            <span class="countdown-separator">:</span>
            <div class="countdown-item">
              <span class="digit-number" id="count-secs">--</span>
              <span class="digit-unit">Seg</span>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- DESTACADO: PREMIOS OFICIALES DE LAS BASES -->
        <div class="prize-showcase">
          <div class="prize-content">
            <span class="prize-header-badge">🏆 1er Premio Destacado</span>
            <h2 class="prize-model-title">Samsung Galaxy S25 FE</h2>
            <p class="prize-pitch">
              Podrás ganar un fantástico <strong>Samsung Galaxy S25 FE</strong> para inmortalizar cada rincón de Cáceres con la mejor cámara y tecnología.
            </p>

            <!-- 2º Y 3ER PREMIO SEGÚN BASES LEGALES -->
            <div class="secondary-prizes-grid">
              <div class="secondary-prize-card">
                <span class="sec-icon">🥈</span>
                <div class="sec-info">
                  <strong>2º Premio</strong>
                  <span>Tarjeta Amazon valorada en 200€</span>
                </div>
              </div>
              <div class="secondary-prize-card">
                <span class="sec-icon">🥉</span>
                <div class="sec-info">
                  <strong>3er Premio</strong>
                  <span>Bono desayuno + visita bodega en Atrio</span>
                </div>
              </div>
            </div>
          </div>

          <div class="prize-image-wrap">
            <img src="assets/img/samsung-galaxy-s25-fe.png" 
                 alt="Premio Samsung Galaxy S25 FE" 
                 width="160" 
                 height="265"
                 loading="lazy">
          </div>
        </div>

        <!-- BOTONERA DE ACCESO A LAS 3 FASES (CONTROLADA POR CONFIG.JSON) -->
        <section class="contest-steps-section" aria-label="Fases del concurso">
          <div class="section-label">Fases del concurso</div>
          <div class="action-buttons-list">
            
            <!-- FASE 1: INSCRIPCIÓN -->
            <?php if ($btn1['activo']): ?>
            <a href="<?php echo $btn1['url']; ?>" class="action-card <?php echo $btn1['destacado'] ? 'card-primary' : 'card-secondary'; ?>" title="Inscribirse al concurso fotográfico">
              <div class="card-step-badge">1</div>
              <div class="card-info-wrap">
                <span class="card-main-title">
                  <?php echo $btn1['titulo']; ?>
                  <span class="card-state-tag <?php echo $btn1['badge_class']; ?>"><?php echo $btn1['badge']; ?></span>
                </span>
                <span class="card-subtext"><?php echo $btn1['subtexto']; ?></span>
              </div>
              <div class="card-action-icon"><?php echo $btn1['icono']; ?></div>
            </a>
            <?php else: ?>
            <div class="action-card card-disabled" title="Fase no disponible en este momento">
              <div class="card-step-badge">1</div>
              <div class="card-info-wrap">
                <span class="card-main-title">
                  <?php echo $btn1['titulo']; ?>
                  <span class="card-state-tag <?php echo $btn1['badge_class']; ?>"><?php echo $btn1['badge']; ?></span>
                </span>
                <span class="card-subtext"><?php echo $btn1['subtexto']; ?></span>
              </div>
              <div class="card-action-icon"><?php echo $btn1['icono']; ?></div>
            </div>
            <?php endif; ?>

            <!-- FASE 2: SUBIR FOTOS -->
            <?php if ($btn2['activo']): ?>
            <a href="<?php echo $btn2['url']; ?>" class="action-card <?php echo $btn2['destacado'] ? 'card-primary' : 'card-secondary'; ?>" title="Subir tus fotografías">
              <div class="card-step-badge">2</div>
              <div class="card-info-wrap">
                <span class="card-main-title">
                  <?php echo $btn2['titulo']; ?>
                  <span class="card-state-tag <?php echo $btn2['badge_class']; ?>"><?php echo $btn2['badge']; ?></span>
                </span>
                <span class="card-subtext"><?php echo $btn2['subtexto']; ?></span>
              </div>
              <div class="card-action-icon"><?php echo $btn2['icono']; ?></div>
            </a>
            <?php else: ?>
            <div class="action-card card-disabled" title="Fase no disponible en este momento">
              <div class="card-step-badge">2</div>
              <div class="card-info-wrap">
                <span class="card-main-title">
                  <?php echo $btn2['titulo']; ?>
                  <span class="card-state-tag <?php echo $btn2['badge_class']; ?>"><?php echo $btn2['badge']; ?></span>
                </span>
                <span class="card-subtext"><?php echo $btn2['subtexto']; ?></span>
              </div>
              <div class="card-action-icon"><?php echo $btn2['icono']; ?></div>
            </div>
            <?php endif; ?>

            <!-- FASE 3: GALERÍA Y GANADORES -->
            <?php if ($btn3['activo']): ?>
            <a href="<?php echo $btn3['url']; ?>" class="action-card <?php echo $btn3['destacado'] ? 'card-primary' : 'card-secondary'; ?>" title="Ver galería y ganadores">
              <div class="card-step-badge">3</div>
              <div class="card-info-wrap">
                <span class="card-main-title">
                  <?php echo $btn3['titulo']; ?>
                  <span class="card-state-tag <?php echo $btn3['badge_class']; ?>"><?php echo $btn3['badge']; ?></span>
                </span>
                <span class="card-subtext"><?php echo $btn3['subtexto']; ?></span>
              </div>
              <div class="card-action-icon"><?php echo $btn3['icono']; ?></div>
            </a>
            <?php else: ?>
            <div class="action-card card-disabled" title="Fase no disponible en este momento">
              <div class="card-step-badge">3</div>
              <div class="card-info-wrap">
                <span class="card-main-title">
                  <?php echo $btn3['titulo']; ?>
                  <span class="card-state-tag <?php echo $btn3['badge_class']; ?>"><?php echo $btn3['badge']; ?></span>
                </span>
                <span class="card-subtext"><?php echo $btn3['subtexto']; ?></span>
              </div>
              <div class="card-action-icon"><?php echo $btn3['icono']; ?></div>
            </div>
            <?php endif; ?>

          </div>
        </section>

        <!-- GUÍA OFICIAL Y PREGUNTAS FRECUENTES (EXTRAÍDAS DE LAS BASES LEGALES) -->
        <div class="guide-accordion">
          
          <div class="accordion-item active">
            <button class="accordion-trigger" aria-expanded="true">
              <span>¿En qué consiste el concurso y cuál es el horario?</span>
              <span class="chevron">▼</span>
            </button>
            <div class="accordion-body">
              <p>
                El concurso se llevará a cabo el <strong>sábado 21 de noviembre</strong> mediante un tour fotográfico por el casco antiguo de Cáceres en horario de <strong>12:00 a 14:00 horas</strong>.
              </p>
              <p>
                Los participantes podrán capturar la belleza de la ciudad monumental y deberán subir un <strong>máximo de tres fotografías</strong> a través de esta plataforma antes de las <strong>15:00 horas</strong> del mismo 21 de noviembre.
              </p>
            </div>
          </div>

          <div class="accordion-item">
            <button class="accordion-trigger" aria-expanded="false">
              <span>¿Quién puede participar y con qué dispositivo?</span>
              <span class="chevron">▼</span>
            </button>
            <div class="accordion-body">
              <p>
                El concurso está dirigido a personas físicas <strong>mayores de 18 años o menores acompañados</strong> de padre, madre o tutor legal (quienes deberán cumplimentar el formulario y la autorización correspondiente).
              </p>
              <p>
                La fotografía se realizará mediante <strong>cualquier tipo de dispositivo móvil</strong>. La participación es completamente <strong>gratuita</strong>.
              </p>
            </div>
          </div>

          <div class="accordion-item">
            <button class="accordion-trigger" aria-expanded="false">
              <span>¿Cuáles son los premios oficiales del certamen?</span>
              <span class="chevron">▼</span>
            </button>
            <div class="accordion-body">
              <ol>
                <li><strong>Primer premio:</strong> Smartphone Samsung Galaxy S25 FE.</li>
                <li><strong>Segundo premio:</strong> Tarjeta regalo Amazon valorada en 200€.</li>
                <li><strong>Tercer premio:</strong> Bono Regalo de desayuno para 2 personas con visita guiada a la bodega en el prestigioso restaurante Atrio de Cáceres (3 estrellas Michelin).</li>
              </ol>
            </div>
          </div>

          <div class="accordion-item">
            <button class="accordion-trigger" aria-expanded="false">
              <span>¿Cómo se seleccionan los ganadores? (Jurado profesional)</span>
              <span class="chevron">▼</span>
            </button>
            <div class="accordion-body">
              <p>
                No hay votación popular. Un <strong>jurado profesional nombrado por Diario HOY</strong> seleccionará a los 3 ganadores y 3 suplentes, siendo su fallo inapelable.
              </p>
              <p>
                Los criterios de valoración se basarán en la <strong>fuerza visual</strong> y en <strong>ensalzar la esencia y belleza de la ciudad de Cáceres</strong>.
              </p>
              <p>
                La comunicación oficial de los ganadores se realizará a partir del <strong>1 de diciembre de 2026</strong> en el diario HOY y en este sitio web.
              </p>
            </div>
          </div>

        </div>

        <!-- BARRA SOCIAL PARA COMPARTIR EL CONCURSO -->
        <section class="share-section" aria-label="Compartir en redes sociales">
          <div class="share-title">¡Corre la voz entre amigos y amantes de la fotografía!</div>
          <div class="share-buttons-row">
            
            <!-- WhatsApp -->
            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($shareText . ' ' . $baseUrl); ?>" 
               target="_blank" 
               rel="noopener" 
               class="share-btn share-whatsapp"
               title="Compartir en WhatsApp">
              <svg viewBox="0 0 24 24"><path d="M12.031 2C6.504 2 2 6.494 2 12.016c0 1.956.564 3.774 1.537 5.32L2 22l4.825-1.503A9.97 9.97 0 0 0 12.031 22C17.556 22 22 17.538 22 12.016 22 6.494 17.556 2 12.031 2zm5.787 14.394c-.24.673-1.396 1.285-1.925 1.365-.515.078-1.184.11-3.418-.813-2.673-1.106-4.38-3.83-4.512-4.008-.133-.178-1.077-1.433-1.077-2.733 0-1.3.682-1.939.924-2.195.24-.256.527-.32.703-.32.176 0 .352.002.507.01.164.007.385-.062.602.46.223.535.76 1.854.827 1.99.066.136.11.295.02.473-.09.178-.135.29-.267.447-.133.156-.279.35-.398.47-.133.133-.272.278-.117.544.155.267.688 1.135 1.474 1.835 1.012.902 1.865 1.181 2.132 1.314.267.133.424.111.58-.067.157-.178.67-.78 848-1.047.178-.267.356-.223.596-.134.24.089 1.52.716 1.78.847.26.13.434.195.498.306.064.111.064.646-.176 1.319z"/></svg>
              <span>WhatsApp</span>
            </a>

            <!-- X (Twitter) -->
            <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($shareText); ?>&url=<?php echo urlencode($baseUrl); ?>&via=hoyextremadura" 
               target="_blank" 
               rel="noopener" 
               class="share-btn share-x"
               title="Compartir en X">
              <svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
              <span>Compartir</span>
            </a>

            <!-- Facebook -->
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($baseUrl); ?>" 
               target="_blank" 
               rel="noopener" 
               class="share-btn share-facebook"
               title="Compartir en Facebook">
              <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
              <span>Facebook</span>
            </a>

            <!-- Copiar Enlace -->
            <button type="button" class="share-btn share-copy" id="btn-copy-link" title="Copiar enlace al portapapeles">
              <span>📋 Copiar Enlace</span>
            </button>

          </div>
        </section>

        <!-- SECCIÓN DE PATROCINADORES Y COLABORADORES OFICIALES (DEL CARTEL) -->
        <section class="sponsors-wrapper" aria-label="Organización y colaboradores">
          <div class="sponsors-label">Organiza y Colaboran</div>
          <div class="sponsors-banner-wrap">
            <img src="assets/img/patrocinadores.png" 
                 alt="Organiza: Diario HOY. Colabora: Ayuntamiento de Cáceres, Capital Europea de la Cultura 2031, Consorcio Cáceres Ciudad Histórica" 
                 width="2067" 
                 height="267"
                 loading="lazy">
          </div>
        </section>

      </div>
    </article>
  </main>

  <!-- NOTIFICACIÓN TOAST (COPIAR ENLACE) -->
  <div class="toast-notice" id="toast-notice" role="status" aria-live="polite">
    ✓ ¡Enlace del concurso copiado al portapapeles!
  </div>

  <!-- 3. PIE DE PÁGINA NATIVO DIARIO HOY (COHERENCIA CON QUALIFIO) -->
  <footer class="site-footer" role="contentinfo">
    <div class="footer-content">
      <div class="footer-links">
        <a href="https://www.hoy.es/contactar/" target="_blank" rel="noopener">Contactar</a> 
        <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/aviso-legal.html" target="_blank" rel="noopener">Aviso legal</a> 
        <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/condiciones-uso.html" target="_blank" rel="noopener">Condiciones de uso</a> 
        <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/politica-privacidad.html" target="_blank" rel="noopener">Política de privacidad</a> 
        <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/publicidad/" target="_blank" rel="noopener">Publicidad</a> 
        <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/mapa-web.html" target="_blank" rel="noopener">Mapa web</a> 
        <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/politica-cookies.html" target="_blank" rel="noopener">Política de cookies</a>
      </div>
    </div>
  </footer>

  <!-- SCRIPT CON INTERACTIVIDAD LIGERA -->
  <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>
