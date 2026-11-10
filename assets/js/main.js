/**
 * ==============================================================================
 * CÁCERES EN EL FOCO - CONCURSO FOTOGRÁFICO | DIARIO HOY
 * 40º Aniversario de Cáceres como Patrimonio de la Humanidad
 *
 * @author    Antonio Horrillo <ahorrillo@hoy.es>
 * @copyright (c) 2026 Corporación de Medios de Extremadura, S.A. (Grupo Vocento)
 *            HOY Diario de Extremadura · Avda. del Diario Hoy, s/n, 06008 Badajoz
 *            CIF: A-78865441 · Todos los derechos reservados.
 * @link      https://www.hoy.es
 * @link      https://servicios.hoy.es/caceresenelfoco/
 *
 * Funcionalidades interactivas (Vanilla JS compatible y ligero)
 * ==============================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. CUENTA ATRÁS DINÁMICA HASTA EL RALLY (21 DE NOVIEMBRE 2026, 10:00:00)
  initCountdown();

  // 2. ACORDEÓN INTERACTIVO ("CÓMO PARTICIPAR")
  initAccordion();

  // 3. BOTÓN DE COPIAR ENLACE Y COMPARTIR
  initShareFeatures();
});

/**
 * Inicializa el reloj de cuenta atrás
 */
function initCountdown() {
  var daysEl = document.getElementById('count-days');
  var hoursEl = document.getElementById('count-hours');
  var minsEl = document.getElementById('count-mins');
  var secsEl = document.getElementById('count-secs');
  var countdownContainer = document.getElementById('event-countdown');

  if (!daysEl || !hoursEl || !minsEl || !secsEl) return;

  // Fecha objetivo del evento (configurable desde config.json via data-target-date)
  var dateAttr = countdownContainer.getAttribute('data-target-date');
  var targetDate = dateAttr ? new Date(dateAttr).getTime() : new Date('2026-11-21T12:00:00+01:00').getTime();

  function update() {
    var now = new Date().getTime();
    var distance = targetDate - now;

    if (distance < 0) {
      // Si ya venció la fecha objetivo
      var titleEl = document.querySelector('.countdown-title');
      if (titleEl) {
        var expiredText = countdownContainer.getAttribute('data-expired-label') || '⚡ ¡Tiempo completado!';
        titleEl.innerHTML = expiredText;
      }
      daysEl.textContent = '00';
      hoursEl.textContent = '00';
      minsEl.textContent = '00';
      secsEl.textContent = '00';
      return;
    }

    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

    daysEl.textContent = days < 10 ? '0' + days : days;
    hoursEl.textContent = hours < 10 ? '0' + hours : hours;
    minsEl.textContent = minutes < 10 ? '0' + minutes : minutes;
    secsEl.textContent = seconds < 10 ? '0' + seconds : seconds;
  }

  update();
  setInterval(update, 1000);
}

/**
 * Control del acordeón interactivo
 */
function initAccordion() {
  var triggers = document.querySelectorAll('.accordion-trigger');
  triggers.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = this.closest('.accordion-item');
      var isOpen = item.classList.contains('active');

      // Cerrar otros abiertos (opcional para mantener orden)
      document.querySelectorAll('.accordion-item.active').forEach(function (activeItem) {
        if (activeItem !== item) {
          activeItem.classList.remove('active');
          var trigger = activeItem.querySelector('.accordion-trigger');
          if (trigger) trigger.setAttribute('aria-expanded', 'false');
        }
      });

      // Alternar el actual
      if (isOpen) {
        item.classList.remove('active');
        this.setAttribute('aria-expanded', 'false');
      } else {
        item.classList.add('active');
        this.setAttribute('aria-expanded', 'true');
      }
    });
  });
}

/**
 * Funcionalidades de compartir en redes y copiar enlace
 */
function initShareFeatures() {
  var copyBtn = document.getElementById('btn-copy-link');
  var toast = document.getElementById('toast-notice');

  if (copyBtn) {
    copyBtn.addEventListener('click', function (e) {
      e.preventDefault();
      var currentUrl = window.location.href;

      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(currentUrl).then(showToast).catch(function () {
          fallbackCopyText(currentUrl);
        });
      } else {
        fallbackCopyText(currentUrl);
      }
    });
  }

  function fallbackCopyText(text) {
    var textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.top = '-9999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
      document.execCommand('copy');
      showToast();
    } catch (err) {
      alert('Enlace del concurso: ' + text);
    }
    document.body.removeChild(textArea);
  }

  function showToast() {
    if (!toast) return;
    toast.classList.add('show');
    setTimeout(function () {
      toast.classList.remove('show');
    }, 2800);
  }
}
