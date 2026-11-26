<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BAST - {{ $bast_number }}</title>
    <style>
        /* Pengaturan Dasar Dokumen */
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 14px; 
            line-height: 1.6; 
            color: #000; 
            margin: 10px 30px; 
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        /* Heading */
        .title { 
            font-size: 18px; 
            text-decoration: underline; 
            margin-top: 0;
            margin-bottom: 4px; 
        }
        .subtitle { 
            font-size: 16px; 
            margin-top: 0; 
            margin-bottom: 40px; 
        }

        .header-logo-container {
            text-align: right;
            margin-bottom: 0;
            padding-bottom: 0;
            line-height: 1;
        }
        .company-logo {
            max-width: 140px;
            max-height: 60px;
            height: auto;
            vertical-align: bottom;
        }
        
        /* Paragraf Utama */
        .content { 
            margin-top: 20px; 
            text-align: justify; 
        }
        
        /* Tabel Rincian */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            margin-bottom: 25px; 
        }
        th, td { 
            border: 1px solid #000; 
            padding: 10px; 
            text-align: left; 
        }
        th { 
            background-color: #f2f2f2; 
            text-align: center; 
        }
        
        /* Kolom Tanda Tangan */
        .signature-box { 
            width: 100%; 
            margin-top: 50px; 
        }
        .signature-left { 
            float: left; 
            width: 40%; 
            text-align: center; 
        }
        .signature-right { 
            float: right; 
            width: 40%; 
            text-align: center; 
        }
        .signature-space { 
            height: 90px; 
        }
        .clear { clear: both; }
        
        /* Page Break untuk Lampiran Foto */
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    @php
        // Konversi logo lokal ke Base64 agar DomPDF merender secara instan tanpa error path
        $logoPath = resource_path('assets/pertamina-logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }
    @endphp

    <!-- LOGO POJOK KANAN ATAS (RAPAT TANPA GAP) -->
    <div class="header-logo-container">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="company-logo" alt="Logo">
        @endif
    </div>

    <!-- JUDUL DI TENGAH (LANGSUNG DI BAWAH LOGO TANPA JARAK KOSONG) -->
    <div class="text-center" style="margin-top: 0; margin-bottom: 25px;">
        <div class="title font-bold">BERITA ACARA SERAH TERIMA</div>
        <div class="subtitle font-bold" style="margin-top: 4px; margin-bottom: 0;">{{ $bast_number }}</div>
    </div>

    @php
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $dateObj = \Carbon\Carbon::parse($date);
        $indoDate = $dateObj->format('d') . ' ' . $months[(int)$dateObj->format('m')] . ' ' . $dateObj->format('Y');
        
        $roleName = auth()->user()->role === 'supervisor' ? 'Spv. Maintenance' : 'Admin Maintenance';
    @endphp

    <div class="content">
        <p>
            Pada hari ini, <strong>{{ $indoDate }}</strong>, telah dilaksanakan serah terima untuk kendaraan 
            <strong>{{ $uniqueFleets }}</strong> kepada Tim <strong>{{ $receiving_team }}</strong> 
            dengan rincian sebagai berikut :
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">Deskripsi</th>
                <th style="width: 20%;">Jumlah</th>
                <th style="width: 30%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-center">{{ $item->qty }} {{ $item->unit }}</td>
                <td class="text-center">Keadaan Baik</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="content">
        <p>Demikian berita acara serah terima ini kami buat, agar dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="signature-box">
        <div class="signature-left">
            <p>Yang menerima,<br>Tim {{ $receiving_team }}</p>
            <div class="signature-space"></div>
            <p style="margin-bottom: 0;"><strong>_________________________</strong></p>
        </div>
        <div class="signature-right">
            <p>Yang menyerahkan,<br>Tim maintenance</p>
            <div class="signature-space"></div>
            <p style="margin-bottom: 0;"><strong>{{ auth()->user()->name }}</strong></p>
            <p style="margin-top: 5px; margin-bottom: 0;">{{ $roleName }}</p>
        </div>
        <div class="clear"></div>
    </div>

    @php
        $resolveProofFiles = function ($rawValue) {
            if (empty($rawValue)) {
                return [];
            }
            $trimmed = trim((string) $rawValue);
            $paths = str_starts_with($trimmed, '[') ? (json_decode($trimmed, true) ?? [$trimmed]) : [$trimmed];
            $resolved = [];
            foreach ($paths as $path) {
                if (!is_string($path) || $path === '' || str_ends_with(strtolower($path), '.pdf')) {
                    continue;
                }
                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
                    $resolved[] = \Illuminate\Support\Facades\Storage::disk('local')->path($path);
                } elseif (file_exists(public_path($path))) {
                    $resolved[] = public_path($path);
                }
            }
            return $resolved;
        };
    @endphp

    @foreach($transactions as $trx)
        @php
            $trxDetails = $details->where('transaction_id', $trx->id);
            $fleetNo = $trx->fleet_number ?? '-';
            $attachments = [];

            if ($trxDetails->isNotEmpty()) {
                foreach ($trxDetails as $detail) {
                    $beforeFiles = $resolveProofFiles($detail->proof_image_before_url);
                    if (empty($beforeFiles)) {
                        $beforeFiles = $resolveProofFiles($trx->proof_image_before_url);
                    }
                    $afterFiles = $resolveProofFiles($detail->proof_image_url);
                    if (empty($afterFiles)) {
                        $afterFiles = $resolveProofFiles($trx->proof_image_url);
                    }
                    if (!empty($beforeFiles) || !empty($afterFiles)) {
                        $attachments[] = (object) [
                            'description' => $detail->description,
                            'qty' => $detail->qty . ' ' . $detail->unit,
                            'beforeFiles' => $beforeFiles,
                            'afterFiles' => $afterFiles,
                        ];
                    }
                }
            } else {
                $beforeFiles = $resolveProofFiles($trx->proof_image_before_url);
                $afterFiles = $resolveProofFiles($trx->proof_image_url);
                if (!empty($beforeFiles) || !empty($afterFiles)) {
                    $attachments[] = (object) [
                        'description' => 'Sparepart',
                        'qty' => '',
                        'beforeFiles' => $beforeFiles,
                        'afterFiles' => $afterFiles,
                    ];
                }
            }
        @endphp

        @foreach($attachments as $att)
            <div class="page-break"></div>
            <div class="text-center" style="margin-top: 10px;">
                <div class="title font-bold" style="text-decoration: none; margin-bottom: 5px; font-size: 15px;">
                    Lampiran Dokumentasi Pemasangan: {{ $att->description }} pada {{ $fleetNo }}
                </div>
                <div style="font-size: 11px; color: #555; margin-bottom: 12px;">
                    Jumlah: {{ $att->qty }}
                </div>

                <!-- Layout Atas Bawah (Top & Bottom) dengan tabel agar rapi di DomPDF -->
                <div style="width: 100%; text-align: center;">
                    <!-- FOTO SEBELUM -->
                    <div style="margin-bottom: 14px;">
                        <div class="font-bold" style="margin-bottom: 6px; color: #000; font-size: 12px;">1. FOTO SEBELUM PEMASANGAN</div>
                        @if(!empty($att->beforeFiles))
                            <table style="width: 100%; border-collapse: collapse; border: none;">
                                @foreach(array_chunk($att->beforeFiles, 3) as $row)
                                    <tr>
                                        @foreach($row as $imgPath)
                                            <td style="border: none; padding: 4px; text-align: center; vertical-align: top; width: {{ round(100 / min(3, count($att->beforeFiles)), 2) }}%;">
                                                <img src="{{ $imgPath }}" style="display: block; margin: 0 auto; max-width: 100%; max-height: 180px; border: 1px solid #ccc; padding: 2px; background: #fff;">
                                            </td>
                                        @endforeach
                                        @for($empty = count($row); $empty < min(3, count($att->beforeFiles)); $empty++)
                                            <td style="border: none; padding: 4px;"></td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </table>
                        @else
                            <div style="height: 80px; border: 1px dashed #ccc; padding-top: 30px; color: #999; font-style: italic; font-size: 11px;">
                                Tidak ada foto sebelum
                            </div>
                        @endif
                    </div>

                    <!-- FOTO SESUDAH -->
                    <div>
                        <div class="font-bold" style="margin-bottom: 6px; color: #000; font-size: 12px;">2. FOTO SESUDAH PEMASANGAN</div>
                        @if(!empty($att->afterFiles))
                            <table style="width: 100%; border-collapse: collapse; border: none;">
                                @foreach(array_chunk($att->afterFiles, 3) as $row)
                                    <tr>
                                        @foreach($row as $imgPath)
                                            <td style="border: none; padding: 4px; text-align: center; vertical-align: top; width: {{ round(100 / min(3, count($att->afterFiles)), 2) }}%;">
                                                <img src="{{ $imgPath }}" style="display: block; margin: 0 auto; max-width: 100%; max-height: 180px; border: 1px solid #ccc; padding: 2px; background: #fff;">
                                            </td>
                                        @endforeach
                                        @for($empty = count($row); $empty < min(3, count($att->afterFiles)); $empty++)
                                            <td style="border: none; padding: 4px;"></td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </table>
                        @else
                            <div style="height: 80px; border: 1px dashed #ccc; padding-top: 30px; color: #999; font-style: italic; font-size: 11px;">
                                Tidak ada foto sesudah
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
</body>
</html>