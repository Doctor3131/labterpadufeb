# 📧 Panduan Setup Email untuk Production

## File yang Sudah Diupdate

File `.env` sudah diupdate dengan konfigurasi SMTP Gmail. Sekarang tinggal isi kredensial yang sesuai.

---

## 🎯 Opsi 1: Gmail (Recommended untuk Testing)

### **Langkah Setup Gmail:**

**1. Aktifkan 2-Factor Authentication (2FA)**
   - Buka: https://myaccount.google.com/security
   - Scroll ke "2-Step Verification"
   - Klik "Get Started" dan ikuti petunjuk

**2. Generate App Password**
   - Buka: https://myaccount.google.com/apppasswords
   - Pilih "Mail" sebagai app
   - Pilih "Other" sebagai device, ketik "Lab Terpadu"
   - Klik "Generate"
   - **Copy password 16 karakter yang muncul** (tanpa spasi)

**3. Update File `.env`**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=alamat-email-anda@gmail.com
MAIL_PASSWORD=abcd efgh ijkl mnop  # 16 char App Password (hapus spasi)
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="labterpadu@feb.undip.ac.id"
MAIL_FROM_NAME="Lab Terpadu FEB UNDIP"
```

**4. Clear Cache & Test**

```bash
php artisan config:clear
php artisan cache:clear
```

### ⚠️ **Catatan Penting Gmail:**
- **JANGAN pakai password Gmail biasa** - harus App Password
- Jika error "Username and Password not accepted", cek:
  - 2FA sudah aktif?
  - App Password sudah benar (16 karakter)?
  - Less Secure Apps TIDAK perlu diaktifkan (deprecated)

---

## 🎯 Opsi 2: Mailtrap (Recommended untuk Development)

Mailtrap menangkap semua email testing tanpa mengirim ke inbox asli.

**1. Buat Akun Gratis**
   - Daftar di: https://mailtrap.io/
   - Gratis untuk 500 emails/bulan

**2. Get Credentials**
   - Masuk ke Dashboard
   - Pilih Inbox → SMTP Settings
   - Copy kredensial yang ditampilkan

**3. Update `.env`**

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="labterpadu@feb.undip.ac.id"
MAIL_FROM_NAME="Lab Terpadu FEB UNDIP"
```

### ✅ **Keuntungan Mailtrap:**
- Tidak perlu email asli
- Bisa lihat tampilan email di berbagai email client
- Tidak ada risiko spam
- Testing aman

---

## 🎯 Opsi 3: SMTP UNDIP (Untuk Production)

Jika FEB UNDIP punya SMTP server sendiri:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.undip.ac.id  # Tanya ke IT UNDIP
MAIL_PORT=587
MAIL_USERNAME=labterpadu@feb.undip.ac.id
MAIL_PASSWORD=password-email-undip
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="labterpadu@feb.undip.ac.id"
MAIL_FROM_NAME="Lab Terpadu FEB UNDIP"
```

**Hubungi:** IT Support UNDIP untuk mendapatkan:
- SMTP host address
- Port number (biasanya 587 atau 465)
- Kredensial email resmi

---

## 🎯 Opsi 4: SendGrid (Scalable untuk Production)

SendGrid bagus untuk volume email besar (100 emails/day gratis).

**1. Daftar SendGrid**
   - https://sendgrid.com/
   - Pilih Free Plan

**2. Create API Key**
   - Settings → API Keys → Create API Key
   - Full Access atau Mail Send saja
   - Copy API Key

**3. Update `.env`**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.xxxxxxxxxxxxxxxxxxxxx  # API Key dari SendGrid
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="labterpadu@feb.undip.ac.id"
MAIL_FROM_NAME="Lab Terpadu FEB UNDIP"
```

---

## 🧪 Testing Email

Setelah konfigurasi, test dengan:

### **1. Via Tinker (Terminal)**

```bash
php artisan tinker
```

```php
Mail::raw('Test email dari Lab Terpadu', function($message) {
    $message->to('email-test@example.com')
            ->subject('Test Email System');
});
```

Jika tidak ada error, email berhasil terkirim!

### **2. Via Application**

1. Buat booking baru di form
2. Isi email Anda sendiri
3. Submit form
4. Cek inbox email Anda (atau spam folder)

---

## 🔧 Troubleshooting

### **Error: "Connection could not be established with host"**

**Solusi:**
```env
# Coba ganti port
MAIL_PORT=465
MAIL_ENCRYPTION=ssl

# Atau
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

### **Error: "Username and Password not accepted"**

**Gmail:** Pastikan pakai App Password, bukan password biasa
**SMTP Lain:** Cek username/password benar

### **Email masuk ke Spam**

Normal untuk development. Untuk production:
- Gunakan domain email resmi (@undip.ac.id)
- Setup SPF, DKIM, DMARC records
- Gunakan service seperti SendGrid/Mailgun

### **Email tidak terkirim tanpa error**

```bash
# Cek queue
php artisan queue:work

# Atau set QUEUE_CONNECTION=sync di .env untuk instant send
```

---

## 📝 Rekomendasi per Environment

| Environment | Recommended Service | Alasan |
|------------|-------------------|---------|
| **Local Development** | Mailtrap / Log | Aman, tidak spam inbox |
| **Testing/Staging** | Gmail / Mailtrap | Mudah setup |
| **Production** | SMTP UNDIP / SendGrid | Profesional, reliable |

---

## 🔒 Security Best Practices

1. **JANGAN commit `.env` ke Git**
   - Sudah ada di `.gitignore`
   - Share kredensial via secure channel

2. **Gunakan Environment Variables di Server**
   - Di production server, set via server config
   - Jangan hardcode di file

3. **Rotate Credentials Regularly**
   - Ganti App Password/API Key setiap 6 bulan
   - Revoke yang tidak dipakai

4. **Monitor Usage**
   - Cek log untuk detect abuse
   - Setup rate limiting

---

## 📊 Current Configuration

File `.env` sudah diupdate dengan template Gmail SMTP. 

**Yang perlu diubah:**
```env
MAIL_USERNAME=your-email@gmail.com          # ← Ganti dengan email Anda
MAIL_PASSWORD=your-app-password              # ← Ganti dengan App Password
MAIL_FROM_ADDRESS="labterpadu@feb.undip.ac.id"  # ← Ganti jika perlu
```

Setelah edit, jalankan:
```bash
php artisan config:clear
```

---

## ✅ Quick Start (Gmail)

```bash
# 1. Generate App Password di Google Account
# 2. Edit .env
MAIL_USERNAME=youremail@gmail.com
MAIL_PASSWORD=your-16-char-app-password

# 3. Clear cache
php artisan config:clear

# 4. Test
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));
```

Done! 🎉
