<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // Get dashboard analytics
    public function index()
    {
        // Total users
        $totalUsers = User::count();

        // Total payments
        $totalPayments = Payment::whereNotNull('deposit_date')->sum('amount');

        // Total pending to deposit
        $totalPendingToDeposit = Payment::whereNull('deposit_date')->sum('amount');

        // Total payments by type
        $paymentsByType = Payment::selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->get();

        // Recent payments (last 5)
        $recentPayments = Payment::with('user.info')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // Total payments by nature of collection
        $paymentsByNature = Payment::selectRaw('nature_of_collection, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('nature_of_collection')
            ->get();

        // Total payments by mode of payment
        $paymentsByMode = Payment::selectRaw('mode_of_payment, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('mode_of_payment')
            ->get();

        // Monthly payments (last 12 months)
        $monthlyPayments = Payment::selectRaw('MONTH(payment_date) as month, SUM(amount) as total')
            ->whereNotNull('payment_date')
            ->groupBy('month')
            ->orderBy('month')
            ->take(12)
            ->get();

        // Yearly payments (last 5 years)
        $yearlyPayments = Payment::selectRaw('YEAR(payment_date) as year, SUM(amount) as total')
            ->whereNotNull('payment_date')
            ->groupBy('year')
            ->orderBy('year')
            ->take(5)
            ->get();

        return response()->json([
            'total_users' => $totalUsers,
            'total_payments' => $totalPayments,
            'total_pending_to_deposit' => $totalPendingToDeposit,
            'payments_by_type' => $paymentsByType,
            'recent_payments' => $recentPayments,
            'payments_by_nature' => $paymentsByNature,
            'payments_by_mode' => $paymentsByMode,
            'monthly_payments' => $monthlyPayments,
            'yearly_payments' => $yearlyPayments,
        ]);
    }
}