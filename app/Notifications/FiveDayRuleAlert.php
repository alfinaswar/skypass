<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FiveDayRuleAlert extends Notification
{
    use Queueable;

    protected $details;

    /**
     * Create a new notification instance.
     * $details berisi info: nama mekanik, mobil, sparepart, tanggal.
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        // Default: Pelanggaran 5 Hari
        $title = 'Warning: Repeated GI within 5 days';
        $message = "Part {$this->details['part_name']} has been requested repeatedly for vehicle {$this->details['fleet_number']} within 5 days.";
        $type = 'violation';

        // Cek jika ini adalah notifikasi Pending GI normal
        if (isset($this->details['alert_type']) && $this->details['alert_type'] === 'new_pending_gi') {
            $title = 'New Pending GI Request';
            $message = "Admin {$this->details['user_name']} has issued GI on part {$this->details['part_name']} for {$this->details['fleet_number']}. Pending verification.";
            $type = 'info';
        }

        return [
            'title'   => $title,
            'message' => $message,
            'user'    => $this->details['user_name'],
            'time'    => now()->timezone('Asia/Jakarta'), 
            'type'    => $type
        ];
    }
}