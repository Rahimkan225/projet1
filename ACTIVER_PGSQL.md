# Activer l'extension PostgreSQL dans XAMPP

## ❌ Erreur
```
Erreur connexion Supabase : could not find driver
```

Cette erreur signifie que l'extension PDO PostgreSQL n'est pas activée dans PHP.

## ✅ Solution

### Étape 1 : Ouvrir php.ini

1. Localisez le fichier `php.ini` :
   - Chemin : `C:\xampp\php\php.ini`
   - Ou utilisez : `C:\xampp\php\php.exe --ini` pour trouver le chemin exact

2. **Ouvrez `php.ini` avec un éditeur de texte** (en tant qu'administrateur)

### Étape 2 : Activer les extensions PostgreSQL

**Option A : Script automatique (Recommandé)**

1. Ouvrez PowerShell en tant qu'**administrateur**
2. Naviguez vers le projet :
   ```powershell
   cd C:\xampp\htdocs\projetPHP
   ```
3. Exécutez le script :
   ```powershell
   .\activer_pgsql.ps1
   ```

**Option B : Modification manuelle**

1. Recherchez ces lignes dans `php.ini` (utilisez Ctrl+F) :
   ```ini
   ;extension=pdo_pgsql
   ;extension=pgsql
   ```

2. **Décommentez-les** (enlevez le point-virgule `;` au début) :
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```

3. **Sauvegardez** le fichier

### Étape 3 : Vérifier que les fichiers DLL existent

Les fichiers doivent exister dans `C:\xampp\php\ext\` :
- `php_pdo_pgsql.dll`
- `php_pgsql.dll`

Si ces fichiers n'existent pas, vous devrez :
1. Télécharger une version de XAMPP qui inclut PostgreSQL
2. Ou télécharger les DLL depuis [PECL](https://pecl.php.net/package/pdo_pgsql)

### Étape 4 : Redémarrer Apache

1. Ouvrez le **panneau de contrôle XAMPP**
2. **Arrêtez Apache** (Stop)
3. **Redémarrez Apache** (Start)

### Étape 5 : Vérifier l'activation

Exécutez cette commande dans PowerShell :

```powershell
C:\xampp\php\php.exe -m | Select-String -Pattern "pdo_pgsql|pgsql"
```

Vous devriez voir :
```
pdo_pgsql
pgsql
```

## 🔄 Alternative : Utiliser l'API REST Supabase

Si vous ne pouvez pas activer l'extension PostgreSQL, vous pouvez utiliser l'API REST de Supabase au lieu de PDO. 

Le fichier `config/supabase.php` contient déjà la fonction `supabaseRequest()` pour cela.

## 📝 Notes

- **XAMPP par défaut** : Certaines versions de XAMPP n'incluent pas les extensions PostgreSQL
- **Solution alternative** : Utiliser l'API REST Supabase (déjà implémentée dans le code)
- **Production** : Assurez-vous que l'extension est activée sur votre serveur de production

## ⚠️ Si les DLL n'existent pas

1. **Télécharger XAMPP avec PostgreSQL** :
   - Certaines versions de XAMPP incluent PostgreSQL
   - Ou utilisez une version complète de PHP avec toutes les extensions

2. **Télécharger manuellement les DLL** :
   - Visitez [PECL Windows](https://windows.php.net/downloads/pecl/releases/)
   - Téléchargez les DLL correspondant à votre version de PHP
   - Placez-les dans `C:\xampp\php\ext\`

3. **Vérifier la version PHP** :
   ```powershell
   C:\xampp\php\php.exe -v
   ```
   - Assurez-vous que les DLL correspondent à la même version (Thread Safe ou Non-Thread Safe)

