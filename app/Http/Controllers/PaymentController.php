<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Routing\Controller;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // List all payments with optional pagination and search
    public function index(Request $request)
    {
        $query = Payment::with(['user.info'])
            ->where('type', $request->input('type'))
            ->orderBy('id', 'desc'); // Sort by id in descending order

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('or', 'like', "%{$search}%")
                  ->orWhere('payor_name', 'like', "%{$search}%")
                  ->orWhere('nature_of_collection', 'like', "%{$search}%")
                  // Search by user's email
                  ->orWhereHas('user', function ($userQ) use ($search) {
                      $userQ->where('email', 'like', "%{$search}%")
                            // Search by user's info full_name
                            ->orWhereHas('info', function ($infoQ) use ($search) {
                                $infoQ->whereRaw("CONCAT_WS(' ', firstname, middlename, lastname, suffix) like ?", ["%{$search}%"]);
                            });
                  });
            });
        }

        $perPage = $request->input('per_page', 20);
        
        activity()
            ->causedBy(auth()->user()->info)
            ->log('Viewed trust receipt funds payments list');

        return response()->json($query->paginate($perPage));
    }

    // Store a new payment
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'or' => 'required|numeric',
            'payor_name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'mode_of_payment' => 'nullable|string|max:255',
            'reference' => 'required|string|max:255|unique:payments,reference',
            'nature_of_collection' => 'required|string|max:255',
        ]);

        $payment = Payment::create($request->only([
            'user_id',
            'amount',
            'or',
            'payor_name',
            'payment_date',
            'deposit_date',
            'mode_of_payment',
            'reference',
            'description',
            'nature_of_collection',
            'type',
        ]));

        activity()
            ->causedBy(auth()->user()->info)
            ->performedOn($payment)
            ->log('Created payment ' . $payment->reference);

        return response()->json($payment, 201);
    }

    // Show a specific payment
    public function show($id)
    {
        $payment = Payment::findOrFail($id);

        activity()
            ->causedBy(auth()->user()->info)
            ->performedOn($payment)
            ->log('Viewed payment ' . $payment->reference);

        return response()->json($payment);
    }

    // Update a payment
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'or' => 'sometimes|required|numeric',
            'payor_name' => 'sometimes|required|string|max:255',
            'payment_date' => 'sometimes|required|date',
            'mode_of_payment' => 'nullable|string|max:255',
            'reference' => 'sometimes|required|string|max:255|unique:payments,reference,' . $payment->id,
            'nature_of_collection' => 'sometimes|required|string|max:255',
        ]);

        $payment->update($request->only([
            'user_id',
            'amount',
            'or',
            'payor_name',
            'payment_date',
            'deposit_date',
            'mode_of_payment',
            'reference',
            'description',
            'nature_of_collection',
            'type'
        ]));

        activity()
            ->causedBy(auth()->user()->info)
            ->performedOn($payment)
            ->log('Updated payment ' . $payment->reference);

        return response()->json($payment);
    }

    // Delete a payment
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);

        activity()
            ->causedBy(auth()->user()->info)
            ->performedOn($payment)
            ->log('Deleted payment ' . $payment->reference);

        $payment->delete();

        return response()->json(['message' => 'Payment deleted successfully']);
    }
}