<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class WarehouseController extends Controller
{
    /**
     * Menampilkan halaman layout gudang.
     */
    public function layout()
    {
        $directory = public_path('uploads/layout');
        $layoutUrl = null;

        // Jika folder ada, cari gambar di dalamnya
        if (File::exists($directory)) {
            $files = File::files($directory);
            if (count($files) > 0) {
                // Ambil file pertama yang ditemukan
                $layoutUrl = 'uploads/layout/' . $files[0]->getFilename();
            }
        }

        return view('warehouse.layout', compact('layoutUrl'));
    }

    /**
     * Memperbarui layout gudang (Hanya Supervisor).
     */
    public function updateLayout(Request $request)
    {
        // Keamanan Lapis 2: Tolak jika bukan supervisor
        if (auth()->user()->role !== 'supervisor') {
            return redirect()->back()->with('error', 'Unauthorized action. Only supervisors can upload layout.');
        }

        $request->validate([
            'layout_image' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Maks 5MB
        ]);

        $directory = public_path('uploads/layout');

        try {
            // 1. Buat folder "layout" jika belum ada
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            } else {
                // 2. Jika sudah ada, hapus semua file lama di folder tersebut 
                // agar server tidak penuh dengan file layout jadul
                File::cleanDirectory($directory);
            }

            // 3. Simpan gambar baru
            $file = $request->file('layout_image');
            $filename = 'warehouse_layout_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);

            return redirect()->route('warehouse.layout')->with('success', 'Warehouse layout uploaded successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to upload layout: ' . $e->getMessage());
        }
    }
}