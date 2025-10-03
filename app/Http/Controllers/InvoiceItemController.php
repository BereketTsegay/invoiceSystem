<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceItemController extends Controller
{
    /**
     * Display a listing of invoice items for a specific invoice.
     */
    public function index(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $items = $invoice->items()
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'items' => $items,
            'invoice_total' => $invoice->total_amount,
            'invoice_subtotal' => $invoice->sub_total,
            'tax_amount' => $invoice->tax_amount,
        ]);
    }

    /**
     * Store a newly created invoice item in storage.
     */
    public function store(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        // Check if invoice is editable
        if (!$invoice->isEditable()) {
            return response()->json([
                'message' => 'Cannot add items to an invoice that is already sent or paid.',
            ], 422);
        }

        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'quantity' => 'required|numeric|min:0.01|max:999999',
            'unit_price' => 'required|numeric|min:0|max:9999999.99',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'nullable|in:percentage,fixed',
        ]);

        try {
            DB::beginTransaction();

            // Calculate item totals
            $quantity = $validated['quantity'];
            $unitPrice = $validated['unit_price'];
            $taxRate = $validated['tax_rate'] ?? 0;
            $discount = $validated['discount'] ?? 0;
            $discountType = $validated['discount_type'] ?? 'percentage';

            // Calculate discount amount
            if ($discountType === 'percentage') {
                $discountAmount = ($unitPrice * $quantity) * ($discount / 100);
            } else {
                $discountAmount = $discount;
            }

            $subTotal = ($unitPrice * $quantity) - $discountAmount;
            $taxAmount = $subTotal * ($taxRate / 100);
            $total = $subTotal + $taxAmount;

            $invoiceItem = $invoice->items()->create([
                'description' => $validated['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax_rate' => $taxRate,
                'discount' => $discount,
                'discount_type' => $discountType,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total' => $total,
            ]);

            // Update invoice totals
            $this->updateInvoiceTotals($invoice);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($invoiceItem)
                ->withProperties([
                    'invoice_id' => $invoice->id,
                    'item' => $invoiceItem->toArray()
                ])
                ->log('added_item');

            DB::commit();

            return response()->json([
                'message' => 'Invoice item added successfully.',
                'item' => $invoiceItem,
                'invoice_totals' => [
                    'sub_total' => $invoice->fresh()->sub_total,
                    'tax_amount' => $invoice->fresh()->tax_amount,
                    'total_amount' => $invoice->fresh()->total_amount,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to add invoice item.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified invoice item.
     */
    public function show(Invoice $invoice, InvoiceItem $item)
    {
        $this->authorize('view', $invoice);

        // Verify the item belongs to the invoice
        if ($item->invoice_id !== $invoice->id) {
            return response()->json([
                'message' => 'Item does not belong to this invoice.',
            ], 404);
        }

        return response()->json([
            'item' => $item,
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
            ]
        ]);
    }

    /**
     * Update the specified invoice item in storage.
     */
    public function update(Request $request, Invoice $invoice, InvoiceItem $item)
    {
        $this->authorize('update', $invoice);

        // Verify the item belongs to the invoice
        if ($item->invoice_id !== $invoice->id) {
            return response()->json([
                'message' => 'Item does not belong to this invoice.',
            ], 404);
        }

        // Check if invoice is editable
        if (!$invoice->isEditable()) {
            return response()->json([
                'message' => 'Cannot modify items of an invoice that is already sent or paid.',
            ], 422);
        }

        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'quantity' => 'required|numeric|min:0.01|max:999999',
            'unit_price' => 'required|numeric|min:0|max:9999999.99',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'nullable|in:percentage,fixed',
        ]);

        try {
            DB::beginTransaction();

            $oldItem = $item->toArray();

            // Calculate item totals
            $quantity = $validated['quantity'];
            $unitPrice = $validated['unit_price'];
            $taxRate = $validated['tax_rate'] ?? $item->tax_rate;
            $discount = $validated['discount'] ?? $item->discount;
            $discountType = $validated['discount_type'] ?? $item->discount_type;

            // Calculate discount amount
            if ($discountType === 'percentage') {
                $discountAmount = ($unitPrice * $quantity) * ($discount / 100);
            } else {
                $discountAmount = $discount;
            }

            $subTotal = ($unitPrice * $quantity) - $discountAmount;
            $taxAmount = $subTotal * ($taxRate / 100);
            $total = $subTotal + $taxAmount;

            $item->update([
                'description' => $validated['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax_rate' => $taxRate,
                'discount' => $discount,
                'discount_type' => $discountType,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total' => $total,
            ]);

            // Update invoice totals
            $this->updateInvoiceTotals($invoice);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($item)
                ->withProperties([
                    'invoice_id' => $invoice->id,
                    'old' => $oldItem,
                    'new' => $item->toArray()
                ])
                ->log('updated_item');

            DB::commit();

            return response()->json([
                'message' => 'Invoice item updated successfully.',
                'item' => $item->fresh(),
                'invoice_totals' => [
                    'sub_total' => $invoice->fresh()->sub_total,
                    'tax_amount' => $invoice->fresh()->tax_amount,
                    'total_amount' => $invoice->fresh()->total_amount,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update invoice item.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified invoice item from storage.
     */
    public function destroy(Invoice $invoice, InvoiceItem $item)
    {
        $this->authorize('update', $invoice);

        // Verify the item belongs to the invoice
        if ($item->invoice_id !== $invoice->id) {
            return response()->json([
                'message' => 'Item does not belong to this invoice.',
            ], 404);
        }

        // Check if invoice is editable
        if (!$invoice->isEditable()) {
            return response()->json([
                'message' => 'Cannot remove items from an invoice that is already sent or paid.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $itemData = $item->toArray();
            $item->delete();

            // Update invoice totals
            $this->updateInvoiceTotals($invoice);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'invoice_id' => $invoice->id,
                    'item' => $itemData
                ])
                ->log('removed_item');

            DB::commit();

            return response()->json([
                'message' => 'Invoice item removed successfully.',
                'invoice_totals' => [
                    'sub_total' => $invoice->fresh()->sub_total,
                    'tax_amount' => $invoice->fresh()->tax_amount,
                    'total_amount' => $invoice->fresh()->total_amount,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to remove invoice item.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Bulk update invoice items.
     */
    public function bulkUpdate(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        // Check if invoice is editable
        if (!$invoice->isEditable()) {
            return response()->json([
                'message' => 'Cannot modify items of an invoice that is already sent or paid.',
            ], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:invoice_items,id,invoice_id,' . $invoice->id,
            'items.*.description' => 'required|string|max:1000',
            'items.*.quantity' => 'required|numeric|min:0.01|max:999999',
            'items.*.unit_price' => 'required|numeric|min:0|max:9999999.99',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_type' => 'nullable|in:percentage,fixed',
            'items.*._destroy' => 'nullable|boolean', // Flag to mark item for deletion
        ]);

        try {
            DB::beginTransaction();

            $existingItemIds = $invoice->items()->pluck('id')->toArray();
            $updatedItemIds = [];

            foreach ($validated['items'] as $itemData) {
                // Check if item should be deleted
                if (isset($itemData['_destroy']) && $itemData['_destroy']) {
                    if (isset($itemData['id'])) {
                        $item = InvoiceItem::find($itemData['id']);
                        if ($item && $item->invoice_id === $invoice->id) {
                            $item->delete();
                        }
                    }
                    continue;
                }

                // Calculate item totals
                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'];
                $taxRate = $itemData['tax_rate'] ?? 0;
                $discount = $itemData['discount'] ?? 0;
                $discountType = $itemData['discount_type'] ?? 'percentage';

                // Calculate discount amount
                if ($discountType === 'percentage') {
                    $discountAmount = ($unitPrice * $quantity) * ($discount / 100);
                } else {
                    $discountAmount = $discount;
                }

                $subTotal = ($unitPrice * $quantity) - $discountAmount;
                $taxAmount = $subTotal * ($taxRate / 100);
                $total = $subTotal + $taxAmount;

                $itemAttributes = [
                    'description' => $itemData['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_rate' => $taxRate,
                    'discount' => $discount,
                    'discount_type' => $discountType,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                ];

                if (isset($itemData['id']) && in_array($itemData['id'], $existingItemIds)) {
                    // Update existing item
                    $item = InvoiceItem::find($itemData['id']);
                    $item->update($itemAttributes);
                    $updatedItemIds[] = $itemData['id'];
                } else {
                    // Create new item
                    $item = $invoice->items()->create($itemAttributes);
                    $updatedItemIds[] = $item->id;
                }
            }

            // Delete items that weren't included in the update
            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            if (!empty($itemsToDelete)) {
                InvoiceItem::whereIn('id', $itemsToDelete)->delete();
            }

            // Update invoice totals
            $this->updateInvoiceTotals($invoice);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($invoice)
                ->withProperties(['item_count' => count($updatedItemIds)])
                ->log('bulk_updated_items');

            DB::commit();

            return response()->json([
                'message' => 'Invoice items updated successfully.',
                'items' => $invoice->items()->get(),
                'invoice_totals' => [
                    'sub_total' => $invoice->fresh()->sub_total,
                    'tax_amount' => $invoice->fresh()->tax_amount,
                    'total_amount' => $invoice->fresh()->total_amount,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update invoice items.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Recalculate and update invoice totals.
     */
    private function updateInvoiceTotals(Invoice $invoice)
    {
        $items = $invoice->items;

        $subTotal = $items->sum('total');
        $taxAmount = $items->sum('tax_amount');
        $totalAmount = $subTotal;

        // If using global tax rate instead of per-item tax
        if ($invoice->tax_rate) {
            $taxAmount = $subTotal * ($invoice->tax_rate / 100);
            $totalAmount = $subTotal + $taxAmount;
        }

        $invoice->update([
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);

        return $invoice;
    }

    /**
     * Duplicate an invoice item.
     */
    public function duplicate(Invoice $invoice, InvoiceItem $item)
    {
        $this->authorize('update', $invoice);

        // Verify the item belongs to the invoice
        if ($item->invoice_id !== $invoice->id) {
            return response()->json([
                'message' => 'Item does not belong to this invoice.',
            ], 404);
        }

        // Check if invoice is editable
        if (!$invoice->isEditable()) {
            return response()->json([
                'message' => 'Cannot add items to an invoice that is already sent or paid.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $newItem = $item->replicate();
            $newItem->description = $item->description . ' (Copy)';
            $newItem->save();

            // Update invoice totals
            $this->updateInvoiceTotals($invoice);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($newItem)
                ->withProperties([
                    'invoice_id' => $invoice->id,
                    'original_item_id' => $item->id
                ])
                ->log('duplicated_item');

            DB::commit();

            return response()->json([
                'message' => 'Invoice item duplicated successfully.',
                'item' => $newItem,
                'invoice_totals' => [
                    'sub_total' => $invoice->fresh()->sub_total,
                    'tax_amount' => $invoice->fresh()->tax_amount,
                    'total_amount' => $invoice->fresh()->total_amount,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to duplicate invoice item.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Reorder invoice items.
     */
    public function reorder(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        // Check if invoice is editable
        if (!$invoice->isEditable()) {
            return response()->json([
                'message' => 'Cannot reorder items of an invoice that is already sent or paid.',
            ], 422);
        }

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:invoice_items,id,invoice_id,' . $invoice->id,
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['order'] as $position => $itemId) {
                InvoiceItem::where('id', $itemId)
                    ->where('invoice_id', $invoice->id)
                    ->update(['position' => $position]);
            }

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($invoice)
                ->withProperties(['item_count' => count($validated['order'])])
                ->log('reordered_items');

            DB::commit();

            return response()->json([
                'message' => 'Invoice items reordered successfully.',
                'items' => $invoice->items()->orderBy('position')->get()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to reorder invoice items.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}