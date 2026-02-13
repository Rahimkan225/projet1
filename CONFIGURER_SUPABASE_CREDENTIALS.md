# Configurer les Credentials Supabase

## ✅ Progrès
L'extension PostgreSQL est maintenant activée ! Le problème actuel est lié aux credentials dans le fichier `.env`.

## 📋 Étapes pour obtenir les bonnes credentials

### 1. Connectez-vous à Supabase
- Allez sur [https://app.supabase.com](https://app.supabase.com)
- Connectez-vous à votre compte
- Sélectionnez votre projet (ou créez-en un nouveau)

### 2. Obtenir les credentials de la base de données

#### Étape A : Database Password
1. Allez dans **Settings** (⚙️) > **Database**
2. Dans la section **Database Password**, vous verrez :
   - Si vous avez déjà défini un mot de passe : il est affiché (ou vous pouvez le réinitialiser)
   - Si c'est un nouveau projet : le mot de passe initial est affiché lors de la création

**⚠️ Important** : Si vous avez oublié le mot de passe, vous pouvez le réinitialiser dans cette section.

#### Étape B : Connection String
1. Toujours dans **Settings** > **Database**
2. Cherchez la section **Connection string** ou **Connection pooling**
3. Vous verrez quelque chose comme :
   ```
   postgresql://postgres:[YOUR-PASSWORD]@db.xxxxx.supabase.co:5432/postgres
   ```

   Ou séparément :
   - **Host** : `db.xxxxx.supabase.co`
   - **Database name** : `postgres`
   - **Port** : `5432`
   - **User** : `postgres`
   - **Password** : (celui que vous avez défini)

### 3. Obtenir l'URL et la clé API (optionnel, pour REST API)

1. Allez dans **Settings** > **API**
2. Vous trouverez :
   - **Project URL** : `https://xxxxx.supabase.co`
   - **anon/public key** : Une longue chaîne de caractères

### 4. Mettre à jour le fichier `.env`

Ouvrez le fichier `.env` à la racine du projet et modifiez ces lignes :

```env
USE_SUPABASE=true

# Remplacez par vos vraies valeurs
SUPABASE_DB_HOST=db.xxxxx.supabase.co
SUPABASE_DB_NAME=postgres
SUPABASE_DB_USER=postgres
SUPABASE_DB_PASSWORD=votre-mot-de-passe-ici
SUPABASE_DB_PORT=5432

# Optionnel (pour REST API)
SUPABASE_URL=https://xxxxx.supabase.co
SUPABASE_KEY=votre-anon-key-ici
```

**⚠️ Important** :
- Le mot de passe ne doit **PAS** contenir d'espaces
- Si le mot de passe contient des caractères spéciaux, vous pouvez le mettre entre guillemets dans certains cas
- Vérifiez qu'il n'y a pas d'espaces avant/après les valeurs

### 5. Tester la connexion

Utilisez le script de test fourni :
```powershell
.\test_supabase_connection.php
```

Ou testez directement dans votre application.

## 🔍 Vérification rapide

Votre `.env` devrait ressembler à ceci (avec vos vraies valeurs) :

```env
USE_SUPABASE=true
SUPABASE_DB_HOST=db.abcdefghijklmnop.supabase.co
SUPABASE_DB_NAME=postgres
SUPABASE_DB_USER=postgres
SUPABASE_DB_PASSWORD=MonMotDePasse123!
SUPABASE_DB_PORT=5432
```

## ⚠️ Problèmes courants

### "password authentication failed"
- ✅ Vérifiez que le mot de passe est correct (copiez-collez depuis Supabase)
- ✅ Vérifiez qu'il n'y a pas d'espaces avant/après
- ✅ Vérifiez que vous utilisez le bon projet Supabase
- ✅ Essayez de réinitialiser le mot de passe dans Supabase

### "could not connect to server"
- ✅ Vérifiez que `SUPABASE_DB_HOST` est correct (commence par `db.` et se termine par `.supabase.co`)
- ✅ Vérifiez que le port est `5432`
- ✅ Vérifiez votre connexion Internet
- ✅ Vérifiez que le projet Supabase est actif (pas en pause)

### "database does not exist"
- ✅ Le nom de la base de données est généralement `postgres` (par défaut)
- ✅ Vérifiez dans Supabase Settings > Database

## 📝 Exemple de credentials valides

```
Host: db.abcdefghijklmnop.supabase.co
Port: 5432
Database: postgres
User: postgres
Password: VotreMotDePasse123!
```

## 🔐 Sécurité

- ⚠️ **Ne partagez JAMAIS** votre fichier `.env`
- ⚠️ **Ne commitez JAMAIS** `.env` dans Git
- ✅ Utilisez `.env.example` pour partager la structure sans les valeurs





