<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class CashReceiptRegisterController extends Controller
{
    public function getDailyReport(Request $request)
    {
        // Get the date from the request or default to today's date
        $date = $request->input('date', now()->toDateString());
        $type = $request->input('type'); // Optional payment type filter

        // Fetch payments for the specified date
        $query = Payment::with(['user.info', 'natureOfCollection'])
            ->whereDate('payment_date', $date);

        if ($type) {
            $query->where('type', $type); // Apply type filter if provided
        }

        $payments = $query->get();

        // Format the data for the report
        $rows = $payments->map(function ($payment) {
            return [
                'date' => $payment->payment_date->format('d-M-Y'),
                'OR' => $payment->or, // Official Receipt Number
                'payor_name' => $payment->user->info->full_name ?? '-', // Full name from user->info
                'nature_of_collection' => $payment->natureOfCollection->type ?? '-',
                'amount' => $payment->amount,
            ];
        });

        // Calculate the total deposit for the day
        $totalDepositOfDay = [
            'date' => $date,
            'total' => $rows->sum('amount'),
        ];

        return response()->json([
            'rows' => $rows,
            'totalDepositOfDay' => $totalDepositOfDay,
        ]);
    }

    public function getMonthlyReport(Request $request)
    {
        // Get the month and year from the request or default to the current month and year
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $type = $request->input('type'); // Optional payment type filter

        // Fetch payments for the specified month and year
        $query = Payment::with(['user.info', 'natureOfCollection'])
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month);

        if ($type) {
            $query->where('type', $type); // Apply type filter if provided
        }

        $payments = $query->get();

        // Group payments by date and sort by date
        $groupedPayments = $payments->groupBy(function ($payment) {
            return $payment->payment_date->format('d-M-Y');
        })->sortKeys(); // Sort by date

        // Format the data for the report
        $rows = $groupedPayments->map(function ($payments, $date) {
            return [
                'date' => $date,
                'transactions' => $payments->map(function ($payment) {
                    return [
                        'OR' => $payment->or, // Official Receipt Number
                        'payor_name' => $payment->payor_name ?? '-', // Full name from user->info
                        'nature_of_collection' => $payment->natureOfCollection->type ?? '-',
                        'amount' => $payment->amount,
                    ];
                }),
                'daily_total' => $payments->sum('amount'), // Total amount for the day
            ];
        });

        // Calculate the total deposit for the month
        $totalDepositOfMonth = [
            'month' => $month,
            'year' => $year,
            'total' => $payments->sum('amount'),
        ];

        return response()->json([
            'rows' => $rows,
            'totalDepositOfMonth' => $totalDepositOfMonth,
        ]);
    }
}