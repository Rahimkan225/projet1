# Configuration du fichier .env

## ✅ Fichier créé

Le fichier `.env` a été créé à partir de `env.example.txt`. 

## 📝 Configuration actuelle

Par défaut, le fichier `.env` est configuré pour utiliser **MySQL** (XAMPP).

### Configuration MySQL (par défaut)
```env
USE_SUPABASE=false
DB_HOST=127.0.0.1
DB_NAME=liens_espoir
DB_USER=root
DB_PASS=
DB_PORT=3306
```

## 🔄 Pour activer Supabase

1. **Ouvrez le fichier `.env`** à la racine du projet

2. **Modifiez les valeurs Supabase** :
```env
USE_SUPABASE=true

SUPABASE_URL=https://your-project.supabase.co
SUPABASE_KEY=your-anon-key
SUPABASE_DB_HOST=db.your-project.supabase.co
SUPABASE_DB_NAME=postgres
SUPABASE_DB_USER=postgres
SUPABASE_DB_PASSWORD=your-database-password
SUPABASE_DB_PORT=5432
```

3. **Où trouver ces valeurs ?**
   - Connectez-vous à [Supabase](https://app.supabase.com)
   - Allez dans votre projet
   - **Settings > Database** : Host, User, Password, Port
   - **Settings > API** : URL et Key (anon/public)

4. **Sauvegardez le fichier**

5. **Testez** : L'application utilisera automatiquement Supabase

## ⚠️ Important

- **Ne commitez JAMAIS** le fichier `.env` dans Git
- Le fichier contient des informations sensibles (mots de passe)
- Utilisez `env.example.txt` comme modèle pour les autres développeurs

## 🔙 Retour à MySQL

Pour revenir à MySQL, modifiez simplement :
```env
USE_SUPABASE=false
```

## 📋 Checklist

- [ ] Fichier `.env` créé
- [ ] Variables MySQL configurées (par défaut)
- [ ] (Optionnel) Variables Supabase configurées
- [ ] (Optionnel) `USE_SUPABASE=true` si vous utilisez Supabase
- [ ] `.env` ajouté à `.gitignore` (recommandé)

