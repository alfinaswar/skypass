<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request)
    {
        // 1. Summary Cards Data
        $totalItems = DB::table('spareparts')->count();

        $criticalStockCount = DB::table('spareparts')
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->count();

        // Count Pending GI (Barang Keluar)
        $pendingIssuesCount = DB::table('transactions')
            ->where('type', 'OUT')
            ->where('status', 'pending')
            ->count();

        // Count Pending GR (Barang Masuk Procurement) - NEW
        $pendingGRCount = DB::table('transactions')
            ->where('type', 'IN')
            ->where('source_type', 'procurement')
            ->where('status', 'pending')
            ->count();

        // 2. Critical Stock Table (Limit 5 items)
        $criticalItems = DB::table('spareparts')
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->orderBy('current_stock', 'asc')
            ->limit(5)
            ->get();

        // 3. Pending Issues Table (Limit 5 items)
        $pendingIssues = DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->join('vehicles', 'transactions.vehicle_id', '=', 'vehicles.id')
            ->select('transactions.*', 'users.name as requester', 'vehicles.fleet_number')
            ->where('transactions.type', 'OUT')
            ->where('transactions.status', 'pending')
            ->orderBy('transactions.created_at', 'desc')
            ->limit(5)
            ->get();

        // 4. Recent Transactions (Union GR & GI, Limit 7)
        // We join with details to get the Item Name
        $recentTransactions = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('spareparts', 'transaction_details.sparepart_id', '=', 'spareparts.id')
            ->select(
                'transactions.created_at',
                'transactions.type',
                'transactions.invoice_no',
                'transactions.status',
                'spareparts.description as item_name',
                'transaction_details.qty',
                'spareparts.unit'
            )
            // Show all completed/canceled/pending transactions
            ->orderBy('transactions.created_at', 'desc')
            ->limit(7)
            ->get();

        // 5. Monthly Transaction Chart Data (UPDATED: With Month Filter)
        
        // A. Tentukan Bulan yang Dipilih (Default: Bulan Ini)
        $selectedMonth = $request->get('month', now()->timezone('Asia/Jakarta')->format('Y-m')); // Format: YYYY-MM
        $startOfMonth = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // B. Generate List Bulan untuk Dropdown (Dari transaksi pertama s/d sekarang)
        $firstTransaction = DB::table('transactions')->orderBy('date', 'asc')->first();
        $startDate = $firstTransaction ? Carbon::parse($firstTransaction->date)->startOfMonth() : now()->startOfMonth();
        $endDate = now()->startOfMonth();
        
        $availableMonths = [];
        // Loop dari bulan sekarang mundur ke bulan pertama transaksi
        while ($endDate->gte($startDate)) {
            $value = $endDate->format('Y-m');
            $label = $endDate->format('F Y');
            $availableMonths[$value] = $label;
            $endDate->subMonth();
        }

        // C. Get all dates in selected month (Sumbu X)
        $dates = [];
        $period = \Carbon\CarbonPeriod::create($startOfMonth, $endOfMonth);
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        // D. Query IN transactions (GR) for Selected Month
        $inTransactions = DB::table('transactions')
            ->where('type', 'IN')
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->select(DB::raw('date(date) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // E. Query OUT transactions (GI) for Selected Month
        $outTransactions = DB::table('transactions')
            ->where('type', 'OUT')
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->select(DB::raw('date(date) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // F. Format data for Chart.js
        $chartDates = [];
        $chartDataIn = [];
        $chartDataOut = [];

        foreach ($dates as $date) {
            $chartDates[] = Carbon::parse($date)->format('d'); // Format: 01, 02...
            $chartDataIn[] = $inTransactions[$date] ?? 0;
            $chartDataOut[] = $outTransactions[$date] ?? 0;
        }

        // Tambahkan variable baru ke compact
        return view('dashboard', compact(
            'totalItems', 
            'criticalStockCount', 
            'pendingIssuesCount',
            'pendingGRCount',
            'criticalItems',
            'pendingIssues',
            'recentTransactions',
            'chartDates',
            'chartDataIn',
            'chartDataOut',
            'availableMonths', // <--- BARU
            'selectedMonth'    // <--- BARU
        ));
    }
}