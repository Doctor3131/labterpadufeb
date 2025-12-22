<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Peminjaman</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f3f4f6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #EAB308 0%, #F59E0B 100%); padding: 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                                Lab Terpadu FEB UNDIP
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #ffffff; font-size: 16px;">
                                Sistem Peminjaman Laboratorium
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <div style="text-align: center; margin-bottom: 30px;">
                                <div style="width: 80px; height: 80px; background-color: #FEF3C7; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                                    <span style="font-size: 40px;">⏱️</span>
                                </div>
                                <h2 style="margin: 0; color: #1F2937; font-size: 24px; font-weight: bold;">
                                    Peminjaman Berhasil Diajukan
                                </h2>
                                <p style="margin: 10px 0 0 0; color: #6B7280; font-size: 16px;">
                                    Permintaan Anda sedang menunggu persetujuan
                                </p>
                            </div>

                            <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
                                Halo <strong>{{ $booking->nama_peminjam }}</strong>,
                            </p>

                            <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
                                Terima kasih telah mengajukan peminjaman laboratorium. Berikut adalah detail peminjaman Anda:
                            </p>

                            <!-- Booking Details -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #F9FAFB; border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table width="100%" cellpadding="8" cellspacing="0">
                                            <tr>
                                                <td style="color: #6B7280; font-size: 14px; width: 40%;">ID Booking:</td>
                                                <td style="color: #1F2937; font-size: 14px; font-weight: bold;">#{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #6B7280; font-size: 14px; padding-top: 12px;">Laboratorium:</td>
                                                <td style="color: #1F2937; font-size: 14px; font-weight: bold; padding-top: 12px;">{{ $booking->lab->name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #6B7280; font-size: 14px; padding-top: 12px;">Tanggal:</td>
                                                <td style="color: #1F2937; font-size: 14px; font-weight: bold; padding-top: 12px;">{{ \Carbon\Carbon::parse($booking->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #6B7280; font-size: 14px; padding-top: 12px;">Waktu:</td>
                                                <td style="color: #1F2937; font-size: 14px; font-weight: bold; padding-top: 12px;">{{ $booking->start_time }} - {{ $booking->end_time }} WIB</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #6B7280; font-size: 14px; padding-top: 12px;">Status:</td>
                                                <td style="padding-top: 12px;">
                                                    <span style="background-color: #FEF3C7; color: #92400E; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">
                                                        ⏱ MENUNGGU PERSETUJUAN
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Tracking Link -->
                            <div style="background-color: #DBEAFE; border-left: 4px solid #3B82F6; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                                <p style="margin: 0 0 12px 0; color: #1E40AF; font-size: 15px; font-weight: bold;">
                                    🔍 Tracking Peminjaman
                                </p>
                                <p style="margin: 0 0 12px 0; color: #1E3A8A; font-size: 14px;">
                                    Gunakan link berikut untuk memantau status peminjaman Anda:
                                </p>
                                <a href="{{ route('booking.track', $booking->tracking_token) }}" 
                                   style="display: inline-block; background-color: #3B82F6; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;">
                                    Cek Status Peminjaman
                                </a>
                            </div>

                            <!-- Info Box -->
                            <div style="background-color: #F0FDF4; border-left: 4px solid: #10B981; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                                <p style="margin: 0 0 8px 0; color: #065F46; font-size: 14px; font-weight: bold;">
                                    ℹ️ Informasi Penting:
                                </p>
                                <ul style="margin: 0; padding-left: 20px; color: #047857; font-size: 14px; line-height: 1.8;">
                                    <li>Permintaan akan diproses oleh admin Lab Terpadu</li>
                                    <li>Anda akan menerima email notifikasi ketika status berubah</li>
                                    <li>Jangan gunakan laboratorium sebelum mendapat persetujuan</li>
                                    <li>Untuk pertanyaan, hubungi administrasi Lab Terpadu FEB UNDIP</li>
                                </ul>
                            </div>

                            <p style="color: #6B7280; font-size: 14px; line-height: 1.6; margin: 0;">
                                Terima kasih,<br>
                                <strong>Tim Lab Terpadu FEB UNDIP</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #F9FAFB; padding: 20px 30px; text-align: center; border-top: 1px solid #E5E7EB;">
                            <p style="margin: 0; color: #9CA3AF; font-size: 12px;">
                                Email otomatis dari Sistem Peminjaman Lab Terpadu FEB UNDIP
                            </p>
                            <p style="margin: 8px 0 0 0; color: #9CA3AF; font-size: 12px;">
                                © {{ date('Y') }} Lab Terpadu FEB UNDIP. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
