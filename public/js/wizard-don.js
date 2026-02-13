let currentStep = 1;

function showStep(step) {
  document.querySelectorAll('.wizard-step').forEach((el) => el.classList.remove('active'));
  const target = document.getElementById(`step-${step}`);
  if (target) target.classList.add('active');
  currentStep = step;
}

function validateStep(step) {
  if (step === 1) {
    const amount = document.getElementById('montant');
    return amount && Number(amount.value || 0) > 0;
  }
  if (step === 2) {
    // Si champs présents, vérifier required
    const form = document.getElementById('don-form');
    if (!form) return true;
    return window.validateForm('don-form');
  }
  return true;
}

function nextStep() {
  if (validateStep(currentStep)) showStep(currentStep + 1);
}

function prevStep() {
  showStep(Math.max(1, currentStep - 1));
}

document.addEventListener('DOMContentLoaded', function () {
  showStep(1);

  document.querySelectorAll('.btn-montant').forEach((btn) => {
    btn.addEventListener('click', function () {
      const montant = this.getAttribute('data-montant');
      const input = document.getElementById('montant');
      if (input) input.value = montant;
      document.querySelectorAll('.btn-montant').forEach((b) => b.classList.remove('active'));
      this.classList.add('active');
    });
  });
});


