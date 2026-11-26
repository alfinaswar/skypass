<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use App\Notifications\FiveDayRuleAlert;
use Illuminate\Support\Facades\Notification;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions (History).
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'OUT'); 

        $query = DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->leftJoin('vehicles', 'transactions.vehicle_id', '=', 'vehicles.id')
            ->select('transactions.*', 'users.name as user_name', 'vehicles.fleet_number')
            ->where('transactions.type', $tab)
            ->whereIn('transactions.status', ['completed', 'canceled']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereExists(function ($details) use ($search) {
                    $details->selectRaw('1')
                        ->from('transaction_details')
                        ->join('spareparts', 'transaction_details.sparepart_id', '=', 'spareparts.id')
                        ->whereColumn('transaction_details.transaction_id', 'transactions.id')
                        ->where(function ($part) use ($search) {
                            $part->where('spareparts.description', 'like', "%{$search}%")
                                ->orWhere('spareparts.part_number', 'like', "%{$search}%");
                        });
                })->orWhere('transactions.invoice_no', 'like', "%{$search}%")
                    ->orWhere('transactions.purchase_order', 'like', "%{$search}%");

                if (request('tab', 'OUT') === 'OUT') {
                    $q->orWhere('transactions.requester_name', 'like', "%{$search}%");
                }
            });
        }

        if ($tab === 'OUT' && $request->filled('vehicle_id')) {
            $query->where('transactions.vehicle_id', $request->vehicle_id);
        }

        $sort = $request->get('sort', 'desc'); 
        $query->orderBy('transactions.date', $sort)
            ->orderBy('transactions.created_at', $sort);

        $transactions = $query->paginate(15)->withQueryString();
        $transactionDetails = DB::table('transaction_details')
            ->join('spareparts', 'transaction_details.sparepart_id', '=', 'spareparts.id')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereIn('transaction_details.transaction_id', $transactions->pluck('id'))
            ->select(
                'transaction_details.*',
                'spareparts.description as part_name',
                'spareparts.part_number',
                'spareparts.unit',
                DB::raw('COALESCE(transaction_details.proof_image_before_url, transactions.proof_image_before_url) as proof_image_before_url'),
                DB::raw('COALESCE(transaction_details.proof_image_url, transactions.proof_image_url) as proof_image_url')
            )
            ->get()
            ->groupBy('transaction_id');
        $vehicles = DB::table('vehicles')->orderBy('fleet_number', 'asc')->get();

        // --- BARU: LOGIKA PENOMORAN OTOMATIS BAST ---
        $nextBastNumber = '';
        if ($tab === 'OUT') {
            $currentYear = now()->timezone('Asia/Jakarta')->format('Y');
            
            // Cari data nomor urut terakhir di tabel baru
            $sequence = DB::table('bast_sequences')->where('year', $currentYear)->first();
            
            // Jika ada, tambah 1. Jika belum ada (tahun baru), mulai dari 1.
            $nextNumber = $sequence ? $sequence->last_number + 1 : 1;
            
            // Rangkai string sesuai format: 001/PPNLH0102/MTC/2026-S0
            $nextBastNumber = sprintf("%03d/PPNLH0102/MTC/%s-S0", $nextNumber, $currentYear);
        }

        return view('transactions.index', compact('transactions', 'transactionDetails', 'vehicles', 'tab', 'nextBastNumber'));
    }

    public function showInstallationProof(Request $request, $detail, $type)
    {
        abort_unless(in_array($type, ['before', 'after'], true), 404);

        $proofValue = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transaction_details.id', $detail)
            ->where('transactions.type', 'OUT')
            ->where('transactions.status', 'completed')
            ->value($type === 'before'
                ? DB::raw('COALESCE(transaction_details.proof_image_before_url, transactions.proof_image_before_url)')
                : DB::raw('COALESCE(transaction_details.proof_image_url, transactions.proof_image_url)'));

        $paths = [];
        if (is_string($proofValue) && $proofValue !== '') {
            $decoded = str_starts_with(trim($proofValue), '[') ? json_decode($proofValue, true) : null;
            $paths = is_array($decoded) ? array_values($decoded) : [$proofValue];
        }

        $index = max(0, (int) $request->query('index', 0));
        $proofPath = $paths[$index] ?? null;

        abort_unless($proofPath && Storage::disk('local')->exists($proofPath), 404);

        return Storage::disk('local')->response($proofPath);
    }

    // --- BARU: FUNGSI DOWNLOAD PDF BAST (BULK) ---
    public function downloadBulkGI(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|array|min:1',
            'bast_number' => 'required|string|max:100',
            'receiving_team' => 'required|string',
            'bast_date' => 'required|date',
        ]);

        // 1. Ambil Data Header Transaksi yang dicentang
        $transactions = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->leftJoin('vehicles', 'transactions.vehicle_id', '=', 'vehicles.id')
            ->select('transactions.*', 'users.name as issuer_name', 'vehicles.fleet_number')
            ->whereIn('transactions.id', $request->selected_ids)
            ->where('transactions.type', 'OUT')
            ->get();

        if ($transactions->isEmpty()) {
            abort(404, 'Transaksi tidak ditemukan atau bukan transaksi GI.');
        }

        // Ambil list unik kendaraan untuk teks paragraf BAST
        $uniqueFleets = $transactions->pluck('fleet_number')->filter()->unique()->implode(', ');
        if(empty($uniqueFleets)) {
            $uniqueFleets = '-';
        }

        // 2. Ambil Rincian Barang (Item Details) dari semua transaksi yg dipilih
        $details = DB::table('transaction_details')
            ->join('spareparts', 'transaction_details.sparepart_id', '=', 'spareparts.id')
            ->select('transaction_details.*', 'spareparts.description', 'spareparts.part_number', 'spareparts.unit')
            ->whereIn('transaction_id', $request->selected_ids)
            ->get();

        $bast_number = $request->bast_number;
        $receiving_team = $request->receiving_team;
        $date = $request->bast_date;
        
        // --- BARU: UPDATE COUNTER BAST JIKA FORMATNYA STANDAR ---
        // Mengecek apakah input user cocok persis dengan pola urutan kita (menggunakan Regex)
        // Pola: Minimal 3 digit angka, diikuti /PPNLH0102/MTC/, lalu 4 digit tahun, lalu -S0
        if (preg_match('/^(\d{3,})\/PPNLH0102\/MTC(?:-KNO)?\/(\d{4})-S0$/', $bast_number, $matches)) {
            $submittedNum = (int)$matches[1]; // Ekstrak nomor urut
            $submittedYear = (int)$matches[2]; // Ekstrak tahun

            $sequence = DB::table('bast_sequences')->where('year', $submittedYear)->first();
            $currentLastNum = $sequence ? $sequence->last_number : 0;

            // Jika nomor yang disubmit LEBIH BESAR dari yang ada di database, update database-nya
            // (Jadi kalau manual diketik mundur untuk cetak ulang, database tetap aman)
            if ($submittedNum > $currentLastNum) {
                if ($sequence) {
                    DB::table('bast_sequences')->where('year', $submittedYear)->update(['last_number' => $submittedNum]);
                } else {
                    DB::table('bast_sequences')->insert(['year' => $submittedYear, 'last_number' => $submittedNum]);
                }
            }
        }

        // 3. Render ke tampilan PDF
        $pdf = Pdf::loadView('pdf.good_issue', compact('transactions', 'details', 'bast_number', 'receiving_team', 'uniqueFleets', 'date'))
                  ->setPaper('a4', 'portrait');

        // 4. Buka di tab baru (stream) dengan nama dinamis
        return $pdf->stream('BAST_GI_' . str_replace('/', '_', $bast_number) . '.pdf');
    }

    // ... sisa fungsi lainnya tetap sama seperti milik Anda (Pending GI, GR, Check PO, dll) ...
    
    // --- FUNGSI PENDING ISSUE (GI) ---
    public function indexPending()
    {
        $rows = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('spareparts', 'transaction_details.sparepart_id', '=', 'spareparts.id')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->join('vehicles', 'transactions.vehicle_id', '=', 'vehicles.id')
            ->select(
                'transactions.id',
                'transactions.invoice_no',
                'transactions.date',
                'transactions.created_at',
                'transactions.requester_name',
                'transaction_details.sparepart_id',
                'transaction_details.qty',
                'spareparts.description as part_name',
                'spareparts.unit',
                'users.name as user_name',
                'vehicles.fleet_number'
            )
            ->where('transactions.type', 'OUT')
            ->where('transactions.status', 'pending')
            ->whereNull('transaction_details.proof_image_url')
            ->orderBy('transactions.created_at', 'desc')
            ->orderBy('transaction_details.id', 'asc')
            ->get();

        $pendingTransactions = $rows->groupBy('id')->map(function ($group) {
            $first = $group->first();
            $items = $group->groupBy('sparepart_id')->map(function ($partGroup) {
                $item = $partGroup->first();
                return (object) [
                    'sparepart_id' => $item->sparepart_id,
                    'part_name' => $item->part_name,
                    'unit' => $item->unit,
                    'qty' => $partGroup->sum('qty'),
                ];
            })->values();

            return (object) [
                'id' => $first->id,
                'invoice_no' => $first->invoice_no,
                'date' => $first->date,
                'created_at' => $first->created_at,
                'requester_name' => $first->requester_name,
                'user_name' => $first->user_name,
                'fleet_number' => $first->fleet_number,
                'items' => $items,
            ];
        })->values();

        return view('transactions.pending', compact('pendingTransactions'));
    }

    public function updatePending(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:verify,cancel',
            'sparepart_id' => 'nullable|integer',
        ]);

        $transaction = DB::table('transactions')->where('id', $id)->first();

        if (!$transaction || $transaction->status !== 'pending') {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan atau sudah diproses.');
        }

        $detailsQuery = DB::table('transaction_details')
            ->where('transaction_id', $id)
            ->whereNull('proof_image_url');

        if ($request->filled('sparepart_id')) {
            $detailsQuery->where('sparepart_id', $request->sparepart_id);
        }

        $details = $detailsQuery->orderBy('id')->get();

        if ($details->isEmpty()) {
            return redirect()->back()->with('error', 'Item transaksi tidak ditemukan atau sudah diproses.');
        }

        if ($request->action === 'verify') {
            $request->validate([
                'proofs.0.before' => 'required|array|min:1',
                'proofs.0.before.*' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
                'proofs.0.after' => 'required|array|min:1',
                'proofs.0.after.*' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            ], [
                'proofs.0.before.required' => 'Foto sebelum pemasangan wajib diunggah.',
                'proofs.0.before.*.mimes' => 'Format foto sebelum harus JPEG, PNG, JPG, atau PDF.',
                'proofs.0.after.required' => 'Foto sesudah pemasangan wajib diunggah.',
                'proofs.0.after.*.mimes' => 'Format foto sesudah harus JPEG, PNG, JPG, atau PDF.',
            ]);
        }

        try {
            DB::beginTransaction();

            if ($request->action === 'cancel') {
                if (auth()->user()->role !== 'supervisor') {
                    return redirect()->back()->with('error', 'Only Supervisor can cancel transactions.');
                }
                foreach ($details as $detail) {
                    DB::table('spareparts')->where('id', $detail->sparepart_id)->increment('current_stock', $detail->qty);
                }
                DB::table('transaction_details')->whereIn('id', $details->pluck('id'))->delete();

                $remainingCount = DB::table('transaction_details')->where('transaction_id', $id)->count();
                $remainingPending = DB::table('transaction_details')->where('transaction_id', $id)->whereNull('proof_image_url')->count();

                if ($remainingCount === 0) {
                    DB::table('transactions')->where('id', $id)->update([
                        'status'     => 'canceled',
                        'updated_at' => now()->timezone('Asia/Jakarta')
                    ]);
                } elseif ($remainingPending === 0) {
                    DB::table('transactions')->where('id', $id)->update([
                        'status'     => 'completed',
                        'updated_at' => now()->timezone('Asia/Jakarta')
                    ]);
                }
                $msg = 'Item canceled. Stock returned.';
            } else {
                $beforePaths = collect($request->file('proofs.0.before', []))
                    ->map(fn ($file) => $file->store('installations', 'local'))
                    ->values()
                    ->all();
                $afterPaths = collect($request->file('proofs.0.after', []))
                    ->map(fn ($file) => $file->store('installations', 'local'))
                    ->values()
                    ->all();

                $beforeValue = count($beforePaths) === 1 ? $beforePaths[0] : json_encode($beforePaths);
                $afterValue = count($afterPaths) === 1 ? $afterPaths[0] : json_encode($afterPaths);

                DB::table('transaction_details')
                    ->whereIn('id', $details->pluck('id'))
                    ->update([
                        'proof_image_before_url' => $beforeValue,
                        'proof_image_url' => $afterValue,
                        'updated_at' => now()->timezone('Asia/Jakarta'),
                    ]);

                $remainingPending = DB::table('transaction_details')
                    ->where('transaction_id', $id)
                    ->whereNull('proof_image_url')
                    ->count();

                if ($remainingPending === 0) {
                    DB::table('transactions')->where('id', $id)->update([
                        'status' => 'completed',
                        'updated_at' => now()->timezone('Asia/Jakarta')
                    ]);
                    $msg = 'Proofs uploaded (Before & After). Transaction completed.';
                } else {
                    $msg = 'Item proofs uploaded (Before & After). Remaining items are still pending.';
                }
            }

            DB::commit();
            return redirect()->route('transactions.pending')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    // --- BARU: FUNGSI PENDING GR (IN) ---

    /**
     * Menampilkan daftar Pending GR (Procurement tanpa PO).
     */
    public function indexPendingGR()
    {
        $pendingGR = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('spareparts', 'transaction_details.sparepart_id', '=', 'spareparts.id')
            ->select(
                'transactions.id as trx_id',
                'transactions.date',
                'transactions.created_at',
                'transactions.invoice_no',
                'transactions.source_name as vendor_name',
                'spareparts.description as item_name',
                'transaction_details.qty',
                'spareparts.unit'
            )
            ->where('transactions.type', 'IN')
            ->where('transactions.source_type', 'procurement')
            ->where('transactions.status', 'pending')
            ->orderBy('transactions.created_at', 'desc')
            ->get();

        return view('transactions.pending_gr', compact('pendingGR'));
    }

    public function processPendingGR(Request $request)
    {
        $request->validate([
            'purchase_order' => 'required|string|max:50',
            'bast_image' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'selected_ids' => 'required|array|min:1', 
            'replace_bast' => 'nullable|in:1,0', 
            'new_transaction_date' => 'nullable|date' 
        ]);

        try {
            $selectedTrx = DB::table('transactions')->whereIn('id', $request->selected_ids)->get();
            $uniqueVendors = $selectedTrx->pluck('source_name')->unique();

            if ($uniqueVendors->count() > 1) {
                return redirect()->back()->with('error', 'Gagal: Transaksi yang dipilih memiliki Vendor yang berbeda-beda. Satu PO hanya boleh untuk satu Vendor.');
            }

            $currentVendor = $uniqueVendors->first();
            $existingPO = DB::table('transactions')->where('purchase_order', $request->purchase_order)->where('type', 'IN')->first();

            if ($existingPO && $existingPO->source_name !== $currentVendor) {
                return redirect()->back()->with('error', "Vendor Tidak Cocok! PO '{$request->purchase_order}' adalah milik '{$existingPO->source_name}', sedangkan item yang dipilih dari '{$currentVendor}'.");
            }

            DB::beginTransaction();
            $bastPath = null;

            if ($existingPO) {
                if ($request->hasFile('bast_image') && $request->replace_bast == '1') {
                    if ($existingPO->proof_image_url && File::exists(public_path($existingPO->proof_image_url))) {
                        File::delete(public_path($existingPO->proof_image_url));
                    }
                    $file = $request->file('bast_image');
                    $ext = $file->getClientOriginalExtension();
                    $filename = time() . '_bast_' . Str::slug($request->purchase_order) . '.' . $ext;
                    $file->move(public_path("uploads/bast"), $filename);
                    $bastPath = "uploads/bast/" . $filename;

                    DB::table('transactions')->where('purchase_order', $request->purchase_order)->update(['proof_image_url' => $bastPath]);
                } else {
                    $bastPath = $existingPO->proof_image_url;
                }
            } else {
                if (!$request->hasFile('bast_image')) {
                    return response()->json(['status' => 'error', 'message' => 'PO not found. Please upload BAST.'], 422);
                }
                $file = $request->file('bast_image');
                $ext = $file->getClientOriginalExtension();
                $filename = time() . '_bast_' . Str::slug($request->purchase_order) . '.' . $ext;
                $file->move(public_path("uploads/bast"), $filename);
                $bastPath = "uploads/bast/" . $filename;
            }

            $updateData = [
                'purchase_order' => $request->purchase_order,
                'proof_image_url' => $bastPath,
                'status' => 'completed', 
                'updated_at' => now()->timezone('Asia/Jakarta')
            ];

            if ($request->filled('new_transaction_date')) {
                $newTimestamp = $request->new_transaction_date . ' ' . now()->timezone('Asia/Jakarta')->format('H:i:s');
                $updateData['date'] = $request->new_transaction_date;
                $updateData['created_at'] = $newTimestamp; 

                if ($existingPO) {
                    DB::table('transactions')
                        ->where('purchase_order', $request->purchase_order)
                        ->where('type', 'IN')
                        ->update(['date' => $request->new_transaction_date, 'created_at' => $newTimestamp, 'updated_at' => now()->timezone('Asia/Jakarta')]);
                }
            }

            DB::table('transactions')->whereIn('id', $request->selected_ids)->update($updateData);
            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'success', 'message' => 'Pending GR processed successfully!']);
            }
            return redirect()->route('transactions.pendingGR')->with('success', 'Pending GR processed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            return redirect()->back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function checkPO(Request $request)
    {
        $poData = DB::table('transactions')->where('purchase_order', $request->purchase_order)->where('type', 'IN')->select('source_name')->first();
        return response()->json(['exists' => (bool) $poData, 'vendor' => $poData ? $poData->source_name : null]);
    }

    // --- FORMS & API ---
    public function createGR()
    {
        $vehicles = DB::table('vehicles')->orderBy('fleet_number', 'asc')->get();
        return view('transactions.goods_receipt', compact('vehicles'));
    }

    public function createGI()
    {
        $vehicles = DB::table('vehicles')->orderBy('fleet_number', 'asc')->get();
        return view('transactions.goods_issue', compact('vehicles'));
    }

    public function searchParts(Request $request)
    {
        $query = $request->get('query');
        if (strlen($query) < 2) return response()->json([]);

        $parts = DB::table('spareparts')
            ->where('description', 'like', "%{$query}%")
            ->orWhere('part_number', 'like', "%{$query}%")
            ->select('id', 'part_number', 'description', 'unit', 'image_url', 'current_stock', 'category')
            ->limit(10)
            ->get();
        return response()->json($parts);
    }

    public function storeGR(Request $request)
    {
        $rules = [
            'date' => 'required|date',
            'source_type' => 'required|in:procurement,purchase,cannibalization,ibt',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:spareparts,id',
            'items.*.qty' => 'required|integer|min:1',
        ];

        if ($request->source_type === 'procurement') {
            $rules['source_name'] = 'required|string|max:255'; 
        } elseif ($request->source_type === 'purchase') {
            $rules['proof_image'] = 'required|file|mimes:jpeg,png,jpg,pdf|max:5120'; 
        } elseif ($request->source_type === 'cannibalization') {
            $rules['vehicle_id'] = 'required|exists:vehicles,id';
        } elseif ($request->source_type === 'ibt') {
            $rules['source_name'] = 'required|string|max:255'; 
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $proofPath = null;
            if ($request->hasFile('proof_image') && $request->source_type !== 'procurement') {
                $file = $request->file('proof_image');
                $ext = $file->getClientOriginalExtension();
                $typeFolder = $request->source_type === 'procurement' ? 'bast' : 'notes';
                $filename = time() . '_proof.' . $ext;
                $file->move(public_path("uploads/{$typeFolder}"), $filename);
                $proofPath = "uploads/{$typeFolder}/" . $filename;
            }

            $finalSourceName = $request->source_name;
            $finalVehicleId = null;

            if ($request->source_type === 'cannibalization') {
                $vehicle = DB::table('vehicles')->where('id', $request->vehicle_id)->first();
                $finalSourceName = $vehicle->fleet_number;
                $finalVehicleId = $request->vehicle_id;
            }

            $status = ($request->source_type === 'procurement') ? 'pending' : 'completed';

            $transactionId = DB::table('transactions')->insertGetId([
                'invoice_no' => 'GR-' . time(), 
                'type' => 'IN', 
                'status' => $status, 
                'date' => $request->date,
                'user_id' => auth()->id(),
                'source_type' => $request->source_type,
                'source_name' => $finalSourceName,
                'proof_image_url' => $proofPath,
                'vehicle_id' => $finalVehicleId,
                'created_at' => now()->timezone('Asia/Jakarta'),
                'updated_at' => now()->timezone('Asia/Jakarta'),
            ]);

            foreach ($request->items as $item) {
                DB::table('transaction_details')->insert([
                    'transaction_id' => $transactionId,
                    'sparepart_id' => $item['id'],
                    'qty' => $item['qty'],
                    'created_at' => now()->timezone('Asia/Jakarta'),
                    'updated_at' => now()->timezone('Asia/Jakarta'),
                ]);
                DB::table('spareparts')->where('id', $item['id'])->increment('current_stock', $item['qty']);
            }

            DB::commit();
            $msg = ($status === 'pending') ? 'GR Draft Saved (Pending BAST). Please complete in Pending GR menu.' : 'Good Receipt Transaction Successful!';
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($proofPath && File::exists(public_path($proofPath))) {
                File::delete(public_path($proofPath));
            }
            return redirect()->back()->with('error', 'Transaction Failed: ' . $e->getMessage())->withInput();
        }
    }

    public function storeGI(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'vehicle_id' => 'required|exists:vehicles,id',
            'requester_name' => 'required|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:spareparts,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $vehicle = DB::table('vehicles')->where('id', $request->vehicle_id)->first();
        $partsById = DB::table('spareparts')
            ->whereIn('id', collect($request->items)->pluck('id'))
            ->get()
            ->keyBy('id');

        foreach ($request->items as $item) {
            $part = $partsById->get($item['id']);
            if (!$part || $part->current_stock < $item['qty']) {
                $available = $part ? $part->current_stock : 0;
                $name = $part ? $part->description : 'Selected part';
                return redirect()->back()->with('error', "Part stock insufficient for {$name}! Available: {$available}")->withInput();
            }
        }

        if ($request->force_submit != '1') {
            $fiveDaysAgo = now()->timezone('Asia/Jakarta')->subDays(5)->format('Y-m-d');
            $recentTransaction = DB::table('transactions')
                ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->where('transactions.type', 'OUT')
                ->where('transactions.vehicle_id', $request->vehicle_id)
                ->whereIn('transaction_details.sparepart_id', collect($request->items)->pluck('id'))
                ->where('transactions.date', '>=', $fiveDaysAgo)
                ->whereIn('transactions.status', ['completed', 'pending'])
                ->first();

            if ($recentTransaction) {
                $msg = "This sparepart has been requested before for {$vehicle->fleet_number} in the last 5 days, are you sure you want to proceed?";
                return redirect()->back()->with('warning_5_day', $msg)->withInput();
            }
        }

        try {
            DB::beginTransaction();

            $transactionId = DB::table('transactions')->insertGetId([
                'invoice_no' => 'GI-' . time(),
                'type' => 'OUT',
                'status' => 'pending', 
                'date' => $request->date,
                'user_id' => auth()->id(),
                'vehicle_id' => $request->vehicle_id,
                'requester_name' => $request->requester_name,
                'proof_image_url' => null, 
                'created_at' => now()->timezone('Asia/Jakarta'),
                'updated_at' => now()->timezone('Asia/Jakarta'),
            ]);

            $partNames = [];
            foreach ($request->items as $item) {
                DB::table('transaction_details')->insert([
                    'transaction_id' => $transactionId,
                    'sparepart_id' => $item['id'],
                    'qty' => $item['qty'],
                    'created_at' => now()->timezone('Asia/Jakarta'),
                    'updated_at' => now()->timezone('Asia/Jakarta'),
                ]);

                DB::table('spareparts')->where('id', $item['id'])->decrement('current_stock', $item['qty']);
                $part = $partsById->get($item['id']);
                if ($part) {
                    $partNames[] = $part->description;
                }
            }

            $supervisors = User::where('role', 'supervisor')->get();
            $details = [
                'part_name' => implode(', ', $partNames),
                'fleet_number' => $vehicle->fleet_number,
                'user_name' => auth()->user()->name,
            ];

            if ($request->force_submit == '1') {
                Notification::send($supervisors, new FiveDayRuleAlert($details));
            } else {
                $details['alert_type'] = 'new_pending_gi'; 
                Notification::send($supervisors, new FiveDayRuleAlert($details));
            }

            DB::commit();
            return redirect()->back()->with('success', 'Good Issue created successfully (Status: Pending). Please upload proof later.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Transaction Failed: ' . $e->getMessage())->withInput();
        }
    }

    public function destroyPendingGR($id)
    {
        if (auth()->user()->role !== 'supervisor') {
            return redirect()->back()->with('error', 'Unauthorized action. Only Supervisors can cancel Pending GR.');
        }

        $trx = DB::table('transactions')->where('id', $id)->first();
        if (!$trx || $trx->status !== 'pending' || $trx->source_type !== 'procurement') {
            return redirect()->back()->with('error', 'Invalid transaction status or type.');
        }

        try {
            DB::beginTransaction();
            $details = DB::table('transaction_details')->where('transaction_id', $id)->get();
            foreach ($details as $item) {
                DB::table('spareparts')->where('id', $item->sparepart_id)->decrement('current_stock', $item->qty);
            }
            DB::table('transactions')->where('id', $id)->update(['status' => 'canceled', 'updated_at' => now()->timezone('Asia/Jakarta')]);
            DB::commit();
            return redirect()->back()->with('success', 'Pending GR canceled and stock reverted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to cancel: ' . $e->getMessage());
        }
    }

    public function indexPO(Request $request)
    {
        $query = DB::table('transactions')
            ->join('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->select(
                'transactions.purchase_order',
                'transactions.source_name as vendor_name',
                DB::raw('COUNT(DISTINCT transaction_details.sparepart_id) as total_items'),
                DB::raw('MAX(transactions.date) as latest_date'), 
                'transactions.proof_image_url' 
            )
            ->where('transactions.type', 'IN')
            ->where('transactions.source_type', 'procurement')
            ->whereNotNull('transactions.purchase_order')
            ->where('transactions.status', 'completed')
            ->groupBy('transactions.purchase_order', 'transactions.source_name', 'transactions.proof_image_url');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transactions.purchase_order', 'like', "%{$search}%")
                    ->orWhere('transactions.source_name', 'like', "%{$search}%");
            });
        }

        $purchaseOrders = $query->orderBy('latest_date', 'desc')->paginate(10);
        return view('transactions.po_index', compact('purchaseOrders'));
    }

    public function showPO($po_number)
    {
        $transactions = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('spareparts', 'transaction_details.sparepart_id', '=', 'spareparts.id')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->select(
                'transactions.*',
                'transaction_details.qty',
                'spareparts.description as part_name',
                'spareparts.part_number',
                'spareparts.unit',
                'users.name as user_name'
            )
            ->where('transactions.purchase_order', $po_number)
            ->where('transactions.type', 'IN')
            ->orderBy('transactions.created_at', 'desc')
            ->get();

        if ($transactions->isEmpty()) {
            return redirect()->route('transactions.po.index')->with('error', 'Purchase Order not found.');
        }

        $header = $transactions->first();
        return view('transactions.po_show', compact('transactions', 'header'));
    }
}
