<?php
// app/Http/Controllers/Api/TopSellerController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TopSeller;
use App\Models\Penitip;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class TopSellerController extends Controller
{
    /**
     * Get current active top seller
     */
    public function getCurrentTopSeller(): JsonResponse
    {
        try {
            $topSeller = TopSeller::with('penitip')
                ->where('tanggal_selesai', '>=', Carbon::now())
                ->first();

            if (!$topSeller) {
                return response()->json([
                    'success' => true,
                    'message' => 'No active top seller at the moment',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Top seller found',
                'data' => [
                    'id_top_seller' => $topSeller->idTopSeller,
                    'penitip' => [
                        'id' => $topSeller->penitip->idPenitip,
                        'nama' => $topSeller->penitip->nama,
                        'email' => $topSeller->penitip->email,
                        'rating' => $topSeller->penitip->rating,
                        'total_saldo' => $topSeller->penitip->saldo,
                        'total_bonus' => $topSeller->penitip->bonus,
                    ],
                    'periode' => [
                        'tanggal_mulai' => $topSeller->tanggal_mulai->format('Y-m-d'),
                        'tanggal_selesai' => $topSeller->tanggal_selesai->format('Y-m-d'),
                        'sisa_hari' => Carbon::now()->diffInDays($topSeller->tanggal_selesai, false),
                        'is_active' => Carbon::now()->isBefore($topSeller->tanggal_selesai)
                    ],
                    'badge' => [
                        'title' => 'TOP SELLER',
                        'description' => 'Penitip Terbaik Bulan Ini',
                        'color' => '#FFD700', // Gold color
                        'icon' => '🏆'
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving top seller data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if specific penitip is current top seller
     */
    public function checkTopSellerStatus(Request $request, $idPenitip): JsonResponse
    {
        try {
            // Validate penitip exists
            $penitip = Penitip::find($idPenitip);
            if (!$penitip) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penitip not found'
                ], 404);
            }

            $topSeller = TopSeller::with('penitip')
                ->where('tanggal_selesai', '>=', Carbon::now())
                ->where('idPenitip', $idPenitip)
                ->first();

            $isTopSeller = !is_null($topSeller);

            $response = [
                'success' => true,
                'message' => $isTopSeller ? 'User is current top seller' : 'User is not top seller',
                'data' => [
                    'penitip' => [
                        'id' => $penitip->idPenitip,
                        'nama' => $penitip->nama,
                        'email' => $penitip->email
                    ],
                    'is_top_seller' => $isTopSeller,
                    'badge_info' => $isTopSeller ? [
                        'title' => 'TOP SELLER',
                        'description' => 'Anda adalah penitip terbaik bulan ini!',
                        'color' => '#FFD700',
                        'icon' => '🏆',
                        'periode' => [
                            'tanggal_mulai' => $topSeller->tanggal_mulai->format('Y-m-d'),
                            'tanggal_selesai' => $topSeller->tanggal_selesai->format('Y-m-d'),
                            'sisa_hari' => Carbon::now()->diffInDays($topSeller->tanggal_selesai, false)
                        ]
                    ] : null
                ]
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking top seller status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top seller history (optional)
     */
    public function getTopSellerHistory(Request $request): JsonResponse
    {
        try {
            $limit = $request->query('limit', 10);
            
            $topSellers = TopSeller::with('penitip')
                ->orderBy('tanggal_mulai', 'desc')
                ->limit($limit)
                ->get();

            $history = $topSellers->map(function ($topSeller) {
                return [
                    'id_top_seller' => $topSeller->idTopSeller,
                    'penitip' => [
                        'id' => $topSeller->penitip->idPenitip,
                        'nama' => $topSeller->penitip->nama,
                        'email' => $topSeller->penitip->email
                    ],
                    'periode' => [
                        'tanggal_mulai' => $topSeller->tanggal_mulai->format('Y-m-d'),
                        'tanggal_selesai' => $topSeller->tanggal_selesai->format('Y-m-d'),
                        'bulan' => $topSeller->tanggal_mulai->format('F Y'),
                        'is_active' => Carbon::now()->isBefore($topSeller->tanggal_selesai)
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Top seller history retrieved successfully',
                'data' => $history,
                'count' => $history->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving top seller history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top seller stats for dashboard
     */
    public function getTopSellerStats(): JsonResponse
    {
        try {
            $currentTopSeller = TopSeller::with('penitip')
                ->where('tanggal_selesai', '>=', Carbon::now())
                ->first();

            $totalTopSellers = TopSeller::distinct('idPenitip')->count();
            $currentMonth = Carbon::now()->format('F Y');
            
            $stats = [
                'current_top_seller' => $currentTopSeller ? [
                    'nama' => $currentTopSeller->penitip->nama,
                    'periode' => $currentTopSeller->tanggal_mulai->format('F Y'),
                    'sisa_hari' => Carbon::now()->diffInDays($currentTopSeller->tanggal_selesai, false)
                ] : null,
                'total_unique_top_sellers' => $totalTopSellers,
                'current_period' => $currentMonth,
                'has_active_top_seller' => !is_null($currentTopSeller)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Top seller stats retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving top seller stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}