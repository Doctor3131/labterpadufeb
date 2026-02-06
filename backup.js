/**
 * Database Backup Script
 * 
 * Cara penggunaan:
 * node backup.js
 * 
 * Script ini akan:
 * 1. Membaca konfigurasi database dari file .env
 * 2. Menjalankan mysqldump untuk export database
 * 3. Menyimpan hasil ke folder backups/ dengan timestamp
 * 4. Memverifikasi hasil backup
 */

import { execSync } from 'child_process';
import { existsSync, mkdirSync, readFileSync, statSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

// Get current directory (ES Module compatible)
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

// Parse .env file
function parseEnvFile(envPath) {
    const envContent = readFileSync(envPath, 'utf-8');
    const envVars = {};

    envContent.split('\n').forEach(line => {
        // Skip comments and empty lines
        if (line.startsWith('#') || !line.trim()) return;

        const [key, ...valueParts] = line.split('=');
        if (key) {
            let value = valueParts.join('=').trim();
            // Remove quotes if present
            value = value.replace(/^["']|["']$/g, '');
            envVars[key.trim()] = value;
        }
    });

    return envVars;
}

// Format date for filename
function getFormattedDate() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day}_${hours}-${minutes}-${seconds}`;
}

// Main backup function
function backup() {
    console.log('🔄 Memulai proses backup database...\n');

    // Load environment variables
    const envPath = join(__dirname, '.env');
    if (!existsSync(envPath)) {
        console.error('❌ Error: File .env tidak ditemukan!');
        process.exit(1);
    }

    const env = parseEnvFile(envPath);

    // Get database configuration
    const dbHost = env.DB_HOST || '127.0.0.1';
    const dbPort = env.DB_PORT || '3306';
    const dbName = env.DB_DATABASE;
    const dbUser = env.DB_USERNAME || 'root';
    const dbPass = env.DB_PASSWORD || '';

    if (!dbName) {
        console.error('❌ Error: DB_DATABASE tidak ditemukan di .env!');
        process.exit(1);
    }

    console.log(`📋 Konfigurasi Database:`);
    console.log(`   Host     : ${dbHost}`);
    console.log(`   Port     : ${dbPort}`);
    console.log(`   Database : ${dbName}`);
    console.log(`   Username : ${dbUser}`);
    console.log(`   Password : ${dbPass ? '********' : '(kosong)'}\n`);

    // Create backups directory if not exists
    const backupsDir = join(__dirname, 'backups');
    if (!existsSync(backupsDir)) {
        mkdirSync(backupsDir, { recursive: true });
        console.log('📁 Folder backups/ dibuat.\n');
    }

    // Generate backup filename with timestamp
    const timestamp = getFormattedDate();
    const backupFileName = `backup_${dbName}_${timestamp}.sql`;
    const backupPath = join(backupsDir, backupFileName);

    // Build mysqldump command
    let mysqldumpCmd = `mysqldump`;
    mysqldumpCmd += ` -h ${dbHost}`;
    mysqldumpCmd += ` -P ${dbPort}`;
    mysqldumpCmd += ` -u ${dbUser}`;
    if (dbPass) {
        mysqldumpCmd += ` -p"${dbPass}"`;
    }
    mysqldumpCmd += ` ${dbName}`;
    mysqldumpCmd += ` > "${backupPath}"`;

    console.log(`📦 Menjalankan backup ke: ${backupFileName}`);

    try {
        execSync(mysqldumpCmd, {
            stdio: 'pipe',
            shell: true
        });

        // Verify backup file
        const stats = statSync(backupPath);
        const fileSizeKB = (stats.size / 1024).toFixed(2);
        const fileSizeMB = (stats.size / (1024 * 1024)).toFixed(2);

        if (stats.size === 0) {
            console.error(`\n❌ Error: File backup kosong!`);
            process.exit(1);
        }

        // Read file to count tables and verify content
        const backupContent = readFileSync(backupPath, 'utf-8');
        const tableMatches = backupContent.match(/CREATE TABLE/gi) || [];
        const insertMatches = backupContent.match(/INSERT INTO/gi) || [];

        // Check for valid MySQL dump header
        const isValidDump = backupContent.includes('MySQL dump') ||
            backupContent.includes('MariaDB dump') ||
            backupContent.includes('CREATE TABLE');

        console.log(`\n✅ Backup berhasil!`);
        console.log(`\n📊 Statistik Backup:`);
        console.log(`   📍 File     : ${backupFileName}`);
        console.log(`   📦 Ukuran   : ${fileSizeKB} KB (${fileSizeMB} MB)`);
        console.log(`   📋 Tabel    : ${tableMatches.length} tabel ditemukan`);
        console.log(`   📝 Insert   : ${insertMatches.length} statement INSERT`);
        console.log(`   ✓ Valid     : ${isValidDump ? 'Ya' : 'Perlu dicek manual'}`);
        console.log(`\n📍 Lokasi file: ${backupPath}`);

    } catch (error) {
        console.error(`\n❌ Error saat backup:`);
        console.error(error.message);

        // Provide helpful troubleshooting
        console.log(`\n💡 Troubleshooting:`);
        console.log(`   - Pastikan MySQL/MariaDB sudah berjalan`);
        console.log(`   - Pastikan mysqldump tersedia di PATH`);
        console.log(`   - Cek konfigurasi database di .env`);

        process.exit(1);
    }
}

// Run backup
backup();
