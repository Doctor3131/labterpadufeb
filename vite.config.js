import { defineConfig } from "vite";
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

// [DITAMBAHKAN] Allow an explicit HMR host for containerized dev (Docker),
// e.g. VITE_HMR_HOST=localhost when the host browser reaches Vite on :5173.
const hmrHost = process.env.VITE_HMR_HOST || localIp;

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
        port: 5173, // [DITAMBAHKAN] Port default Vite
        strictPort: true, // [DITAMBAHKAN] Gagal jika port sudah digunakan
        hmr: {
            // [DITAMBAHKAN] Hot Module Replacement configuration
            // HMR menggunakan IP yang terdeteksi otomatis agar CSS/JS reload
            // berfungsi dengan baik saat diakses dari mobile device
            host: hmrHost, // [DITAMBAHKAN] Auto-detect IP, or VITE_HMR_HOST in Docker
            protocol: "ws", // [DITAMBAHKAN] WebSocket protocol untuk HMR
            port: 5173, // [DITAMBAHKAN] Port untuk HMR WebSocket
            clientPort: 5173, // [DITAMBAHKAN] Port yang digunakan client untuk connect
        },
    },
});
