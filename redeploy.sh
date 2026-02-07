#!/bin/bash

# Pastikan script berhenti jika ada command yang error
set -e

echo "🚀 Starting deployment..."

# 1. Pull changes terbaru dari git
echo "📥 Pulling latest changes..."
git pull origin main

# 2. Install dependencies PHP (jika ada perubahan di composer.json)
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# 3. Install dependencies Node modules (jika ada perubahan di package.json)
echo "📦 Installing Node dependencies..."
npm install

# 4. Build frontend assets
echo "🏗️ Building frontend assets..."
npm run build

# 5. Jalankan migrasi database
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 6. Jalankan seeder khusus BPS (sesuai request)
echo "🌱 Seeding BPS data..."
php artisan db:seed --class=BpsDataSeeder --force

# 7. Cache configuration, routes, and views
echo "🧹 Optimizing cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Restart PM2 process
echo "🔄 Restarting PM2 processes..."
pm2 restart all

echo "✅ Deployment finished successfully!"
