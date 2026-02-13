# Liens d'Espoir (Crowdfunding médical) — PHP/MySQL (MVC léger)

Plateforme de cagnottes médicales (patients) + dons simulés (donateurs), sécurisée (PDO préparé, hash mdp, CSRF, validation uploads).

## Prérequis
- PHP 8.x (XAMPP/WAMP)
- MySQL/MariaDB

## Installation rapide (XAMPP)
1. Copier ce projet dans `htdocs/liens-espoir` (ou équivalent).
2. Créer une base MySQL : `liens_espoir`.
3. Importer `database/schema.sql` dans phpMyAdmin.
4. Configurer l’accès BDD dans `config/database.php`.
5. Ouvrir `http://localhost/liens-espoir/`

## Comptes de test
- **Admin**: `admin@liensespoir.ci` / `admin123`
- **Patients**: `jean.kouassi@email.ci` / `password123` ; `fatou.diallo@email.ci` / `password123`
- **Donateurs**: `marie.kone@email.ci` / `password123` ; `ibrahim.o@email.ci` / `password123`

## Structure
Voir les dossiers `config/`, `includes/`, `models/`, `controllers/`, `views/`, `public/`, `admin/`, `database/`.

## Notes
- Le paiement est **simulé** (projet étudiant). Le don est validé côté serveur et enregistré en base.
- Les reçus PDF ne sont pas générés (un reçu HTML imprimable est fourni). Vous pouvez brancher Dompdf plus tard.


