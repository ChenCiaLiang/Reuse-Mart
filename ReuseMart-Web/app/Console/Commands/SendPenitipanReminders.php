<?php

namespace App\Console\Commands;

use App\Models\TransaksiPenitipan;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPenitipanReminders extends Command
{
    protected $signature = 'notifications:penitipan-reminders';
    protected $description = 'Send penitipan expiry reminders';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        // H-3 notification
        $h3Transactions = TransaksiPenitipan::with(['penitip', 'detailTransaksiPenitipan.produk'])
            ->where('statusPenitipan', 'Aktif')
            ->whereDate('tanggalAkhirPenitipan', Carbon::now()->addDays(3)->toDateString())
            ->get();

        foreach ($h3Transactions as $transaksi) {
            foreach ($transaksi->detailTransaksiPenitipan as $detail) {
                $this->notificationService->sendPenitipanExpireNotification(
                    $transaksi->penitip,
                    $detail->produk,
                    3
                );
            }
        }

        // H-0 notification
        $h0Transactions = TransaksiPenitipan::with(['penitip', 'detailTransaksiPenitipan.produk'])
            ->where('statusPenitipan', 'Aktif')
            ->whereDate('tanggalAkhirPenitipan', Carbon::now()->toDateString())
            ->get();

        foreach ($h0Transactions as $transaksi) {
            foreach ($transaksi->detailTransaksiPenitipan as $detail) {
                $this->notificationService->sendPenitipanExpireNotification(
                    $transaksi->penitip,
                    $detail->produk,
                    0
                );
            }
        }

        $this->info('Penitipan reminders sent successfully');
    }
}
