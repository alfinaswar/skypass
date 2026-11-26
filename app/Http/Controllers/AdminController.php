<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use ZipArchive;

class AdminController extends Controller
{
    /**
     * Tampilkan halaman menu Admin Tools dengan Tab.
     */
    public function index(Request $request)
    {
        // SECURITY CHECK: Hanya Supervisor
        if (auth()->user()->role !== 'supervisor') {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $tab = $request->get('tab', 'users'); // Default tab: users

        $users = [];
        $transactions = [];
        $vehicles = []; // Variabel baru untuk tab vehicles

        if ($tab === 'users') {
            // Ambil semua user KECUALI yang sedang login (diri sendiri) & Supervisor lain (opsional)
            $users = DB::table('users')
                ->where('role', '!=', 'supervisor') // Hanya tampilkan staff/admin biasa
                ->orderBy('created_at', 'desc')
                ->get();
        } 
        elseif ($tab === 'transactions') {
            // Ambil list transaksi untuk dihapus
            $query = DB::table('transactions');

            // Filter Good Issue (GI) / Good Receipt (GR) berdasarkan prefix invoice_no
            if ($request->get('trx_type') === 'GI') {
                $query->where('invoice_no', 'like', 'GI%');
            } elseif ($request->get('trx_type') === 'GR') {
                $query->where('invoice_no', 'like', 'GR%');
            }
            
            // Search logic untuk transaksi
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', "%{$search}%")
                      ->orWhere('purchase_order', 'like', "%{$search}%");
                });
            }

            $transactions = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        }
        elseif ($tab === 'vehicles') {
            // LOGIKA BARU: Ambil semua kendaraan
            $vehicles = DB::table('vehicles')->orderBy('fleet_number', 'asc')->get();
        }

        return view('admin.index', compact('tab', 'users', 'transactions', 'vehicles'));
    }

    // --- TAB 1: USER MANAGER ---

    public function storeUser(Request $request)
    {
        if (auth()->user()->role !== 'supervisor') abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:6|confirmed', 
        ]);

        DB::table('users')->insert([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'admin', // Default role admin gudang
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.index', ['tab' => 'users'])->with('success', 'New Admin user created successfully.');
    }

    public function destroyUser($id)
    {
        if (auth()->user()->role !== 'supervisor') abort(403);

        $user = DB::table('users')->where('id', $id)->first();
        if ($user && $user->role !== 'supervisor') {
            // Cek apakah user memiliki riwayat transaksi di sistem
            $hasTransactions = DB::table('transactions')->where('user_id', $id)->exists();
            if ($hasTransactions) {
                return redirect()->back()->with('error', 'Cannot delete user because they have associated transactions. To deactivate, simply change their password.');
            }

            DB::table('users')->where('id', $id)->delete();
            return redirect()->route('admin.index', ['tab' => 'users'])->with('success', 'User deleted.');
        }

        return redirect()->back()->with('error', 'Cannot delete this user.');
    }

    public function updateUserPassword(Request $request, $id)
    {
        if (auth()->user()->role !== 'supervisor') abort(403);

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        DB::table('users')->where('id', $id)->update([
            'password'   => Hash::make($request->password),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.index', ['tab' => 'users'])
            ->with('success', "Password for {$user->name} ({$user->username}) has been updated successfully.");
    }

    // --- TAB 4: MANAGE VEHICLES (BARU) ---

    public function storeVehicle(Request $request)
    {
        if (auth()->user()->role !== 'supervisor') abort(403);

        $request->validate([
            'fleet_number' => 'required|string|max:20|unique:vehicles,fleet_number',
        ]);

        DB::table('vehicles')->insert([
            'fleet_number' => strtoupper($request->fleet_number), // Paksa huruf besar biar rapi
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.index', ['tab' => 'vehicles'])->with('success', 'Vehicle added successfully.');
    }

    public function destroyVehicle($id)
    {
        if (auth()->user()->role !== 'supervisor') abort(403);

        try {
            DB::table('vehicles')->where('id', $id)->delete();
            return redirect()->route('admin.index', ['tab' => 'vehicles'])->with('success', 'Vehicle deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Menangani error jika mobil sudah pernah dipakai transaksi (Foreign Key Constraint)
            if($e->getCode() == "23000"){
                return redirect()->back()->with('error', 'Cannot delete vehicle because it is associated with existing transactions.');
            }
            return redirect()->back()->with('error', 'Failed to delete vehicle: ' . $e->getMessage());
        }
    }

    // --- TAB 2: TRANSACTION REMOVAL ---

    public function destroyTransaction($id)
    {
        if (auth()->user()->role !== 'supervisor') abort(403);

        $trx = DB::table('transactions')->where('id', $id)->first();

        if ($trx) {
            // 1. Hapus Gambar Bukti "After" / BAST (`proof_image_url`)
            if ($trx->proof_image_url && File::exists(public_path($trx->proof_image_url))) {
                $isSharedFile = DB::table('transactions')
                    ->where('proof_image_url', $trx->proof_image_url)
                    ->where('id', '!=', $trx->id)
                    ->exists();

                if (!$isSharedFile) {
                    File::delete(public_path($trx->proof_image_url));
                }
            }

            // 2. Hapus Gambar Bukti "Before" (`proof_image_before_url`) [BARU]
            if ($trx->proof_image_before_url && File::exists(public_path($trx->proof_image_before_url))) {
                $isSharedBefore = DB::table('transactions')
                    ->where('proof_image_before_url', $trx->proof_image_before_url)
                    ->where('id', '!=', $trx->id)
                    ->exists();

                if (!$isSharedBefore) {
                    File::delete(public_path($trx->proof_image_before_url));
                }
            }

            // 3. Hapus Detail Transaksi
            DB::table('transaction_details')->where('transaction_id', $id)->delete();

            // 4. Hapus Header
            DB::table('transactions')->where('id', $id)->delete();

            return redirect()->route('admin.index', ['tab' => 'transactions'])
                             ->with('success', 'Transaction data permanently deleted.');
        }

        return redirect()->back()->with('error', 'Transaction not found.');
    }

    // --- TAB 3: RESTORE BACKUP ---

    public function restoreBackup(Request $request)
    {
            if (auth()->user()->role !== 'supervisor') abort(403);

            $request->validate([
                'backup_file' => 'required|file|mimes:zip|max:51200', // Max 50MB
            ]);

            $zipPath = $request->file('backup_file')->path();
            $zip = new ZipArchive;

            if ($zip->open($zipPath) === TRUE) {
                // Buat folder transit sementara di storage
                $tempDir = storage_path('app/temp_restore_' . time());
                File::makeDirectory($tempDir, 0755, true, true);
                
                try {
                    // 1. Ekstrak ke folder transit
                    $zip->extractTo($tempDir);
                    $zip->close();

                    // 2. Pindahkan Database ke lokasi yang benar (database/database.sqlite)
                    $tempDbPath = $tempDir . '/database.sqlite';
                    if (File::exists($tempDbPath)) {
                        File::copy($tempDbPath, database_path('database.sqlite'));
                    }

                    // 3. Pindahkan folder uploads ke public/uploads
                    $tempUploadsPath = $tempDir . '/uploads';
                    if (File::exists($tempUploadsPath)) {
                        File::copyDirectory($tempUploadsPath, public_path('uploads'));
                    }

                    // 4. Bersihkan folder transit
                    File::deleteDirectory($tempDir);

                    return redirect()->route('admin.index', ['tab' => 'backup'])
                                    ->with('success', 'System Restored Successfully! All database records and images have been recovered.');
                } catch (\Exception $e) {
                    // Jika error, bersihkan folder transit
                    if (File::exists($tempDir)) {
                        File::deleteDirectory($tempDir);
                    }
                    return redirect()->back()->with('error', 'Failed to extract backup: ' . $e->getMessage());
                }
            } else {
                return redirect()->back()->with('error', 'Invalid ZIP file.');
            }
    }
}