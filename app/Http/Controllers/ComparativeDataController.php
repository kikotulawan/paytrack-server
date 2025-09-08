<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class ComparativeDataController extends Controller
{
    public function compareYears(Request $request)
    {
        // Get the years to compare from the request
        $year1 = $request->input('year1', now()->year - 1); // Default to last year
        $year2 = $request->input('year2', now()->year);     // Default to current year

        // Fetch payments grouped by month and type for both years
        $dataYear1 = Payment::selectRaw('MONTH(payment_date) as month, type, SUM(amount) as total')
            ->whereYear('payment_date', $year1)
            ->groupBy('month', 'type')
            ->get();

        $dataYear2 = Payment::selectRaw('MONTH(payment_date) as month, type, SUM(amount) as total')
            ->whereYear('payment_date', $year2)
            ->groupBy('month', 'type')
            ->get();

        // Define all 12 months
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        // Initialize all months with default values
        $formattedData = [];
        foreach ($months as $monthNumber => $monthName) {
            $monthDataYear1 = $dataYear1->where('month', $monthNumber);
            $monthDataYear2 = $dataYear2->where('month', $monthNumber);

            $formattedData[] = [
                'month' => $monthName,
                'year1' => [
                    'trust_receipts' => $monthDataYear1->where('type', 'trust-receipt-funds')->sum('total') ?: 0,
                    'general_funds' => $monthDataYear1->where('type', 'general-funds')->sum('total') ?: 0,
                    'trust_liabilities' => $monthDataYear1->where('type', 'trust-liabilities')->sum('total') ?: 0,
                    'total' => $monthDataYear1->sum('total') ?: 0,
                ],
                'year2' => [
                    'trust_receipts' => $monthDataYear2->where('type', 'trust-receipt-funds')->sum('total') ?: 0,
                    'general_funds' => $monthDataYear2->where('type', 'general-funds')->sum('total') ?: 0,
                    'trust_liabilities' => $monthDataYear2->where('type', 'trust-liabilities')->sum('total') ?: 0,
                    'total' => $monthDataYear2->sum('total') ?: 0,
                ],
            ];
        }

        // Calculate overall totals for both years
        $totalsYear1 = [
            'trust_receipts' => $dataYear1->where('type', 'trust-receipt-funds')->sum('total'),
            'general_funds' => $dataYear1->where('type', 'general-funds')->sum('total'),
            'trust_liabilities' => $dataYear1->where('type', 'trust-liabilities')->sum('total'),
            'total' => $dataYear1->sum('total'),
        ];

        $totalsYear2 = [
            'trust_receipts' => $dataYear2->where('type', 'trust-receipt-funds')->sum('total'),
            'general_funds' => $dataYear2->where('type', 'general-funds')->sum('total'),
            'trust_liabilities' => $dataYear2->where('type', 'trust-liabilities')->sum('total'),
            'total' => $dataYear2->sum('total'),
        ];

        return response()->json([
            'year1' => $year1,
            'year2' => $year2,
            'monthly_comparison' => $formattedData,
            'totals' => [
                'year1' => $totalsYear1,
                'year2' => $totalsYear2,
            ],
        ]);
    }
}