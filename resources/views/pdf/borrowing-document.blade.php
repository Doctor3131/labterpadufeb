<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Peminjaman Barang</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        .page-wrapper {
            position: relative;
            padding: 0;
        }
        
        .header {
            width: 100%;
            height: auto;
            margin: 0;
            padding: 0;
            position: relative;
        }
        
        .header img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0;
            padding: 0;
        }
        
        .footer {
            width: 100%;
            height: auto;
            margin: 0;
            padding: 0;
            position: fixed;
            bottom: 0;
            left: 0;
        }
        
        .footer img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0;
            padding: 0;
        }
        
        .content-wrapper {
            margin: 5mm 18mm 18mm 18mm;
            padding: 0;
        }
        
        .title {
            text-align: center;
            margin: 0 0 10px 0;
        }
        
        .title h2 {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 3px 0;
            letter-spacing: 0.5px;
        }
        
        .title p {
            font-size: 10pt;
            margin: 3px 0;
        }
        
        .content {
            text-align: justify;
            margin: 5px 0;
            font-size: 10pt;
            line-height: 1.4;
        }
        
        .party-info {
            margin: 5px 0 5px 40px;
        }
        
        .party-info table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 2px;
        }
        
        .party-info td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 10pt;
            line-height: 1.4;
        }
        
        .party-info td:first-child {
            width: 120px;
        }
        
        .party-info td:nth-child(2) {
            width: 20px;
            text-align: left;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            font-size: 10pt;
        }
        
        .condition-col {
            text-align: center;
            width: 50px;
        }
        
        .signature-section {
            margin-top: 15px;
            width: 100%;
        }
        
        .signature-table {
            width: 100%;
        }
        
        .signature-box {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        
        .signature-space {
            height: 50px;
        }

        .signature-name-container {
             min-width: 200px;
             display: inline-block;
             text-align: center;
        }
        
        .signature-underline {
            border-bottom: 1px dotted #000;
            display: block;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- Header Image -->
        <div class="header">
            <img src="{{ public_path('images/header1.jpeg') }}" alt="Header">
        </div>

        <!-- Footer Image -->
        <div class="footer">
            <img src="{{ public_path('images/footer.jpeg') }}" alt="Footer">
        </div>

        <!-- Content -->
        <div class="content-wrapper">
            <div class="title">
                <h2>SURAT PEMINJAMAN BARANG</h2>
                <p>Nomor: {{ $borrowing->document_number }}</p>
            </div>

    <div class="content">
        <p style="margin: 0 0 5px 0; text-align: justify; line-height: 1.4;">Pada hari ini, {{ $documentFullDate }}, kami yang bertanda tangan di bawah ini:</p>

        <div class="party-info">
            <table>
                <tr>
                    <td>1.&nbsp;&nbsp;Nama</td>
                    <td>:</td>
                    <td>{{ $borrowing->first_party_name ?? 'Zola Gio Amri sakhi' }}</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jabatan</td>
                    <td>:</td>
                    <td>{{ $borrowing->first_party_position ?? 'Asisten UPK' }}</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Alamat</td>
                    <td>:</td>
                    <td>{{ $borrowing->first_party_address ?? 'Semarang' }}</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Telp. kantor</td>
                    <td>:</td>
                    <td>{{ $borrowing->first_party_phone ?? '+62 877-4119-1305' }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="padding-top: 3px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Selanjutnya disebut <strong>PIHAK PERTAMA</strong></td>
                </tr>
            </table>
        </div>

        <div class="party-info">
            <table>
                <tr>
                    <td>2.&nbsp;&nbsp;Nama</td>
                    <td>:</td>
                    <td>{{ $borrowing->borrower_name }}</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jabatan</td>
                    <td>:</td>
                    <td>{{ $borrowing->borrower_type }}{{ $borrowing->borrower_id_number ? ' - ' . $borrowing->borrower_id_number : '' }}</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Alamat</td>
                    <td>:</td>
                    <td>{{ $borrowing->borrower_address ?? '-' }}</td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Telp. kantor</td>
                    <td>:</td>
                    <td>{{ $borrowing->phone_number }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="padding-top: 3px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Selanjutnya disebut <strong>PIHAK KEDUA</strong></td>
                </tr>
            </table>
        </div>

        <p style="margin: 10px 0 5px 0; text-align: justify; line-height: 1.4;">Bahwa Pihak Pertama memberikan barang kepada Pihak Kedua selaku peminjam, berupa:</p>

        <table class="items-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 40px;">No.</th>
                    <th rowspan="2">Nama Barang</th>
                    <th rowspan="2" style="width: 120px;">Merk/Tipe</th>
                    <th rowspan="2" style="width: 70px;">Jumlah</th>
                    <th colspan="3" style="text-align: center;">Kondisi Barang</th>
                    <th rowspan="2">Keterangan</th>
                </tr>
                <tr>
                    <th class="condition-col">Baik</th>
                    <th class="condition-col">Cukup</th>
                    <th class="condition-col">Lengkap</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Kelompokkan items berdasarkan item_id untuk memisahkan varian/merk
                    $groupedItems = collect($items)->groupBy('item_id')->map(function($group) {
                        $first = $group->first();
                        return [
                            // Nama Barang diisi Kategori (jika ada), jika tidak gunakan Nama Item
                            'name' => $first->item->category ?? $first->item->name,
                            // Merk/Tipe diambil dari Item
                            'brand_type' => $first->item->brand ?? '-',
                            'quantity' => $group->sum('quantity'),
                            'condition_good' => $first->condition_good,
                            'condition_adequate' => $first->condition_adequate,
                            'condition_complete' => $first->condition_complete,
                            'remarks' => $first->remarks ?? '',
                        ];
                    })->values();
                @endphp
                @foreach($groupedItems as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td style="text-align: center;">{{ $item['brand_type'] }}</td>
                    <td style="text-align: center;">{{ $item['quantity'] }}</td>
                    <td class="condition-col">{{ $item['condition_good'] ? 'x' : '' }}</td>
                    <td class="condition-col">{{ $item['condition_adequate'] ? 'x' : '' }}</td>
                    <td class="condition-col">{{ $item['condition_complete'] ? 'x' : '' }}</td>
                    <td>{{ $item['remarks'] }}</td>
                </tr>
                @endforeach
                @php
                    $emptyRows = max(0, 5 - $groupedItems->count());
                @endphp
                @for($i = 0; $i < $emptyRows; $i++)
                <tr>
                    <td style="text-align: center;">{{ $groupedItems->count() + $i + 1 }}</td>
                    <td style="height: 20px;">&nbsp;</td>
                    <td style="text-align: center;">&nbsp;</td>
                    <td style="text-align: center;">&nbsp;</td>
                    <td class="condition-col">&nbsp;</td>
                    <td class="condition-col">&nbsp;</td>
                    <td class="condition-col">&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @endfor
            </tbody>
        </table>

        <p style="margin: 5px 0 3px 0; text-align: justify; line-height: 1.4;">
            Ruang/peralatan/barang-barang inventaris tersebut diterima dalam keadaan baik, dan apabila peralatan/barang-barang 
            inventaris milik Fakultas Ekonomika dan Bisnis Undip tersebut terjadi kerusakan/hilang selama dalam peminjaman 
            kami bersedia memperbaiki/mengganti sesuai keadaan semula, serta kebersihan ruangan setelah acara menjadi tanggung 
            jawab kami selaku peminjam.Demikian berita acara peminjaman barang ini dibuat dengan sebenarnya untuk dapat dipergunakan seperlunya.
        </p>
    </div>

        </div> {{-- end content-wrapper page 1 --}}
    </div> {{-- end page-wrapper page 1 --}}

    {{-- Page 2: Tanda Tangan --}}
    <div class="page-wrapper" style="page-break-before: always;">
        {{-- Header Page 2 --}}
        <div class="header">
            <img src="{{ public_path('images/header1.jpeg') }}" alt="Header">
        </div>

        {{-- Footer Page 2 --}}
        <div class="footer">
            <img src="{{ public_path('images/footer.jpeg') }}" alt="Footer">
        </div>

        <div class="content-wrapper">
            {{-- Tanggal di kanan atas --}}
            <p style="text-align: right; margin: 60px 0 60px 0; font-size: 10pt;">Semarang, {{ $documentDate }}</p>

            {{-- PIHAK KEDUA dan PIHAK PERTAMA sejajar --}}
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; text-align: center; vertical-align: top;">
                        <p style="margin: 0; font-weight: bold;">PIHAK KEDUA</p>
                        <div style="height: 100px;"></div>
                        <div class="signature-name-container">
                            <span style="display: block;">{{ $borrowing->borrower_name }}</span>
                            <span class="signature-underline"></span>
                        </div>
                    </td>
                    <td style="width: 50%; text-align: center; vertical-align: top;">
                        <p style="margin: 0; font-weight: bold;">PIHAK PERTAMA</p>
                        <div style="height: 100px;"></div>
                        <div class="signature-name-container">
                            <span style="display: block;">{{ $borrowing->first_party_name ?? 'Zola Gio Amri sakhi' }}</span>
                            <span class="signature-underline"></span>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Tanda Tangan Mengetahui --}}
            <div style="margin-top: 100px; text-align: center;">
                <p style="margin: 0;">Mengetahui,</p>
                <p style="margin: 5px 0 0 0;">Koordinator Laboratorium</p>
                <div style="height: 100px;"></div>
                <div style="display: inline-block; text-align: center;">
                    <span style="display: block;">(Nugraha Wicaksana, S.E., S.Kom.)</span>
                    <span style="display: block;">NIP. H.7. 198106042021101001</span>
                </div>
            </div>
        </div> {{-- end content-wrapper page 2 --}}
    </div> {{-- end page-wrapper page 2 --}}

</body>
</html>
