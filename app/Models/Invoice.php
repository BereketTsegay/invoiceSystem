<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
     protected $fillable = [
        'invoice_number', 'client_id', 'issue_date', 'due_date',
        'sub_total', 'tax_amount', 'total_amount', 'notes', 'status', 'user_id'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if the invoice is editable.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft']);
    }

    /**
     * Check if the invoice can have items added/modified.
     */
    public function canModifyItems(): bool
    {
        return in_array($this->status, ['draft']);
    }

    /**
     * Recalculate invoice totals based on items.
     */
    public function recalculateTotals(): self
    {
        $subTotal = $this->items->sum('total');
        $taxAmount = $this->items->sum('tax_amount');
        $totalAmount = $subTotal;

        // If using global tax rate
        if ($this->tax_rate) {
            $taxAmount = $subTotal * ($this->tax_rate / 100);
            $totalAmount = $subTotal + $taxAmount;
        }

        $this->update([
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);

        return $this->fresh();
    }

    /**
     * Get the invoice items ordered by position.
     */
    public function getOrderedItemsAttribute()
    {
        return $this->items()->ordered()->get();
    }

    /**
     * Get the total quantity of all items.
     */
    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get the average unit price of items.
     */
    public function getAverageUnitPriceAttribute()
    {
        return $this->items->avg('unit_price');
    }
}
