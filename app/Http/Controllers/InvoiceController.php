<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::with(['client', 'user'])
            ->when($request->status, function ($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->when($request->client_id, function ($q) use ($request) {
                return $q->where('client_id', $request->client_id);
            })
            ->when($request->date_from, function ($q) use ($request) {
                return $q->where('issue_date', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($q) use ($request) {
                return $q->where('issue_date', '<=', $request->date_to);
            });

        // Regular users can only see their own invoices
        if (auth()->user()->hasRole('user')) {
            $query->where('user_id', auth()->id());
        }

        $invoices = $query->latest()->paginate(10);

        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Invoice::class);

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $subTotal = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $taxAmount = $subTotal * 0.1;
            $totalAmount = $subTotal + $taxAmount;

            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . Str::random(8),
                'client_id' => $request->client_id,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'sub_total' => $subTotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return response()->json($invoice->load('items', 'client'), 201);
        });
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        $invoice->load(['client', 'items', 'payments', 'user']);
        return response()->json($invoice);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request, $invoice) {
            $invoice->items()->delete();

            $subTotal = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $taxAmount = $subTotal * 0.1;
            $totalAmount = $subTotal + $taxAmount;

            $invoice->update([
                'client_id' => $request->client_id,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'sub_total' => $subTotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return response()->json($invoice->load('items', 'client'));
        });
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();
        return response()->json(null, 204);
    }

    public function sendInvoice(Invoice $invoice)
    {
        $this->authorize('send', $invoice);

        // Implement email sending logic here
        $invoice->update(['status' => 'sent']);
        
        // Send notification
        auth()->user()->notify(new \App\Notifications\InvoiceSent($invoice));

        return response()->json(['message' => 'Invoice sent successfully']);
    }
}