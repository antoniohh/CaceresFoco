<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cáceres en el Foco - Concurso Fotográfico | Diario HOY</title>

  <style>
    /* 1. RESET Y ESTRUCTURA GLOBAL */
    html, body {
      margin: 0 !important;
      padding: 0 !important;
      width: 100% !important;
      height: 100%;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif !important;
      background-color: #08090c !important;
      color: #0f172a;
      box-sizing: border-box !important;
      display: flex;
      flex-direction: column;
    }

    /* 2. FONDO CINEMÁTICO LIMPIO CON SWITCH MÓVIL */
    .site-wrapper {
      flex: 1 0 auto;
      width: 100%;
      background-color: #08090c !important;
      background-image: url('https://servicios.hoy.es/datos/imagen/concurso_fotos_caceres_bgh.jpg') !important;
      background-size: cover !important;
      background-position: center center !important;
      background-repeat: no-repeat !important;
      background-attachment: fixed !important;
      padding: 30px 16px 50px 16px;
      box-sizing: border-box;
    }

    @media (max-width: 767px) {
      .site-wrapper {
        background-image: url('https://servicios.hoy.es/datos/imagen/concurso_fotos_caceres_bgv.jpg') !important;
        background-attachment: scroll !important;
        padding: 20px 12px 40px 12px;
      }
    }

    /* 3. HEADER NATIVO HOY */
    header {
      width: 100% !important;
      background-color: #ffffff !important;
      padding: 18px 0 !important;
      display: flex !important;
      justify-content: center !important;
      align-items: center !important;
      position: relative !important;
    }
    header::after {
      content: "" !important;
      position: absolute !important;
      bottom: 0 !important;
      left: 0 !important;
      width: 100% !important;
      height: 1px !important;
      background-color: #e9e9e9 !important;
    }
    header nav img {
      height: 24px !important;
      display: block;
    }

    /* 4. TARJETA CONTENEDORA DE LA LANDING */
    .landing-card {
      max-width: 580px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 8px;
      padding: 28px 24px;
      box-shadow: 0 0 1px rgba(0,0,0,0.3), 0 4px 12px rgba(0,0,0,0.15), 0 20px 40px rgba(0,0,0,0.4);
      box-sizing: border-box;
    }

    /* Banner / Imagen Promocional del PDF */
    .hero-banner {
      width: 100%;
      border-radius: 6px;
      overflow: hidden;
      margin-bottom: 20px;
      border: 1px solid #e2e8f0;
    }
    .hero-banner img {
      width: 100%;
      height: auto;
      display: block;
    }

    .badge-tag {
      display: inline-block;
      background-color: #fef3c7;
      color: #92400e;
      font-size: 11px;
      font-weight: 800;
      padding: 4px 10px;
      border-radius: 4px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 12px;
    }

    .main-title {
      font-size: 24px;
      font-weight: 800;
      color: #0f172a;
      line-height: 1.25;
      margin: 0 0 8px 0;
      text-align: center;
    }

    .subtitle {
      font-size: 15px;
      font-weight: 700;
      color: #ce0000;
      text-align: center;
      margin: 0 0 16px 0;
    }

    .description {
      font-size: 14px;
      line-height: 1.6;
      color: #475569;
      text-align: center;
      margin: 0 0 24px 0;
    }

    /* TARJETA DE PREMIO DESTACADO */
    .prize-box {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      border: 1px solid #cbd5e1;
      border-left: 5px solid #ce0000;
      border-radius: 6px;
      padding: 14px 16px;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .prize-info strong {
      display: block;
      color: #0f172a;
      font-size: 15px;
    }
    .prize-info span {
      font-size: 13px;
      color: #64748b;
    }
    .prize-badge {
      background: #ce0000;
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      padding: 4px 8px;
      border-radius: 4px;
      white-space: nowrap;
    }

    /* 5. SECTOR DE BOTONES / BOTONERA PRINCIPAL */
    .action-links {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-bottom: 28px;
    }

    .btn-action {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 20px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 700;
      font-size: 14px;
      transition: all 0.2s ease;
      box-sizing: border-box;
    }
    .btn-action .btn-icon {
      font-size: 16px;
    }

    /* Botón Primario: Inscripción */
    .btn-primary {
      background-color: #ce0000;
      color: #ffffff !important;
      box-shadow: 0 4px 10px rgba(206, 0, 0, 0.25);
    }
    .btn-primary:hover {
      background-color: #a80000;
    }

    /* Botones Secundarios */
    .btn-secondary {
      background-color: #f1f5f9;
      color: #0f172a !important;
      border: 1px solid #cbd5e1;
    }
    .btn-secondary:hover {
      background-color: #e2e8f0;
    }

    /* 6. COLABORADORES Y ORGANIZACIÓN (LOGOS DEL PDF) */
    .sponsors-section {
      border-top: 1px solid #e2e8f0;
      padding-top: 18px;
      text-align: center;
    }
    .sponsors-title {
      font-size: 11px;
      font-weight: 700;
      color: #94a3b8;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 12px;
    }
    .sponsors-grid {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
    }
    .sponsor-item {
      font-size: 12px;
      font-weight: 700;
      color: #475569;
    }

    /* 7. FOOTER NATIVO CON LÍNEA 1200px */
    footer {
      flex-shrink: 0;
      width: 100% !important;
      background-color: #ffffff !important;
      padding: 20px 0 30px 0 !important;
      box-sizing: border-box !important;
    }
    footer::before {
      content: "" !important;
      display: block !important;
      width: 100% !important;
      max-width: 1200px !important;
      height: 2px !important;
      background-color: #202020 !important;
      margin: 0 auto 16px auto !important;
    }
    .footer-links {
      text-align: center !important;
      font-size: 11px !important;
      color: #333333 !important;
      line-height: 1.5 !important;
    }
    .footer-links a {
      color: #2b2b2b !important;
      text-decoration: none !important;
      margin: 0 3px !important;
    }
    .footer-links a:hover {
      text-decoration: underline !important;
    }
    .footer-links .dot {
      color: #888888 !important;
      margin: 0 4px !important;
    }

    @media (max-width: 767px) {
      .landing-card {
        width: calc(100% - 16px);
        padding: 20px 16px;
      }
      .main-title {
        font-size: 20px;
      }
    }
  </style>
</head>
<body>

  <!-- HEADER OFICIAL HOY -->
  <header>
    <nav>
      <a href="https://www.hoy.es" target="_blank" rel="noopener">
        <img src="https://s1.ppllstatics.com/starfish/latest/assets/images/logos/logo-hoy.svg" alt="Diario HOY" onerror="this.src='https://servicios.hoy.es/datos/imagen/logo-hoy.png'">
      </a>
    </nav>
  </header>

  <!-- CUERPO PRINCIPAL LANDING CON FONDO FOTOGRÁFICO -->
  <div class="site-wrapper">
    <div class="landing-card">

      <!-- CABECERA DEL CONCURSO -->
      <div style="text-align: center;">
        <span class="badge-tag">📸 40º Aniversario Patrimonio de la Humanidad</span>
        <h1 class="main-title">CÁCERES EN EL FOCO</h1>
        <p class="subtitle">Concurso fotográfico para todos</p>
      </div>

      <p class="description">
        Una ciudad patrimonio. Dos horas para mirarla a tu manera. El <strong>sábado 21 de noviembre</strong> te esperamos en la Plaza Mayor para fotografiar Cáceres en un evento familiar único organizado por Diario HOY.
      </p>

      <!-- PREMIO DESTACADO DEL PDF -->
      <div class="prize-box">
        <div class="prize-info">
          <span>Primer premio destacado:</span>
          <strong>Samsung Galaxy S25 FE</strong>
        </div>
        <div class="prize-badge">¡Particípa!</div>
      </div>

      <!-- BOTONERA DE ACCESO A LAS 3 FASES -->
      <div class="action-links">
        <a href="https://servicios.hoy.es/rally/inscripcion/" class="btn-action btn-primary">
          <span>1. Inscríbete al Concurso</span>
          <span class="btn-icon">➔</span>
        </a>

        <a href="https://servicios.hoy.es/rally/subir/" class="btn-action btn-secondary">
          <span>2. Subir tus Fotografías</span>
          <span class="btn-icon">📷</span>
        </a>

        <a href="https://servicios.hoy.es/rally/galeria/" class="btn-action btn-secondary">
          <span>3. Ver Galería y Ganadores</span>
          <span class="btn-icon">🏆</span>
        </a>
      </div>

      <!-- SECCIÓN INSTITUCIONAL Y COLABORADORES (DEL PDF) -->
      <div class="sponsors-section">
        <div class="sponsors-title">Organiza y Colaboran</div>
        <div class="sponsors-grid">
          <span class="sponsor-item">Organiza: <strong>HOY</strong></span>
          <span class="sponsor-item">Colabora: <strong>Ayuntamiento de Cáceres</strong></span>
          <span class="sponsor-item"><strong>Consorcio Cáceres Ciudad Histórica</strong></span>
        </div>
      </div>

    </div>
  </div>

  <!-- FOOTER OFICIAL HOY -->
  <footer>
    <div class="custom-hoy-footer-content">
      <div class="footer-links">
        <a href="https://www.hoy.es/contactar/" target="_blank">Contactar</a> <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/aviso-legal.html" target="_blank">Aviso legal</a> <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/condiciones-uso.html" target="_blank">Condiciones de uso</a> <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/politica-privacidad.html" target="_blank">Política de privacidad</a> <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/publicidad/" target="_blank">Publicidad</a> <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/mapa-web.html" target="_blank">Mapa web</a> <span class="dot">&middot;</span>
        <a href="https://www.hoy.es/politica-cookies.html" target="_blank">Política de cookies</a>
      </div>
    </div>
  </footer>

</body>
</html>
