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

        // Group payments by account_name and exclude entries with 0 amount_deposited
        $groupedReports = $reports->groupBy('account_name')->map(function ($group) {
            $amountDeposited = $group->flatMap(function ($nature) {
                return $nature->payments;
            })->sum('amount');

            if ($amountDeposited > 0) {
                return [
                    'pnp_account_name' => $group->first()->account_name,
                    'lbp_bank_account_number' => $group->first()->lbp_bank_account_number,
                    'amount_deposited' => $amountDeposited,
                ];
            }
        })->filter(); // Remove null entries

        // Calculate the grand total
        $grandTotal = $groupedReports->sum('amount_deposited');

        return response()->json([
            'date' => $date,
            'reports' => $groupedReports->values(), // Convert to array for JSON response
            'grand_total' => $grandTotal,
        ]);
    }
}