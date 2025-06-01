<?php

use Illuminate\Support\Facades\Broadcast;

// Channel untuk user spesifik
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Channel untuk penitip
Broadcast::channel('penitip.{penitipId}', function ($user, $penitipId) {
    return $user instanceof \App\Models\Penitip &&
        (int) $user->idPenitip === (int) $penitipId;
});

// Channel untuk pembeli
Broadcast::channel('pembeli.{pembeliId}', function ($user, $pembeliId) {
    return $user instanceof \App\Models\Pembeli &&
        (int) $user->idPembeli === (int) $pembeliId;
});

// Channel untuk kurir
Broadcast::channel('kurir.{kurirId}', function ($user, $kurirId) {
    return $user instanceof \App\Models\Pegawai &&
        $user->idJabatan === 6 && // ID jabatan kurir
        (int) $user->idPegawai === (int) $kurirId;
});
