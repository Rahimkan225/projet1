# Guide de Migration vers Supabase - Complet

## ✅ Modifications Effectuées

### 1. Configuration
- ✅ `config/database.php` : Support MySQL et Supabase via variable d'environnement `USE_SUPABASE`
- ✅ `config/supabase.php` : Fonction `getSupabaseConnection()` pour connexion PostgreSQL
- ✅ `config/init.php` : Inclusion des utilitaires de base de données

### 2. Utilitaires de Compatibilité
- ✅ `includes/db_utils.php` : Fonctions utilitaires pour MySQL/PostgreSQL
  - `isPostgres()` : Détecte le type de base de données
  - `getLastInsertId()` : Compatible MySQL/PostgreSQL
  - `columnExists()` : Vérifie l'existence d'une colonne (compatible)
  - `orderByField()` : Convertit `FIELD()` MySQL en `CASE WHEN` PostgreSQL

### 3. Modèles Adaptés
- ✅ `models/User.php` :
  - Utilise `columnExists()` au lieu de requêtes `INFORMATION_SCHEMA` directes
  - Utilise `orderByField()` pour le tri par type d'utilisateur
  - Utilise `getLastInsertId()` pour compatibilité PostgreSQL

- ✅ `models/Cagnotte.php` :
  - Détection PostgreSQL pour `ORDER BY` avec `FIELD()` vs `CASE WHEN`
  - Adaptation `GROUP BY` pour PostgreSQL (plus strict)
  - Utilise `ILIKE` en PostgreSQL pour recherche insensible à la casse
  - Utilise `getLastInsertId()` pour compatibilité PostgreSQL

- ✅ `models/Don.php` :
  - Utilise `getLastInsertId()` pour compatibilité PostgreSQL

### 4. Schéma PostgreSQL
- ✅ `database/supabase_schema.sql` : Schéma complet compatible PostgreSQL
  - `SERIAL` au lieu de `AUTO_INCREMENT`
  - `BOOLEAN` au lieu de `TINYINT(1)`
  - `CHECK` constraints au lieu de `ENUM`
  - `TIMESTAMP WITH TIME ZONE` pour les dates
  - Trigger pour `date_modification`

## 📋 Étapes pour Activer Supabase

### 1. Créer un Projet Supabase
1. Allez sur [supabase.com](https://supabase.com)
2. Créez un compte et un nouveau projet
3. Notez vos credentials depuis "Settings" > "Database"

### 2. Configurer les Variables d'Environnement
Créez/modifiez le fichier `.env` à la racine du projet :

```env
# Activer Supabase
USE_SUPABASE=true

# Credentials Supabase
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_KEY=your-anon-key
SUPABASE_DB_HOST=db.your-project.supabase.co
SUPABASE_DB_NAME=postgres
SUPABASE_DB_USER=postgres
SUPABASE_DB_PASSWORD=your-database-password
SUPABASE_DB_PORT=5432

# MySQL (garder pour référence ou fallback)
DB_HOST=127.0.0.1
DB_NAME=liens_espoir
DB_USER=root
DB_PASS=
DB_PORT=3306
```

### 3. Exécuter le Schéma PostgreSQL
1. Connectez-vous à votre projet Supabase
2. Allez dans "SQL Editor"
3. Copiez et exécutez le contenu de `database/supabase_schema.sql`

### 4. Migrer les Données (Optionnel)
Si vous avez des données existantes dans MySQL :

**Option A : Utiliser pgloader (Recommandé)**
```bash
# Installer pgloader (Linux/Mac)
# Windows: Utiliser WSL ou Docker

pgloader mysql://user:pass@localhost/liens_espoir \
         postgresql://postgres:password@db.project.supabase.co:5432/postgres
```

**Option B : Export/Import CSV**
1. Exporter vos données MySQL en CSV
2. Importer via l'interface Supabase ou `psql`

**Option C : Script PHP de Migration**
Créez un script temporaire pour migrer les données via PDO.

### 5. Tester la Migration
1. Vérifiez que `USE_SUPABASE=true` dans `.env`
2. Testez l'application :
   - Connexion utilisateur
   - Création de cagnotte
   - Enregistrement de don
   - Affichage des listes
   - Recherche et filtres

## 🔄 Retour à MySQL
Pour revenir à MySQL, modifiez simplement `.env` :
```env
USE_SUPABASE=false
```

## ⚠️ Différences Importantes MySQL vs PostgreSQL

| MySQL | PostgreSQL |
|-------|------------|
| `AUTO_INCREMENT` | `SERIAL` ou `BIGSERIAL` |
| `TINYINT(1)` | `BOOLEAN` |
| `ENUM('val1','val2')` | `VARCHAR` avec `CHECK` constraint |
| `FIELD(col, 'a','b')` | `CASE col WHEN 'a' THEN 1 ... END` |
| `LIKE` (sensible casse) | `ILIKE` (insensible casse) |
| `GROUP BY` permissif | `GROUP BY` strict (toutes colonnes non-agrégées) |
| `DATABASE()` | `current_schema()` |
| `INFORMATION_SCHEMA` (majuscules) | `information_schema` (minuscules) |

## 🐛 Dépannage

### Erreur de Connexion
- Vérifiez les credentials dans `.env`
- Vérifiez que le firewall Supabase autorise votre IP
- Testez la connexion avec `psql` ou un client PostgreSQL

### Erreur "Column does not exist"
- Vérifiez que le schéma a été correctement exécuté
- Vérifiez les noms de colonnes (PostgreSQL est sensible à la casse pour les guillemets)

### Erreur "GROUP BY"
- PostgreSQL est plus strict : toutes les colonnes non-agrégées doivent être dans `GROUP BY`
- Vérifiez les requêtes dans `models/Cagnotte.php`

### Erreur "lastInsertId"
- PostgreSQL nécessite parfois le nom de la séquence
- La fonction `getLastInsertId()` gère cela automatiquement

## 📝 Notes
- Les séquences PostgreSQL commencent à 1, donc les IDs seront différents de MySQL
- Les dates avec `TIMESTAMP WITH TIME ZONE` incluent le fuseau horaire
- Les contraintes `CHECK` sont plus strictes que les `ENUM` MySQL

