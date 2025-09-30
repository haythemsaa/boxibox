#!/bin/bash

echo "========================================"
echo "Installation de Boxibox"
echo "========================================"
echo

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction pour afficher les erreurs
error() {
    echo -e "${RED}ERREUR: $1${NC}"
    exit 1
}

# Fonction pour afficher le succès
success() {
    echo -e "${GREEN}✓ $1${NC}"
}

# Fonction pour afficher les avertissements
warning() {
    echo -e "${YELLOW}! $1${NC}"
}

echo "Étape 1: Vérification des prérequis..."

# Vérification de PHP
if ! command -v php &> /dev/null; then
    error "PHP n'est pas installé ou non configuré dans le PATH"
fi

# Vérification de Composer
if ! command -v composer &> /dev/null; then
    error "Composer n'est pas installé ou non configuré dans le PATH"
fi

success "PHP et Composer détectés"
echo

echo "Étape 2: Installation des dépendances..."
composer install --no-dev --optimize-autoloader || error "Échec de l'installation des dépendances"
success "Dépendances installées"
echo

echo "Étape 3: Configuration de l'environnement..."
if [ ! -f .env ]; then
    cp .env.example .env
    success "Fichier .env créé"
else
    warning "Fichier .env existe déjà"
fi

echo "Génération de la clé d'application..."
php artisan key:generate --no-interaction
success "Clé d'application générée"
echo

echo "Étape 4: Configuration de la base de données..."
read -p "Nom de la base de données (défaut: boxibox): " db_name
db_name=${db_name:-boxibox}

read -p "Utilisateur MySQL (défaut: root): " db_user
db_user=${db_user:-root}

read -s -p "Mot de passe MySQL: " db_pass
echo

echo "Mise à jour du fichier .env..."
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$db_name/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$db_user/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$db_pass/" .env

success "Configuration de la base de données mise à jour"
echo

echo "Étape 5: Création et migration de la base de données..."
php artisan migrate --force || error "Échec des migrations de base de données. Vérifiez vos paramètres de connexion."
success "Base de données migrée"
echo

echo "Étape 6: Installation des données initiales..."
php artisan db:seed --class=DatabaseSeeder --force || error "Échec de l'initialisation des données"
success "Données initiales installées"
echo

echo "Étape 7: Configuration des permissions..."
mkdir -p storage/logs
mkdir -p storage/app/documents
mkdir -p storage/app/sepa
mkdir -p bootstrap/cache

# Définition des permissions appropriées
chmod -R 755 storage bootstrap/cache
success "Permissions configurées"
echo

echo "Étape 8: Optimisation..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
success "Optimisation effectuée"
echo

echo "========================================"
echo "Installation terminée avec succès !"
echo "========================================"
echo
echo "Compte administrateur par défaut :"
echo "Email : admin@boxibox.com"
echo "Mot de passe : admin123"
echo
echo -e "${RED}IMPORTANT: Changez ce mot de passe après votre première connexion !${NC}"
echo
echo "Accédez à votre application à l'adresse :"
echo "http://localhost/boxibox/public (selon votre configuration)"
echo

# Rendre le script exécutable
chmod +x install.sh