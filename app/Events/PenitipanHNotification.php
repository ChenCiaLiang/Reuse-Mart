<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PenitipanHariHNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $idPenitip;
    public $title;
    public $message;
    public $data;

    public function __construct($idPenitip, $produk_names)
    {
        $this->idPenitip = $idPenitip;
        $this->title = "Masa Penitipan Berakhir";
        $this->message = "Hari ini masa penitipan berakhir untuk: " . implode(', ', $produk_names);
        $this->data = [
            'type' => 'penitipan_hari_h',
            'idPenitip' => $idPenitip,
            'produk_names' => $produk_names,
            'created_at' => now()->toISOString()
        ];
    }

    public function broadcastOn()
    {
        return new Channel('penitip.' . $this->idPenitip);
    }

    public function broadcastAs()
    {
        return 'penitipan.hariH';
    }
}
