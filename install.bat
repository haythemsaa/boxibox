@echo off
echo ========================================
echo Installation de Boxibox
echo ========================================
echo.

echo Étape 1: Vérification des prérequis...
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERREUR: PHP n'est pas installé ou non configuré dans le PATH
    pause
    exit /b 1
)

composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERREUR: Composer n'est pas installé ou non configuré dans le PATH
    pause
    exit /b 1
)

echo ✓ PHP et Composer détectés
echo.

echo Étape 2: Installation des dépendances...
call composer install --no-dev --optimize-autoloader
if %errorlevel% neq 0 (
    echo ERREUR: Échec de l'installation des dépendances
    pause
    exit /b 1
)
echo ✓ Dépendances installées
echo.

echo Étape 3: Configuration de l'environnement...
if not exist .env (
    copy .env.example .env
    echo ✓ Fichier .env créé
) else (
    echo ! Fichier .env existe déjà
)

echo Génération de la clé d'application...
php artisan key:generate --no-interaction
echo ✓ Clé d'application générée
echo.

echo Étape 4: Configuration de la base de données...
set /p db_name=Nom de la base de données (défaut: boxibox):
if "%db_name%"=="" set db_name=boxibox

set /p db_user=Utilisateur MySQL (défaut: root):
if "%db_user%"=="" set db_user=root

set /p db_pass=Mot de passe MySQL:

echo Mise à jour du fichier .env...
powershell -Command "(Get-Content .env) -replace 'DB_DATABASE=.*', 'DB_DATABASE=%db_name%' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'DB_USERNAME=.*', 'DB_USERNAME=%db_user%' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=%db_pass%' | Set-Content .env"

echo ✓ Configuration de la base de données mise à jour
echo.

echo Étape 5: Création et migration de la base de données...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo ERREUR: Échec des migrations de base de données
    echo Vérifiez vos paramètres de connexion à la base de données
    pause
    exit /b 1
)
echo ✓ Base de données migrée
echo.

echo Étape 6: Installation des données initiales...
php artisan db:seed --class=DatabaseSeeder --force
if %errorlevel% neq 0 (
    echo ERREUR: Échec de l'initialisation des données
    pause
    exit /b 1
)
echo ✓ Données initiales installées
echo.

echo Étape 7: Configuration des permissions...
if not exist "storage\logs" mkdir "storage\logs"
if not exist "storage\app\documents" mkdir "storage\app\documents"
if not exist "storage\app\sepa" mkdir "storage\app\sepa"
if not exist "bootstrap\cache" mkdir "bootstrap\cache"
echo ✓ Dossiers créés
echo.

echo Étape 8: Optimisation...
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo ✓ Optimisation effectuée
echo.

echo ========================================
echo Installation terminée avec succès !
echo ========================================
echo.
echo Compte administrateur par défaut :
echo Email : admin@boxibox.com
echo Mot de passe : admin123
echo.
echo IMPORTANT: Changez ce mot de passe après votre première connexion !
echo.
echo Accédez à votre application à l'adresse :
echo http://localhost/boxibox/public (selon votre configuration)
echo.
pause