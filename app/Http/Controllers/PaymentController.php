<?php
namespace App\Http\Controllers;

use App\Domains\Finance\Models\Payment;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function create(Invoice $invoice)
    {
        return view('payments.create', compact('invoice'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0|max:' . $invoice->balance,
            'payment_method' => 'required|in:cash,mobile_money,card,bank_transfer',
            'transaction_reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $invoice) {
            // Create payment
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount_paid' => $validated['amount_paid'],
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'],
                'notes' => $validated['notes'],
                'payment_date' => now()
            ]);

            // Create receipt
            Receipt::create([
                'payment_id' => $payment->id,
                'issued_at' => now()
            ]);

            // Update invoice status
            $totalPaid = $invoice->payments->sum('amount_paid') + $validated['amount_paid'];

            if ($totalPaid >= $invoice->total_amount) {
                $invoice->status = 'paid';
            } elseif ($totalPaid > 0) {
                $invoice->status = 'partially_paid';
            }

            $invoice->save();

            // Update job card status if fully paid
            if ($invoice->status === 'paid') {
                $invoice->jobCard->status = 'completed';
                $invoice->jobCard->save();
            }
        });

        return redirect()->route('job-cards.show', $invoice->jobCard)
            ->with('success', 'Payment processed successfully!');
    }
}
