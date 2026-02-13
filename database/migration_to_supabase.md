# Guide de Migration vers Supabase

## Étapes de Migration

### 1. Créer un projet Supabase

1. Allez sur [supabase.com](https://supabase.com)
2. Créez un compte et un nouveau projet
3. Notez vos credentials :
   - Project URL
   - Anon/Public Key
   - Database Password
   - Database Host

### 2. Configurer les variables d'environnement

Créez un fichier `.env` à la racine du projet :

```env
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_KEY=your-anon-key
SUPABASE_DB_HOST=db.your-project.supabase.co
SUPABASE_DB_NAME=postgres
SUPABASE_DB_USER=postgres
SUPABASE_DB_PASSWORD=your-database-password
SUPABASE_DB_PORT=5432
```

### 3. Exécuter le schéma PostgreSQL

1. Connectez-vous à votre projet Supabase
2. Allez dans SQL Editor
3. Exécutez le contenu de `database/supabase_schema.sql`

### 4. Migrer les données

Utilisez un outil de migration comme `pgloader` ou exportez/importez via CSV :

```bash
# Exemple avec pgloader (nécessite installation)
pgloader mysql://user:pass@localhost/liens_espoir \
         postgresql://postgres:pass@db.project.supabase.co:5432/postgres
```

### 5. Modifier la configuration

Dans `config/database.php`, remplacez la connexion MySQL par :

```php
require_once __DIR__ . '/supabase.php';
$pdo = getSupabaseConnection();
```

### 6. Différences importantes MySQL → PostgreSQL

- `AUTO_INCREMENT` → `SERIAL` ou `BIGSERIAL`
- `TINYINT(1)` → `BOOLEAN`
- `ENUM` → `VARCHAR` avec `CHECK` constraint
- `LIMIT ? OFFSET ?` → Même syntaxe mais attention aux paramètres
- `FIELD()` → Utiliser `CASE WHEN` ou `ORDER BY` avec `CASE`

### 7. Adapter les requêtes SQL

Certaines requêtes MySQL doivent être adaptées :

**MySQL:**
```sql
ORDER BY FIELD(c.urgence, 'critique', 'elevee', 'moderee')
```

**PostgreSQL:**
```sql
ORDER BY CASE c.urgence
    WHEN 'critique' THEN 1
    WHEN 'elevee' THEN 2
    WHEN 'moderee' THEN 3
END
```

### 8. Tester la migration

1. Testez toutes les fonctionnalités
2. Vérifiez les performances
3. Testez les requêtes complexes

## Avantages de Supabase

- ✅ Base de données PostgreSQL scalable
- ✅ API REST automatique
- ✅ Authentification intégrée
- ✅ Stockage de fichiers
- ✅ Real-time subscriptions
- ✅ Dashboard de gestion
- ✅ Backups automatiques

## Support

Pour plus d'aide, consultez la [documentation Supabase](https://supabase.com/docs)

