<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SparepartController extends Controller
{
    /**
     * Display a listing of the spareparts.
     */
    public function index(Request $request)
    {
        // 1. Subquery: Hitung total 'OUT' dalam 30 hari terakhir per barang
        // Gunakan timezone Jakarta agar konsisten dengan data transaksi
        $thirtyDaysAgo = now()->timezone('Asia/Jakarta')->subDays(30)->format('Y-m-d');

        // 2. Main Query dengan Subquery (Menggunakan Closure agar lebih aman)
        $query = DB::table('spareparts')
            ->select('spareparts.*')
            ->selectSub(function ($q) use ($thirtyDaysAgo) {
                $q->from('transaction_details')
                    ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                    ->whereColumn('transaction_details.sparepart_id', 'spareparts.id')
                    ->where('transactions.type', 'OUT') // Hanya hitung barang keluar
                    ->where('transactions.date', '>=', $thirtyDaysAgo)
                    ->whereIn('transactions.status', ['completed', 'pending']) // Hitung juga yang pending
                    ->select(DB::raw('COALESCE(SUM(qty), 0)'));
            }, 'usage_30_days');

        // Simple Search Logic
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%")
                ->orWhere('part_number', 'like', "%{$search}%");
        }

        // Get data, ordered by newest first, paginated 10 items per page
        $spareparts = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('spareparts.index', compact('spareparts'));
    }

    /**
     * Show the form for creating a new sparepart.
     */
    public function create()
    {
        return view('spareparts.create');
    }

    /**
     * Store a newly created sparepart in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate Input (Strict Validation)
        $request->validate([
            'description'      => 'required|string|max:255',
            'category'         => 'required|in:consumable,rotating',
            // Part number is required ONLY if category is 'rotating'. 
            // If consumable, it can be null.
            'part_number'      => 'exclude_if:category,consumable|required|string|unique:spareparts,part_number',
            'unit'             => 'required|string|max:20',
            'rack_location'    => 'required|string|max:20',
            
            // Settings Data
            'current_stock'    => 'required|integer|min:0',
            'min_stock'        => 'required|integer|min:0',
            'limit_high_usage' => 'required|integer|min:0', // Default 10 from UI
            'limit_medium_usage'=> 'required|integer|min:0', // Default 7 from UI
            
            // Image is MANDATORY during creation
            'image'            => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            // 2. Handle Image Upload First
            $imagePath = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                
                // Generate Unique Random Name
                $filename = time() . '_' . Str::random(8) . '.' . $extension; 
                
                // Move file to public/uploads/spareparts
                $file->move(public_path('uploads/spareparts'), $filename);
                
                // Save the path for database
                $imagePath = 'uploads/spareparts/' . $filename;
            }

            // 3. Insert Data into Database (Single Step)
            DB::table('spareparts')->insert([
                'category'           => $request->category,
                'part_number'        => $request->category === 'consumable' ? null : $request->part_number,
                'description'        => $request->description,
                'unit'               => $request->unit,
                'rack_location'      => $request->rack_location,
                'min_stock'          => $request->min_stock,
                'current_stock'      => $request->current_stock,
                'limit_high_usage'   => $request->limit_high_usage,
                'limit_medium_usage' => $request->limit_medium_usage,
                'image_url'          => $imagePath, 
                'created_at'         => now()->timezone('Asia/Jakarta'),
                'updated_at'         => now()->timezone('Asia/Jakarta'),
            ]);

            // 4. Redirect with success message
            return redirect()->back()
                            ->with('success', 'Sparepart successfully added! Please proceed.');

        } catch (\Exception $e) {
            // If database insert fails, we should delete the uploaded image to keep folder clean
            if (isset($imagePath) && File::exists(public_path($imagePath))) {
                File::delete(public_path($imagePath));
            }
            
            // Redirect back with error message
            return redirect()->back()
                            ->with('error', 'Failed to save data: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Display the specified sparepart details & history.
     */
    public function show($id)
    {
        // 1. Ambil Data Sparepart
        $sparepart = DB::table('spareparts')->where('id', $id)->first();

        if (!$sparepart) {
            return redirect()->route('spareparts.index')->with('error', 'Sparepart not found.');
        }

        // 2. Ambil Riwayat Transaksi KHUSUS sparepart ini
        // Kita join tabel transactions, users, dan vehicles untuk data lengkap
        $transactions = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->leftJoin('vehicles', 'transactions.vehicle_id', '=', 'vehicles.id')
            ->select(
                'transactions.date',
                'transactions.invoice_no',
                'transactions.type',
                'transactions.status',
                'transactions.source_name',     // Untuk GR (Vendor)
                'transactions.source_type',     // Untuk GR
                'transactions.requester_name',  // Untuk GI (Mekanik)
                'transaction_details.qty',
                'users.name as admin_name',
                'vehicles.fleet_number'
            )
            ->where('transaction_details.sparepart_id', $id)
            ->orderBy('transactions.date', 'desc')
            ->orderBy('transactions.created_at', 'desc')
            ->paginate(7); // Limit 7 per halaman sesuai request

        return view('spareparts.show', compact('sparepart', 'transactions'));
    }

    /**
     * Show the form for editing the specified sparepart.
     */
    public function edit($id)
    {
        // --- SECURITY CHECK: SUPERVISOR ONLY ---
        if (auth()->user()->role !== 'supervisor') {
            return redirect()->route('spareparts.index')
                             ->with('error', 'Unauthorized access. Only Supervisors can edit data.');
        }
        // ---------------------------------------

        $sparepart = DB::table('spareparts')->where('id', $id)->first();

        if (!$sparepart) {
            return redirect()->route('spareparts.index')->with('error', 'Sparepart not found.');
        }

        return view('spareparts.edit', compact('sparepart'));
    }

    /**
     * Update the specified sparepart in storage.
     */
    public function update(Request $request, $id)
    {
        // --- SECURITY CHECK: SUPERVISOR ONLY ---
        if (auth()->user()->role !== 'supervisor') {
            return redirect()->route('spareparts.index')
                             ->with('error', 'Unauthorized action. Only Supervisors can update data.');
        }
        // ---------------------------------------

        // 1. Validate Input
        $request->validate([
            'description'      => 'required|string|max:255',
            'category'         => 'required|in:consumable,rotating',
            // Unique check must ignore current ID
            'part_number'      => 'exclude_if:category,consumable|required|string|unique:spareparts,part_number,'.$id,
            'unit'             => 'required|string|max:20',
            'rack_location'    => 'required|string|max:20',
            'current_stock'    => 'required|integer|min:0',
            'min_stock'        => 'required|integer|min:0',
            'limit_high_usage' => 'required|integer|min:0',
            'limit_medium_usage'=> 'required|integer|min:0',
            // Image is optional during update
            'image'            => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $oldData = DB::table('spareparts')->where('id', $id)->first();
            
            // Keep old image path by default
            $imagePath = $oldData->image_url;

            // 2. Handle Image Upload (If new image provided)
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($oldData->image_url && File::exists(public_path($oldData->image_url))) {
                    File::delete(public_path($oldData->image_url));
                }

                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                
                // Generate Unique Random Name
                $filename = time() . '_' . Str::random(8) . '.' . $extension;
                
                $file->move(public_path('uploads/spareparts'), $filename);
                $imagePath = 'uploads/spareparts/' . $filename;
            }

            // 3. Update Database
            DB::table('spareparts')->where('id', $id)->update([
                'category'           => $request->category,
                'part_number'        => $request->category === 'consumable' ? null : $request->part_number,
                'description'        => $request->description,
                'unit'               => $request->unit,
                'rack_location'      => $request->rack_location,
                'min_stock'          => $request->min_stock,
                'current_stock'      => $request->current_stock,
                'limit_high_usage'   => $request->limit_high_usage,
                'limit_medium_usage' => $request->limit_medium_usage,
                'image_url'          => $imagePath,
                'updated_at'         => now()->timezone('Asia/Jakarta'),
            ]);

            return redirect()->route('spareparts.index')
                            ->with('success', 'Sparepart updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                            ->with('error', 'Failed to update data: ' . $e->getMessage())
                            ->withInput();
        }
    }

    /**
     * Remove the specified sparepart from storage.
     */
    public function destroy($id)
    {
        // --- SECURITY CHECK: SUPERVISOR ONLY ---
        if (auth()->user()->role !== 'supervisor') {
            return redirect()->route('spareparts.index')
                             ->with('error', 'Unauthorized action. Only Supervisors can delete data.');
        }
        // ---------------------------------------

        $sparepart = DB::table('spareparts')->where('id', $id)->first();

        if ($sparepart) {
            // Delete physical image file
            if ($sparepart->image_url && File::exists(public_path($sparepart->image_url))) {
                File::delete(public_path($sparepart->image_url));
            }

            // Delete record from DB
            DB::table('spareparts')->where('id', $id)->delete();
        }

        return redirect()->route('spareparts.index')
                        ->with('success', 'Sparepart deleted successfully!');
    }
}