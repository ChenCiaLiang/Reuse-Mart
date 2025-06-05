@extends('layouts.owner')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Laporan Penjualan Bulanan Keseluruhan</h2>
        <div class="flex space-x-3">
            <form method="GET" class="flex items-center space-x-2">
                <label for="tahun" class="text-sm font-medium text-gray-700">Tahun:</label>
                <select name="tahun" id="tahun" class="border border-gray-300 rounded-md px-3 py-1 text-sm" onchange="this.form.submit()">
                    @for ($i = date('Y'); $i >= 2020; $i--)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </form>
            <a href="{{ route('owner.laporan.penjualan-bulanan.download', ['tahun' => $tahun]) }}" 
               class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm flex items-center">
                <i class="fas fa-download mr-2"></i>
                Download PDF
            </a>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-blue-500 rounded-lg">
                    <i class="fas fa-calendar text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-600">Tahun</p>
                    <p class="text-lg font-bold text-blue-800">{{ $tahun }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-green-500 rounded-lg">
                    <i class="fas fa-box text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-600">Total Barang Terjual</p>
                    <p class="text-lg font-bold text-green-800">{{ $totalBarang }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-500 rounded-lg">
                    <i class="fas fa-money-bill-wave text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-yellow-600">Total Penjualan</p>
                    <p class="text-lg font-bold text-yellow-800">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Grafik Penjualan Bulanan</h3>
        <div class="bg-gray-50 p-4 rounded-lg">
            <canvas id="salesChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Tabel Penjualan Bulanan</h3>
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Bulan
                    </th>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Jumlah Barang Terjual
                    </th>
                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Jumlah Penjualan Kotor
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($dataFormatted as $data)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $data['bulan'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $data['jumlah_barang'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        Rp {{ number_format($data['jumlah_penjualan'], 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
                <tr class="bg-gray-100 font-bold">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        Total
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $totalBarang }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        Rp {{ number_format($totalPenjualan, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    const data = {
        labels: [
            @foreach($dataFormatted as $data)
                '{{ $data["bulan"] }}',
            @endforeach
        ],
        datasets: [{
            label: 'Penjualan (Rp)',
            data: [
                @foreach($dataFormatted as $data)
                    {{ $data['jumlah_penjualan'] }},
                @endforeach
            ],
            backgroundColor: 'rgba(34, 197, 94, 0.2)',
            borderColor: 'rgba(34, 197, 94, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    };

    const config = {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Penjualan Bulanan Tahun {{ $tahun }}'
                },
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    };

    new Chart(ctx, config);
});
</script>
@endsection