document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.progress-bar[data-percentage]').forEach(function (bar) {
    const pct = bar.getAttribute('data-percentage') || '0';
    bar.style.width = pct + '%';
  });
});

window.validateForm = function (formId) {
  const form = document.getElementById(formId);
  if (!form) return true;
  const inputs = form.querySelectorAll('[required]');
  let isValid = true;
  inputs.forEach((input) => {
    if (!String(input.value || '').trim()) {
      input.classList.add('is-invalid');
      isValid = false;
    } else {
      input.classList.remove('is-invalid');
    }
  });
  return isValid;
};


