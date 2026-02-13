  </main>

  <footer class="py-4 border-top bg-light">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
      <div class="text-muted small">© <?= date('Y') ?> Liens d'Espoir — Ensemble pour la santé</div>
      <div class="small">
        <a class="text-decoration-none me-3" href="<?= clean($base ?? '') ?>a-propos.php">À propos</a>
        <a class="text-decoration-none" href="<?= clean($base ?? '') ?>contact.php">Contact</a>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= clean($base ?? '') ?>public/js/app.js"></script>
  </body>
  </html>


