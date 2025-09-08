<?php

namespace App\Http\Controllers;

use App\Models\NatureOfCollection;
use Illuminate\Http\Request;

class DailyReportOfPaymentsController extends Controller
{
    public function index(Request $request)
    {
        // Use the provided date directly
        $date = $request->input('date', now()->toDateString()); // Default to today's date if not provided

        // Fetch nature of collections with their payments for the specified date
        $reports = NatureOfCollection::with(['payments' => function ($query) use ($date) {
            $query->whereDate('payment_date', $date);
        }])->get();

        // Format the report data and exclude entries with 0 amounts
        $formattedReports = $reports->map(function ($nature) {
            $amountDeposited = $nature->payments->sum('amount');
            if ($amountDeposited > 0) {
                return [
                    'pnp_account_name' => $nature->type,
                    'lbp_bank_account_number' => $nature->lbp_bank_account_number,
                    'amount_deposited' => $amountDeposited,
                ];
            }
        })->filter(); // Remove null entries

        // Calculate the grand total
        $grandTotal = $formattedReports->sum('amount_deposited');

        return response()->json([
            'date' => $date,
            'reports' => $formattedReports,
            'grand_total' => $grandTotal,
        ]);
    }
}