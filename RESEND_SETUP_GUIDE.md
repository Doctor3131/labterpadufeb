# 🚀 Setup Resend untuk Email System

## Kenapa Resend?

✅ **Free Tier Generous**: 3,000 emails/bulan (vs Gmail App Password yang ribet)
✅ **Setup Mudah**: Hanya perlu API Key
✅ **Modern Dashboard**: Tracking, analytics, logs lengkap
✅ **Deliverability Bagus**: Email jarang masuk spam
✅ **Developer Friendly**: Dokumentasi jelas, API simple
✅ **No Credit Card**: Free tier tanpa perlu CC

---

## 📝 Langkah Setup Resend (5 Menit)

### **1. Buat Akun Resend**

1. Kunjungi: https://resend.com/signup
2. Sign up dengan GitHub atau email
3. Verifikasi email (cek inbox)
4. Masuk ke Dashboard

### **2. Get API Key**

1. Di Dashboard Resend, klik **"API Keys"** di sidebar kiri
2. Klik **"Create API Key"**
3. Beri nama: `Lab Terpadu Production` atau `Lab Terpadu Development`
4. Pilih permission: **"Sending access"**
5. Klik **"Create"**
6. **COPY API Key** yang muncul (format: `re_xxxxxxxxxxxxx`)
   - ⚠️ **Penting**: API Key hanya muncul sekali, simpan baik-baik!

### **3. Verify Domain (Opsional - Recommended untuk Production)**

Untuk mengirim dari email custom seperti `labterpadu@feb.undip.ac.id`:

1. Di Dashboard, klik **"Domains"** → **"Add Domain"**
2. Masukkan domain: `feb.undip.ac.id`
3. Resend akan memberikan DNS records (SPF, DKIM, DMARC)
4. Tambahkan records ini ke DNS settings domain (minta IT UNDIP)
5. Wait untuk verification (biasanya 10-30 menit)

**Jika belum verify domain**, Resend akan mengirim dari: `onboarding@resend.dev`
(Tetap bisa dipakai untuk testing!)

### **4. Update File `.env`**

File `.env` sudah diupdate. Tinggal ganti API Key:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=465
MAIL_USERNAME=resend
MAIL_PASSWORD=re_xxxxxxxxxxxxx  # ← Paste API Key di sini
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="labterpadu@feb.undip.ac.id"  # ← Ganti jika perlu
MAIL_FROM_NAME="Lab Terpadu FEB UNDIP"
```

**Catatan:**
- `MAIL_USERNAME` selalu `resend` (jangan diganti)
- `MAIL_PASSWORD` adalah API Key dari Resend
- `MAIL_FROM_ADDRESS` gunakan domain yang sudah diverify (atau `onboarding@resend.dev` untuk testing)

### **5. Clear Cache Laravel**

```bash
php artisan config:clear
php artisan cache:clear
```

### **6. Test Email**

```bash
php artisan tinker
```

Di tinker console:
```php
Mail::raw('Test email dari Lab Terpadu via Resend', function($message) {
    $message->to('email-anda@gmail.com')
            ->subject('Test Email System dengan Resend');
});
```

Cek inbox! Email akan terkirim dari Resend.

---

## 📊 Dashboard Resend

Setelah setup, Anda bisa:

1. **Monitor Email**: Lihat semua email yang terkirim di "Emails" tab
2. **Check Status**: Delivered, opened, clicked, bounced
3. **View Logs**: Debug jika ada masalah
4. **Analytics**: Statistik email per hari/minggu/bulan

Dashboard: https://resend.com/emails

---

## 🎯 Setup Domain untuk Production

Untuk mengirim dari email resmi `@feb.undip.ac.id`:

### **Records yang Perlu Ditambahkan di DNS:**

Setelah add domain di Resend, akan muncul 3 DNS records:

```
1. SPF (TXT Record):
   Name: feb.undip.ac.id
   Value: v=spf1 include:amazonses.com ~all

2. DKIM (TXT Record):
   Name: resend._domainkey.feb.undip.ac.id
   Value: [long string dari Resend]

3. DMARC (TXT Record):
   Name: _dmarc.feb.undip.ac.id
   Value: v=DMARC1; p=none;
```

### **Cara Update DNS:**

**Jika UNDIP pakai cPanel/WHM:**
1. Login ke cPanel
2. Zone Editor
3. Add Record → TXT
4. Paste values dari Resend

**Jika managed by IT:**
1. Screenshot DNS records dari Resend
2. Email ke IT Support UNDIP
3. Request untuk add records tersebut

**Verifikasi:**
- Tunggu 10-30 menit propagasi DNS
- Refresh halaman Domains di Resend
- Status akan berubah ke "Verified ✅"

---

## 💰 Pricing & Limits

### **Free Tier** (Cocok untuk Lab Terpadu)
- ✅ 3,000 emails/bulan
- ✅ 100 emails/hari
- ✅ Unlimited domains
- ✅ Email logs 30 hari
- ✅ Full API access

### **Paid Plans** (Jika butuh lebih)
- **Pro**: $20/bulan → 50,000 emails
- **Business**: $80/bulan → 250,000 emails

**Estimasi kebutuhan Lab Terpadu:**
- Booking baru: 1 email (confirmation)
- Approve/Reject: 1 email
- Rata-rata: 2-3 emails per booking
- **3,000 emails = ~1,000 bookings/bulan** (sangat cukup!)

---

## 🆚 Perbandingan dengan Alternatif Lain

| Service | Free Tier | Setup | Deliverability | Dashboard |
|---------|-----------|-------|----------------|-----------|
| **Resend** ⭐ | 3,000/month | ⭐⭐⭐⭐⭐ Easy | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ Modern |
| Gmail | Unlimited* | ⚠️ App Password ribet | ⭐⭐⭐ OK | ❌ No dashboard |
| Mailtrap | 500/month | ⭐⭐⭐⭐⭐ Easy | N/A (testing only) | ⭐⭐⭐⭐ Good |
| SendGrid | 100/day | ⭐⭐⭐ Medium | ⭐⭐⭐⭐ Good | ⭐⭐⭐⭐ Complex |
| Mailgun | 100/day | ⭐⭐ Hard | ⭐⭐⭐⭐ Good | ⭐⭐⭐ OK |

**Resend adalah pilihan terbaik untuk production!** 🏆

---

## 🔧 Troubleshooting

### **Email tidak terkirim / Error 550**

```
Error: 550-5.7.1 Unauthenticated email from domain is not accepted
```

**Solusi:**
- Domain belum diverify di Resend
- Sementara gunakan `MAIL_FROM_ADDRESS="onboarding@resend.dev"`
- Atau verify domain dulu

### **API Key Invalid**

```
Error: Invalid API key
```

**Solusi:**
- Pastikan API Key benar (format: `re_xxxxxxxx`)
- API Key di `.env` sesuai dengan yang di Resend Dashboard
- Jalankan `php artisan config:clear`

### **Port Connection Failed**

**Coba alternatif port:**

```env
# Opsi 1 (Default - Recommended):
MAIL_PORT=465
MAIL_ENCRYPTION=ssl

# Opsi 2:
MAIL_PORT=587
MAIL_ENCRYPTION=tls

# Opsi 3:
MAIL_PORT=2465
MAIL_ENCRYPTION=ssl
```

### **Email Masuk Spam**

Jika domain belum diverify:
- Normal untuk development
- Untuk production: Verify domain + setup SPF/DKIM/DMARC

---

## 🎓 Tips & Best Practices

### **1. Development vs Production**

**Development (.env.local):**
```env
MAIL_FROM_ADDRESS="onboarding@resend.dev"  # No verification needed
```

**Production (.env.production):**
```env
MAIL_FROM_ADDRESS="labterpadu@feb.undip.ac.id"  # Domain verified
```

### **2. Multiple Environments**

Buat API Key terpisah untuk:
- Development (`Lab Terpadu Dev`)
- Staging (`Lab Terpadu Staging`)
- Production (`Lab Terpadu Production`)

### **3. Monitor Usage**

Check dashboard regular:
- Pastikan tidak mendekati 3,000/bulan limit
- Monitor bounce rate (email invalid)
- Check spam reports

### **4. Email Testing**

Gunakan email sendiri dulu untuk testing:
```php
// Test all email templates
$booking = Booking::first();
Mail::to('your-email@gmail.com')->send(new BookingConfirmation($booking));
Mail::to('your-email@gmail.com')->send(new BookingApproved($booking));
Mail::to('your-email@gmail.com')->send(new BookingRejected($booking));
```

---

## 📚 Resources

- **Resend Dashboard**: https://resend.com/emails
- **Documentation**: https://resend.com/docs
- **API Reference**: https://resend.com/docs/api-reference
- **Laravel Integration**: https://resend.com/docs/send-with-laravel
- **Status Page**: https://status.resend.com/

---

## ✅ Quick Start Checklist

- [ ] Sign up di https://resend.com
- [ ] Create API Key
- [ ] Copy API Key ke `.env` → `MAIL_PASSWORD`
- [ ] Set `MAIL_FROM_ADDRESS="onboarding@resend.dev"` (untuk testing)
- [ ] Run `php artisan config:clear`
- [ ] Test dengan `php artisan tinker`
- [ ] (Opsional) Verify domain untuk production
- [ ] Monitor di dashboard

---

## 🎉 Keuntungan Resend untuk Lab Terpadu

✅ **No Credit Card**: Free tier tanpa perlu kartu kredit
✅ **3,000 emails/month**: Cukup untuk ~1,000 bookings
✅ **Beautiful Emails**: Template HTML rendering bagus
✅ **Email Tracking**: Tahu email delivered atau tidak
✅ **Easy Debugging**: Logs lengkap di dashboard
✅ **Professional**: Email tidak masuk spam (jika domain verified)
✅ **Fast Setup**: 5 menit sudah jalan

---

## 🔐 Security Notes

1. **Jangan commit API Key ke Git**
   - API Key di `.env` (sudah di `.gitignore`)
   - Share via secure channel

2. **Rotate API Keys**
   - Ganti API Key setiap 6 bulan
   - Revoke old keys di dashboard

3. **Monitor Abuse**
   - Check logs untuk unusual activity
   - Setup alerts di Resend dashboard

---

## 🆘 Need Help?

**Resend Support:**
- Email: support@resend.com
- Docs: https://resend.com/docs
- Discord: https://discord.gg/resend

**Lab Terpadu Issues:**
- Check `storage/logs/laravel.log`
- Test dengan tinker
- Verify config dengan `php artisan config:show mail`

---

## 📝 Next Steps After Setup

1. ✅ Test semua email templates (confirmation, approved, rejected)
2. ✅ Check email rendering di berbagai email clients
3. ✅ Setup domain verification (jika production)
4. ✅ Monitor dashboard setelah deploy
5. ✅ Configure webhooks untuk track bounces (advanced)

**Current Status:** Ready to use with `onboarding@resend.dev`! 🚀
