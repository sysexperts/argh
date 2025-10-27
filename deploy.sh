#!/bin/bash
# Business Manager - Deployment Script
# Verwendung: ./deploy.sh [production|staging]

set -e  # Exit bei Fehler

ENVIRONMENT=${1:-production}
echo "🚀 Deploying Business Manager to $ENVIRONMENT..."

# 1. Abhängigkeiten installieren
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# 2. Prüfe ob .env existiert
if [ ! -f .env ]; then
    echo "⚠️  .env file not found!"
    echo "Creating from .env.example..."
    cp .env.example .env
    echo "⚠️  Please configure .env file and run this script again!"
    exit 1
fi

# 3. Dateiberechtigungen setzen
echo "🔐 Setting file permissions..."
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod -R 775 database/
chmod 600 .env

# 4. Datenbank-Migrationen (falls vorhanden)
if [ -d "database/migrations" ]; then
    echo "🗄️  Running database migrations..."
    # php database/migrate.php  # Wenn migrate.php existiert
fi

# 5. Cache leeren (falls vorhanden)
if [ -d "storage/cache" ]; then
    echo "🧹 Clearing cache..."
    rm -rf storage/cache/*
fi

# 6. Sicherheits-Check
echo "🔒 Security check..."
if [ -f "public/install.php" ]; then
    echo "⚠️  WARNING: install.php still exists! Remove it for security!"
fi

if [ -d ".git" ]; then
    echo "⚠️  WARNING: .git directory exists in production! Consider removing it."
fi

# 7. Fertig
echo "✅ Deployment complete!"
echo ""
echo "Next steps:"
echo "1. Configure your web server to point to /public"
echo "2. Visit your domain to complete installation"
echo "3. Remove public/install.php after installation"
echo ""
echo "🎉 Happy deploying!"
