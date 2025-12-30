<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Disetujui</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f3f4f6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); padding: 30px; text-align: center;">
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
                                <div style="width: 80px; height: 80px; background-color: #D1FAE5; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                                    <span style="font-size: 40px;">✅</span>
                                </div>
                                <h2 style="margin: 0; color: #059669; font-size: 24px; font-weight: bold;">
                                    Peminjaman Disetujui!
                                </h2>
                                <p style="margin: 10px 0 0 0; color: #6B7280; font-size: 16px;">
                                    Selamat! Peminjaman laboratorium Anda telah disetujui
                                </p>
                            </div>

                            <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
                                Halo <strong>{{ $booking->nama_peminjam }}</strong>,
                            </p>

                            <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
                                Kabar baik! Peminjaman laboratorium Anda telah <strong style="color: #059669;">DISETUJUI</strong> oleh admin Lab Terpadu FEB UNDIP.
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
                                                <td style="color: #6B7280; font-size: 14px; padding-top: 12px;">Jumlah Peserta:</td>
                                                <td style="color: #1F2937; font-size: 14px; font-weight: bold; padding-top: 12px;">{{ $booking->jumlah_peserta }} orang</td>
                                            </tr>
                                            <tr>
                                                <td style="color: #6B7280; font-size: 14px; padding-top: 12px;">Status:</td>
                                                <td style="padding-top: 12px;">
                                                    <span style="background-color: #D1FAE5; color: #065F46; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">
                                                        ✅ DISETUJUI
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Important Notes -->
                            <div style="background-color: #FEF3C7; border-left: 4px solid #F59E0B; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                                <p style="margin: 0 0 8px 0; color: #92400E; font-size: 14px; font-weight: bold;">
                                    ⚠️ Hal yang Perlu Diperhatikan:
                                </p>
                                <ul style="margin: 0; padding-left: 20px; color: #B45309; font-size: 14px; line-height: 1.8;">
                                    <li>Harap datang <strong>tepat waktu</strong> sesuai jadwal yang telah ditentukan</li>
                                    <li>Jaga kebersihan dan kerapihan laboratorium selama penggunaan</li>
                                    <li>Laporkan jika ada kerusakan atau masalah pada peralatan lab</li>
                                    <li>Pastikan semua peralatan dikembalikan dalam kondisi baik</li>
                                    @if($booking->is_recurring)
                                    <li>Peminjaman ini berlaku untuk <strong>setiap minggu</strong> pada hari dan waktu yang sama</li>
                                    @endif
                                </ul>
                            </div>

                            <!-- Tracking Link -->
                            <div style="text-align: center; margin-bottom: 25px;">
                                <a href="{{ route('booking.success', $booking->tracking_token) }}" 
                                   style="display: inline-block; background-color: #10B981; color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);">
                                    Lihat Detail Peminjaman
                                </a>
                            </div>

                            <p style="color: #6B7280; font-size: 14px; line-height: 1.6; margin: 0;">
                                Jika ada pertanyaan atau kendala, silakan hubungi administrasi Lab Terpadu FEB UNDIP.<br><br>
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
