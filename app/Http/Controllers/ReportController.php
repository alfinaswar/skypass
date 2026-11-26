<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive; 
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Facades\File;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman menu laporan (Hanya opsional jika ingin filter lebih detail)
     */
    public function index()
    {
        $vehicles = DB::table('vehicles')->orderBy('fleet_number', 'asc')->get();
        return view('reports.index', compact('vehicles'));
    }
    
    /**
     * 1. Export Laporan Stok Opname
     * Urut berdasarkan Lokasi Rak.
     */
    public function exportStock()
    {
        $fileName = 'stock_opname_' . date('Y-m-d_H-i') . '.csv';

        $data = DB::table('spareparts')
            ->select('rack_location', 'part_number', 'description', 'category', 'current_stock', 'unit', 'min_stock')
            ->orderBy('rack_location', 'asc')
            ->get();

        return $this->generateCsv($fileName, $data, [
            'Rack Location', 'Part Number', 'Description', 'Category', 'Current Stock', 'Unit', 'Min Stock'
        ]);
    }

    /**
     * 2. Export Riwayat Transaksi per Kendaraan.
     */
    public function exportVehicleHistory(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
        ]);

        $vehicle = DB::table('vehicles')->where('id', $request->vehicle_id)->first();
        $fileName = 'history_' . $vehicle->fleet_number . '_' . date('Y-m-d') . '.csv';

        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('spareparts', 'transaction_details.sparepart_id', '=', 'spareparts.id')
            ->select(
                'transactions.date',
                'transactions.invoice_no',
                'spareparts.description',
                'spareparts.part_number',
                DB::raw('SUM(transaction_details.qty) as qty'),
                'spareparts.unit',
                'transactions.requester_name'
            )
            ->where('transactions.type', 'OUT') // Hanya pemakaian (GI)
            ->where('transactions.vehicle_id', $request->vehicle_id)
            ->where('transactions.status', '!=', 'canceled')
            ->groupBy('transactions.date', 'transactions.invoice_no', 'spareparts.description', 'spareparts.part_number', 'spareparts.unit', 'transactions.requester_name');

        // Filter Tanggal Opsional
        if ($request->start_date) {
            $query->where('transactions.date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('transactions.date', '<=', $request->end_date);
        }

        $data = $query->orderBy('transactions.date', 'desc')->get();

        return $this->generateCsv($fileName, $data, [
            'Date', 'Invoice No', 'Item Name', 'Part Number', 'Qty', 'Unit', 'Technician/Requester'
        ]);
    }

    /**
     * 3. DOWNLOAD GI DATA (Good Issued)
     * Sort by Vehicle Fleet Number.
     * Columns: Date & Time, Description, Qty, Vehicle, Technician.
     */
    public function exportGI()
    {
        $fileName = 'transaction_gi_' . date('Y-m-d_H-i') . '.csv';

        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('spareparts', 'transaction_details.sparepart_id', '=', 'spareparts.id')
            ->join('vehicles', 'transactions.vehicle_id', '=', 'vehicles.id')
            ->select(
                'transactions.created_at',   // Tanggal & Jam
                'spareparts.description',    // Deskripsi Part
                DB::raw('SUM(transaction_details.qty) as qty'),   // Qty
                'spareparts.unit',           // Satuan
                'vehicles.fleet_number',     // Vehicle
                'transactions.requester_name'// Technician
            )
            ->where('transactions.type', 'OUT')
            ->where('transactions.status', '!=', 'canceled') // Hanya yang valid
            ->groupBy('transactions.id', 'transactions.created_at', 'spareparts.id', 'spareparts.description', 'spareparts.unit', 'vehicles.fleet_number', 'transactions.requester_name')
            ->orderBy('vehicles.fleet_number', 'asc') // SORT BY VEHICLE
            ->orderBy('transactions.created_at', 'desc');

        $data = $query->get();

        // Format data sebelum dikirim ke CSV (terutama tanggal)
        $formattedData = $data->map(function($item) {
            return [
                'Date & Time' => \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i:s'),
                'Description' => $item->description,
                'Quantity'    => $item->qty . ' ' . $item->unit,
                'Facility'     => $item->fleet_number,
                'Technician'    => $item->requester_name
            ];
        });

        return $this->generateCsv($fileName, $formattedData, [
            'Date & Time', 'Part Description', 'Quantity', 'Facility', 'Technician'
        ]);
    }

    /**
     * 4. DOWNLOAD GR DATA (Good Receipt)
     * Sort by Created At & PO.
     * Columns: Date & Time, PO Number, Description, Qty, Source, Source Type.
     */
    public function exportGR()
    {
        $fileName = 'transaction_gr_' . date('Y-m-d_H-i') . '.csv';

        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('spareparts', 'transaction_details.sparepart_id', '=', 'spareparts.id')
            ->select(
                'transactions.created_at',   // Tanggal & Jam
                'transactions.purchase_order', // Nomor PO (Untuk sorting)
                'spareparts.description',    // Deskripsi Part
                DB::raw('SUM(transaction_details.qty) as qty'),   // Qty
                'spareparts.unit',           // Satuan
                'transactions.source_name',  // Source
                'transactions.source_type'   // Type Source
            )
            ->where('transactions.type', 'IN')
            ->where('transactions.status', '!=', 'canceled')
            ->groupBy('transactions.id', 'transactions.created_at', 'transactions.purchase_order', 'spareparts.id', 'spareparts.description', 'spareparts.unit', 'transactions.source_name', 'transactions.source_type')
            ->orderBy('transactions.created_at', 'desc')
            ->orderBy('transactions.purchase_order', 'desc');

        $data = $query->get();

        $formattedData = $data->map(function($item) {
            return [
                'Date & Time' => \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i:s'),
                'PO Number'   => $item->purchase_order ?? '-',
                'Description' => $item->description,
                'Quantity'    => $item->qty . ' ' . $item->unit,
                'Source'      => $item->source_name ?? '-',
                'Type'        => ucfirst($item->source_type)
            ];
        });

        return $this->generateCsv($fileName, $formattedData, [
            'Date & Time', 'PO Number', 'Part Description', 'Quantity', 'Source', 'Source Type'
        ]);
    }

    /**
     * 5. Download Full Backup (Database + Images) as ZIP
     * (Admin Tool)
     */
    public function downloadBackup()
    {
        // Pastikan hanya Supervisor yang bisa akses (Security Layer 2)
        if (auth()->user()->role !== 'supervisor') {
            abort(403, 'Unauthorized action.');
        }

        $fileName = 'backup_skypass_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = public_path($fileName);

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $connection = config('database.default');

            // 1. Masukkan Database (otomatis SQLite atau MySQL/MariaDB)
            if ($connection === 'sqlite') {
                $dbPath = database_path('database.sqlite');
                if (file_exists($dbPath)) {
                    $zip->addFile($dbPath, 'database.sqlite');
                }
            } else {
                $tables = collect(DB::select('SHOW TABLES'))
                    ->map(fn ($row) => array_values((array) $row)[0])
                    ->reject(fn ($table) => $table === 'migrations')
                    ->values();

                $dump = [];
                foreach ($tables as $table) {
                    $dump[$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
                }

                $zip->addFromString('database_backup.json', json_encode($dump, JSON_UNESCAPED_UNICODE));
            }

            // 2. Masukkan Folder Uploads & Installation Proofs secara Rekursif
            $folders = [
                'uploads' => public_path('uploads'),
                'installations' => storage_path('app/private/installations'),
            ];

            foreach ($folders as $zipPrefix => $folderPath) {
                if (File::exists($folderPath)) {
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($folderPath),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    );

                    foreach ($files as $file) {
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = $zipPrefix . '/' . ltrim(str_replace('\\', '/', substr($filePath, strlen($folderPath))), '/');
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                }
            }

            $zip->close();
        }

        // Return file zip lalu hapus dari server setelah terdownload agar hemat space
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    /**
     * Fungsi Helper Private untuk generate CSV Stream.
     * Menggunakan StreamedResponse agar memori server tidak jebol saat data banyak.
     */
    private function generateCsv($fileName, $data, $headers)
    {
        $response = new StreamedResponse(function () use ($data, $headers) {
            $handle = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8 compatibility (Agar Excel baca rapi)
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // PERBAIKAN: Gunakan pemisah Titik Koma (;) agar Excel Indonesia otomatis membaca kolom
            $delimiter = ';'; 

            // Write Headers
            fputcsv($handle, $headers, $delimiter);

            // Write Data Rows
            foreach ($data as $row) {
                fputcsv($handle, (array) $row, $delimiter);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
