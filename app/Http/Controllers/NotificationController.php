<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Tandai semua notifikasi user saat ini sebagai sudah dibaca.
     */
    public function markAllRead()
    {
        // Fitur bawaan Laravel untuk menandai semua sebagai 'read'
        auth()->user()->unreadNotifications->markAsRead();

        // Kembali ke halaman sebelumnya
        return back();
    }
    public function clearAll()
    {
        auth()->user()->notifications()->delete();
        return back()->with('success', 'All notifications cleared.');
    }
}