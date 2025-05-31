<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Penjualan ReUseMart</title>
    @vite('resources/css/app.css')
    <style>
        @page {
            size: A5;
            margin: 15mm;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        /* Custom font untuk receipt style */
        .receipt-font {
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body class="receipt-font text-xs leading-tight m-0 p-0 text-black bg-white">
    <!-- Main Container -->
    <div class="w-full max-w-sm mx-auto border-2 border-black p-4 bg-white">
        
        <!-- Header Section -->
        <div class="text-center mb-4 border-b border-black pb-3">
            <div class="text-base font-bold mb-1">ReUse Mart</div>
            <div class="text-xs">Jl. Green Eco Park No. 456 Yogyakarta</div>
        </div>
        
        <!-- Invoice Information -->
        <div class="mb-4">
            <table class="w-full border-collapse">
                <tbody>
                    <tr>
                        <td class="py-0.5 font-bold w-32 align-top">No Nota</td>
                        <td class="py-0.5 align-top">: {{ $nomor_nota }}</td>
                    </tr>
                    <tr>
                        <td class="py-0.5 font-bold align-top">Tanggal pesan</td>
                        <td class="py-0.5 align-top">: {{ $transaksi->tanggalPesan }}</td>
                    </tr>
                    <tr>
                        <td class="py-0.5 font-bold align-top">Lunas pada</td>
                        <td class="py-0.5 align-top">: {{ $transaksi->tanggalLunas }}</td>
                    </tr>
                    @if($transaksi->idPegawai)
                        <tr>
                            <td class="py-0.5 font-bold align-top">Tanggal kirim</td>
                            <td class="py-0.5 align-top">: {{ $transaksi->tanggalKirim }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="py-0.5 font-bold align-top">Tanggal ambil</td>
                            <td class="py-0.5 align-top">: {{ $transaksi->tanggalAmbil }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Customer Information -->
        <div class="mb-4 border-b border-black pb-3">
            <div class="font-bold mb-1">Pembeli :</div>
            <div class="mb-1">{{ $transaksi->emailPembeli }} / {{ $transaksi->namaPembeli }}</div>
            <div>{{ $transaksi->alamat ?? 'Alamat tidak tersedia' }}</div>
        </div>
        
        <!-- Delivery Information -->
        <div class="mb-4 font-bold">
            Delivery: {{ $transaksi->namaPegawai ?? 'Diambil sendiri' }}
        </div>
        
        <!-- Items Table -->
        <div class="mb-4">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b-2 border-black">
                        <th class="text-left py-1 px-1 font-bold">Item</th>
                        <th class="text-right py-1 px-1 font-bold">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detail as $d)
                        <tr class="border-b border-dotted border-gray-400">
                            <td class="py-1 px-1 align-top">{{ $d->namaProduk }}</td>
                            <td class="py-1 px-1 text-right align-top">
                                {{ number_format($d->hargaJual, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Totals Section -->
        <div class="mb-4">
            <table class="w-full border-collapse">
                <tbody>
                    <tr>
                        <td class="py-0.5 w-3/5">Subtotal</td>
                        <td class="py-0.5 text-right font-bold">
                            {{ $transaksi->total }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-0.5">Ongkos Kirim</td>
                        <td class="py-0.5 text-right font-bold">
                            {{ $transaksi->ongkir }}
                        </td>
                    </tr>
                    <tr>
                        <td class="py-0.5">Total</td>
                        <td class="py-0.5 text-right font-bold">
                            {{ $transaksi->total + $transaksi->ongkir }}
                        </td>
                    </tr>
                    @if(isset($transaksi->potonganPoin) && $transaksi->potonganPoin > 0)
                        <tr>
                            <td class="py-0.5">Potongan Poin</td>
                            <td class="py-0.5 text-right font-bold">
                                - {{ number_format($transaksi->potonganPoin, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    <tr class="border-t-2 border-black pt-1">
                        <td class="py-1 text-sm font-bold">Total Bayar</td>
                        <td class="py-1 text-right text-sm font-bold">
                            @php
                                $totalBayar = ($transaksi->total + $transaksi->ongkir) - ($transaksi->potonganPoin ?? 0);
                            @endphp
                            {{ number_format($totalBayar, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Points Information -->
        <div class="mb-4 text-xs">
            @php
                $poinDasar = floor($totalBayar / 10000);
                $bonusPoin = $totalBayar > 500000 ? floor($poinDasar * 0.2) : 0;
                $totalPoinBaru = $poinDasar + $bonusPoin;
            @endphp
            <div class="mb-1">Poin dari pesanan ini: {{ $totalPoinBaru }}</div>
            <div>Total poin customer: {{ ($transaksi->poinSebelumnya ?? 0) + $totalPoinBaru }}</div>
        </div>
        
        <!-- QC Information -->
        {{-- <div class="mb-5 text-xs">
            QC oleh: {{ $transaksi->namaPegawaiQC ?? $transaksi->namaPegawai ?? 'Admin' }}
        </div> --}}
        
        <!-- Signature Area -->
        <div class="border-t border-black pt-3">
            @if($transaksi->idPegawai)
                <div class="mb-8">Diterima oleh:</div>
            @else
                <div class="mb-8">Diambil oleh:</div>
            @endif
            
            <div class="mb-4">
                (<span class="inline-block border-b border-dotted border-black w-48 mx-2"></span>)
            </div>
            
            <div>
                Tanggal: <span class="inline-block border-b border-dotted border-black w-48 ml-2"></span>
            </div>
        </div>
        
    </div>

    <!-- Print JavaScript -->
    <script>
        // Auto print when loaded (optional)
        // window.onload = function() { window.print(); }
        
        // Function to manually trigger print
        function printNota() {
            window.print();
        }
        
        // Add print button for preview (remove in production)
        document.addEventListener('DOMContentLoaded', function() {
            // Only show print button if not in print mode
            if (!window.matchMedia('print').matches) {
                const printButton = document.createElement('button');
                printButton.innerHTML = '🖨️ Print Nota';
                printButton.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600';
                printButton.onclick = printNota;
                document.body.appendChild(printButton);
            }
        });
    </script>
</body>
</html>