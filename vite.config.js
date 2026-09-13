import { defineConfig, loadEnv } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
// [DITAMBAHKAN] Import untuk auto-detect IP address
import { networkInterfaces } from "os";

// [DITAMBAHKAN] Fungsi untuk mendapatkan IP address otomatis
// Fungsi ini akan mencari IPv4 address dari network interfaces komputer
// dan menggunakannya untuk konfigurasi Vite HMR agar bisa diakses dari mobile
function getLocalIpAddress() {
    const nets = networkInterfaces();
    for (const name of Object.keys(nets)) {
        for (const net of nets[name]) {
            // Skip internal (127.0.0.1) dan non-IPv4 addresses
            const familyV4Value = typeof net.family === "string" ? "IPv4" : 4;
            if (net.family === familyV4Value && !net.internal) {
                return net.address;
            }
        }
    }
    return "localhost";
}

// [DITAMBAHKAN] Simpan IP address yang terdeteksi
const localIp = getLocalIpAddress();
// [DITAMBAHKAN] Tampilkan IP yang akan digunakan saat dev server berjalan
console.log(`🌐 Vite akan menggunakan IP: ${localIp}`);

// Allow the local browser and HMR endpoint to be configured per machine.
// This avoids collisions when another Vite project already uses the default port.
const env = loadEnv(process.env.NODE_ENV || "development", process.cwd(), "");
const hmrHost = env.VITE_HMR_HOST || "localhost";
const devPort = Number(env.VITE_DEV_SERVER_PORT || 5173);

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
    // [DITAMBAHKAN] Configure the development server
    // Konfigurasi ini memungkinkan Vite dev server dapat diakses dari perangkat lain
    // di jaringan yang sama (seperti mobile device) tanpa perlu mengubah file .env
    server: {
        host: "0.0.0.0", // [DITAMBAHKAN] Listening di semua network interfaces
        port: devPort,
        strictPort: true, // [DITAMBAHKAN] Gagal jika port sudah digunakan
        hmr: {
            // [DITAMBAHKAN] Hot Module Replacement configuration
            // HMR menggunakan IP yang terdeteksi otomatis agar CSS/JS reload
            // berfungsi dengan baik saat diakses dari mobile device
            host: hmrHost, // [DITAMBAHKAN] Auto-detect IP, or VITE_HMR_HOST to override
            protocol: "ws", // [DITAMBAHKAN] WebSocket protocol untuk HMR
            port: devPort,
            clientPort: devPort,
        },
    },
});
