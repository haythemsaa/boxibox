#!/bin/bash

# 🚀 Script de Pré-Production Boxibox
# Version: 2.0.0
# Date: 06 Octobre 2025

echo "════════════════════════════════════════════════════════════"
echo "🚀  Script de Pré-Production Boxibox v2.0.0"
echo "════════════════════════════════════════════════════════════"
echo ""

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Fonction pour afficher les étapes
step() {
    echo -e "${BLUE}▶ $1${NC}"
}

success() {
    echo -e "${GREEN}✔ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

error() {
    echo -e "${RED}✖ $1${NC}"
    exit 1
}

# 1. Vérifier les prérequis
step "1. Vérification des prérequis..."

if ! command -v php &> /dev/null; then
    error "PHP n'est pas installé"
fi
success "PHP: $(php -v | head -n 1)"

if ! command -v composer &> /dev/null; then
    error "Composer n'est pas installé"
fi
success "Composer: $(composer -V)"

if ! command -v npm &> /dev/null; then
    error "NPM n'est pas installé"
fi
success "NPM: $(npm -v)"

if ! command -v git &> /dev/null; then
    error "Git n'est pas installé"
fi
success "Git: $(git --version)"

echo ""

# 2. Vérifier l'environnement
step "2. Vérification de l'environnement..."

if [ ! -f .env ]; then
    error "Fichier .env manquant"
fi
success "Fichier .env trouvé"

# Vérifier si .env contient APP_ENV=production
if grep -q "APP_ENV=production" .env; then
    warning "APP_ENV est en mode PRODUCTION"
else
    echo "APP_ENV n'est pas en production (OK pour pré-prod)"
fi

echo ""

# 3. Installer les dépendances
step "3. Installation des dépendances..."

echo "Installation des dépendances Composer..."
composer install --no-dev --optimize-autoloader || error "Échec installation Composer"
success "Dépendances Composer installées"

echo "Installation des dépendances NPM..."
npm ci || npm install || error "Échec installation NPM"
success "Dépendances NPM installées"

echo ""

# 4. Build des assets
step "4. Build des assets de production..."

npm run build || error "Échec du build"
success "Assets buildés avec succès"

echo ""

# 5. Optimisations Laravel
step "5. Optimisations Laravel..."

echo "Cache de configuration..."
php artisan config:cache || warning "Échec cache config"

echo "Cache des routes..."
php artisan route:cache || warning "Échec cache routes"

echo "Cache des vues..."
php artisan view:cache || warning "Échec cache vues"

echo "Optimisation de l'autoloader..."
composer dump-autoload -o || warning "Échec optimisation autoloader"

success "Optimisations Laravel complétées"

echo ""

# 6. Vérifications de sécurité
step "6. Vérifications de sécurité..."

# Vérifier APP_KEY
if grep -q "APP_KEY=base64:" .env; then
    success "APP_KEY est configurée"
else
    warning "APP_KEY non configurée - Exécutez: php artisan key:generate"
fi

# Vérifier APP_DEBUG
if grep -q "APP_DEBUG=false" .env; then
    success "APP_DEBUG est à false"
else
    warning "APP_DEBUG devrait être à false en production"
fi

# Vérifier les permissions
if [ -w storage ]; then
    success "Permissions storage OK"
else
    warning "Permissions storage à vérifier"
fi

if [ -w bootstrap/cache ]; then
    success "Permissions bootstrap/cache OK"
else
    warning "Permissions bootstrap/cache à vérifier"
fi

echo ""

# 7. Tests de base
step "7. Tests de base..."

echo "Vérification de la connexion base de données..."
php artisan db:show || warning "Impossible de se connecter à la base de données"

echo "Vérification des migrations..."
php artisan migrate:status || warning "Problème avec les migrations"

success "Tests de base complétés"

echo ""

# 8. Vérification des fichiers critiques
step "8. Vérification des fichiers critiques..."

FILES=(
    "VERSION.md"
    "GUIDE_TEST_COMPLET.md"
    "AMELIORATIONS_SESSION_06_10_2025.md"
    "README.md"
    "package.json"
    "composer.json"
    "vite.config.js"
    "public/build/manifest.json"
)

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        success "$file ✓"
    else
        warning "$file manquant"
    fi
done

echo ""

# 9. Informations sur le build
step "9. Informations sur le build..."

echo "Taille du dossier public/build:"
du -sh public/build 2>/dev/null || echo "N/A"

echo ""
echo "Fichiers buildés:"
ls -lh public/build/assets/*.js 2>/dev/null | head -5

echo ""

# 10. Checklist finale
step "10. Checklist finale..."

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║         CHECKLIST PRÉ-PRODUCTION                            ║"
echo "╠════════════════════════════════════════════════════════════╣"
echo "║  ✅ Dépendances installées                                  ║"
echo "║  ✅ Assets buildés                                          ║"
echo "║  ✅ Optimisations Laravel appliquées                        ║"
echo "║  ⏭️  Tests manuels à effectuer                              ║"
echo "║  ⏭️  Backup base de données                                 ║"
echo "║  ⏭️  Configuration .env production                          ║"
echo "║  ⏭️  SSL/HTTPS configuré                                    ║"
echo "║  ⏭️  Domaine configuré                                      ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

success "Script de pré-production terminé avec succès!"
echo ""
echo "📋 Prochaines étapes:"
echo "  1. Effectuer les tests manuels (voir GUIDE_TEST_COMPLET.md)"
echo "  2. Faire un backup de la base de données"
echo "  3. Configurer .env pour production"
echo "  4. Configurer SSL/HTTPS"
echo "  5. Déployer sur le serveur de production"
echo ""
echo "🚀 Boxibox v2.0.0 est prêt pour la production!"
echo ""
