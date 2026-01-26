<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Dashboard Admin
    public function index()
    {
        // Data untuk admin
        $transactionsToday = 5;  // Jumlah transaksi
        $salesToday = 2500000;  // Penjualan hari ini
        $profitToday = 400000;  // Keuntungan hari ini
        $promoCount = 2;  // Promo diskon

        return view('dashboard', compact('transactionsToday', 'salesToday', 'profitToday', 'promoCount'));
    }

    // Dashboard Kasir
    public function kasirDashboard()
    {
        // Data untuk kasir
        $transactionsToday = 3;
        $salesToday = 1200000;
        $profitToday = 400000;  // Keuntungan hari ini
        $promoCount = 1;

        return view('dashboard_kasir', compact('transactionsToday', 'salesToday', 'promoCount'));
    }
}






