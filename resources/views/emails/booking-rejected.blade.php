<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Ditolak</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f3f4f6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); padding: 30px; text-align: center;">
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
                                <div style="width: 80px; height: 80px; background-color: #FEE2E2; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                                    <span style="font-size: 40px;">❌</span>
                                </div>
                                <h2 style="margin: 0; color: #DC2626; font-size: 24px; font-weight: bold;">
                                    Peminjaman Ditolak
                                </h2>
                                <p style="margin: 10px 0 0 0; color: #6B7280; font-size: 16px;">
                                    Mohon maaf, peminjaman laboratorium Anda tidak dapat disetujui
                                </p>
                            </div>

                            <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
                                Halo <strong>{{ $booking->nama_peminjam }}</strong>,
                            </p>

                            <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
                                Setelah dilakukan review, kami mohon maaf harus menginformasikan bahwa peminjaman laboratorium Anda <strong style="color: #DC2626;">DITOLAK</strong> oleh admin Lab Terpadu FEB UNDIP.
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
                                                    <span style="background-color: #FEE2E2; color: #991B1B; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">
                                                        ❌ DITOLAK
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Rejection Reason -->
                            @if($booking->rejection_reason)
                            <div style="background-color: #FEE2E2; border-left: 4px solid #EF4444; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                                <p style="margin: 0 0 8px 0; color: #991B1B; font-size: 14px; font-weight: bold;">
                                    📝 Alasan Penolakan:
                                </p>
                                <p style="margin: 0; color: #7F1D1D; font-size: 14px; line-height: 1.6;">
                                    {{ $booking->rejection_reason }}
                                </p>
                            </div>
                            @endif

                            <!-- Next Steps -->
                            <div style="background-color: #DBEAFE; border-left: 4px solid #3B82F6; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                                <p style="margin: 0 0 8px 0; color: #1E40AF; font-size: 14px; font-weight: bold;">
                                    💡 Langkah Selanjutnya:
                                </p>
                                <ul style="margin: 0; padding-left: 20px; color: #1E3A8A; font-size: 14px; line-height: 1.8;">
                                    <li>Anda dapat mengajukan peminjaman baru dengan jadwal yang berbeda</li>
                                    <li>Pastikan mengisi formulir dengan lengkap dan akurat</li>
                                    <li>Hubungi administrasi Lab Terpadu untuk informasi lebih lanjut</li>
                                    <li>Periksa ketersediaan laboratorium sebelum mengajukan peminjaman</li>
                                </ul>
                            </div>

                            <!-- Action Buttons -->
                            <div style="text-align: center; margin-bottom: 25px;">
                                <a href="{{ route('booking.create') }}" 
                                   style="display: inline-block; background-color: #EAB308; color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 2px 4px rgba(234, 179, 8, 0.3); margin: 0 5px;">
                                    Ajukan Peminjaman Baru
                                </a>
                                <a href="{{ route('booking.success', $booking->tracking_token) }}" 
                                   style="display: inline-block; background-color: #6B7280; color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 2px 4px rgba(107, 114, 128, 0.3); margin: 0 5px;">
                                    Lihat Detail
                                </a>
                            </div>

                            <p style="color: #6B7280; font-size: 14px; line-height: 1.6; margin: 0;">
                                Jika Anda memiliki pertanyaan mengenai penolakan ini, jangan ragu untuk menghubungi administrasi Lab Terpadu FEB UNDIP.<br><br>
                                Terima kasih atas pengertiannya,<br>
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
