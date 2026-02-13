document.addEventListener('DOMContentLoaded', function () {
  // Count-up animation
  document.querySelectorAll('[data-countup]').forEach(function (el) {
    const raw = el.getAttribute('data-countup') || '0';
    const target = Number(raw);
    if (!Number.isFinite(target)) return;

    const duration = 700;
    const start = performance.now();
    const from = 0;

    function tick(now) {
      const t = Math.min(1, (now - start) / duration);
      const val = Math.round(from + (target - from) * (1 - Math.pow(1 - t, 3)));
      el.textContent = val.toLocaleString('fr-FR');
      if (t < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  });

  // Filter pending table
  const q = document.getElementById('pendingFilter');
  const tbody = document.querySelector('#pendingTable tbody');
  if (q && tbody) {
    q.addEventListener('input', function () {
      const needle = String(q.value || '').trim().toLowerCase();
      tbody.querySelectorAll('tr').forEach(function (tr) {
        const text = tr.textContent ? tr.textContent.toLowerCase() : '';
        tr.style.display = needle === '' || text.includes(needle) ? '' : 'none';
      });
    });
  }

  // Chart.js (optional)
  const canvas = document.getElementById('statusChart');
  if (canvas && window.Chart) {
    const active = Number(canvas.getAttribute('data-active') || '0');
    const pending = Number(canvas.getAttribute('data-pending') || '0');
    const done = Number(canvas.getAttribute('data-done') || '0');

    new Chart(canvas, {
      type: 'doughnut',
      data: {
        labels: ['Actives', 'En attente', 'Complétées'],
        datasets: [
          {
            data: [active, pending, done],
            backgroundColor: ['#2D6A4F', '#F77F00', '#06D6A0'],
            borderWidth: 0,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' },
          tooltip: { enabled: true },
        },
        cutout: '68%',
      },
    });
  }
});


