/**
 * Database Restore Script
 * 
 * Cara penggunaan:
 * node restore.js                     # Akan tampilkan daftar backup dan pilih
 * node restore.js backup_file.sql     # Langsung restore file tertentu
 * 
 * Script ini akan:
 * 1. Membaca konfigurasi database dari file .env
 * 2. Menampilkan daftar file backup yang tersedia
 * 3. Menjalankan restore dari file backup yang dipilih
 */

import { execSync } from 'child_process';
import { existsSync, readdirSync, readFileSync, statSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import { createInterface } from 'readline';

// Get current directory (ES Module compatible)
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

// Parse .env file
function parseEnvFile(envPath) {
    const envContent = readFileSync(envPath, 'utf-8');
    const envVars = {};

    envContent.split('\n').forEach(line => {
        if (line.startsWith('#') || !line.trim()) return;

        const [key, ...valueParts] = line.split('=');
        if (key) {
            let value = valueParts.join('=').trim();
            value = value.replace(/^["']|["']$/g, '');
            envVars[key.trim()] = value;
        }
    });

    return envVars;
}

// Format file size
function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

// Get list of backup files
function getBackupFiles(backupsDir) {
    if (!existsSync(backupsDir)) {
        return [];
    }

    const files = readdirSync(backupsDir)
        .filter(f => f.endsWith('.sql'))
        .map(f => {
            const filePath = join(backupsDir, f);
            const stats = statSync(filePath);
            return {
                name: f,
                path: filePath,
                size: stats.size,
                mtime: stats.mtime,
            };
        })
        .sort((a, b) => b.mtime - a.mtime); // Sort by newest first

    return files;
}

// Prompt for user input
function prompt(question) {
    const rl = createInterface({
        input: process.stdin,
        output: process.stdout,
    });

    return new Promise(resolve => {
        rl.question(question, answer => {
            rl.close();
            resolve(answer.trim());
        });
    });
}

// Main restore function
async function restore() {
    console.log('🔄 Database Restore Tool\n');

    // Load environment variables
    const envPath = join(__dirname, '.env');
    if (!existsSync(envPath)) {
        console.error('❌ Error: File .env tidak ditemukan!');
        process.exit(1);
    }

    const env = parseEnvFile(envPath);

    const dbHost = env.DB_HOST || '127.0.0.1';
    const dbPort = env.DB_PORT || '3306';
    const dbName = env.DB_DATABASE;
    const dbUser = env.DB_USERNAME || 'root';
    const dbPass = env.DB_PASSWORD || '';

    if (!dbName) {
        console.error('❌ Error: DB_DATABASE tidak ditemukan di .env!');
        process.exit(1);
    }

    const backupsDir = join(__dirname, 'backups');
    let backupFile;

    // Check if filename provided as argument
    if (process.argv[2]) {
        const argFile = process.argv[2];
        const fullPath = argFile.includes('/') || argFile.includes('\\')
            ? argFile
            : join(backupsDir, argFile);

        if (!existsSync(fullPath)) {
            console.error(`❌ Error: File tidak ditemukan: ${fullPath}`);
            process.exit(1);
        }
        backupFile = fullPath;
    } else {
        // Show list and prompt for selection
        const files = getBackupFiles(backupsDir);

        if (files.length === 0) {
            console.error('❌ Tidak ada file backup di folder backups/');
            console.log('💡 Jalankan "node backup.js" untuk membuat backup terlebih dahulu.');
            process.exit(1);
        }

        console.log('📋 Daftar file backup yang tersedia:\n');
        files.forEach((f, i) => {
            const date = f.mtime.toLocaleString('id-ID');
            console.log(`   ${i + 1}. ${f.name}`);
            console.log(`      📦 Ukuran: ${formatSize(f.size)} | 📅 ${date}\n`);
        });

        const answer = await prompt('Pilih nomor file untuk restore (atau ketik "cancel" untuk batal): ');

        if (answer.toLowerCase() === 'cancel' || answer === '') {
            console.log('\n❌ Restore dibatalkan.');
            process.exit(0);
        }

        const index = parseInt(answer) - 1;
        if (isNaN(index) || index < 0 || index >= files.length) {
            console.error('\n❌ Pilihan tidak valid.');
            process.exit(1);
        }

        backupFile = files[index].path;
    }

    console.log(`\n📂 File yang dipilih: ${backupFile}`);
    console.log(`📋 Database target: ${dbName}`);

    // Confirmation
    console.log('\n⚠️  PERINGATAN: Restore akan MENIMPA semua data di database!');
    const confirm = await prompt('Ketik "YES" untuk melanjutkan: ');

    if (confirm !== 'YES') {
        console.log('\n❌ Restore dibatalkan.');
        process.exit(0);
    }

    // Build mysql command
    let mysqlCmd = `mysql`;
    mysqlCmd += ` -h ${dbHost}`;
    mysqlCmd += ` -P ${dbPort}`;
    mysqlCmd += ` -u ${dbUser}`;
    if (dbPass) {
        mysqlCmd += ` -p"${dbPass}"`;
    }
    mysqlCmd += ` ${dbName}`;
    mysqlCmd += ` < "${backupFile}"`;

    console.log('\n🔄 Menjalankan restore...');

    try {
        execSync(mysqlCmd, {
            stdio: 'pipe',
            shell: true
        });

        console.log('\n✅ Restore berhasil!');
        console.log(`📍 Database "${dbName}" telah di-restore dari backup.`);
        console.log('\n💡 Jangan lupa jalankan:');
        console.log('   php artisan config:cache');
        console.log('   php artisan route:cache');
        console.log('   pm2 restart all (jika di server)');

    } catch (error) {
        console.error('\n❌ Error saat restore:');
        console.error(error.message);

        console.log('\n💡 Troubleshooting:');
        console.log('   - Pastikan MySQL/MariaDB sudah berjalan');
        console.log('   - Pastikan kredensial database benar');
        console.log('   - Pastikan file backup tidak corrupt');

        process.exit(1);
    }
}

// Run restore
restore();
