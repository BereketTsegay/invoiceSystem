<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'discount',
        'discount_type',
        'discount_amount',
        'tax_amount',
        'total',
        'position',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'position' => 'integer',
    ];

    /**
     * Get the invoice that owns the item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Calculate the item total before tax and discount.
     */
    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Calculate the item total after discount but before tax.
     */
    public function getTotalAfterDiscountAttribute()
    {
        return $this->subtotal - $this->discount_amount;
    }

    /**
     * Calculate the total tax amount for the item.
     */
    public function getCalculatedTaxAmountAttribute()
    {
        return $this->total_after_discount * ($this->tax_rate / 100);
    }

    /**
     * Calculate the final total for the item.
     */
    public function getCalculatedTotalAttribute()
    {
        return $this->total_after_discount + $this->calculated_tax_amount;
    }

    /**
     * Scope a query to order items by position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('created_at');
    }

    /**
     * Boot method for model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->position)) {
                $maxPosition = static::where('invoice_id', $item->invoice_id)->max('position');
                $item->position = $maxPosition ? $maxPosition + 1 : 1;
            }
        });

        static::saving(function ($item) {
            // Ensure calculated fields are consistent
            if ($item->discount_type === 'percentage') {
                $item->discount_amount = ($item->quantity * $item->unit_price) * ($item->discount / 100);
            } else {
                $item->discount_amount = $item->discount;
            }

            $totalAfterDiscount = ($item->quantity * $item->unit_price) - $item->discount_amount;
            $item->tax_amount = $totalAfterDiscount * ($item->tax_rate / 100);
            $item->total = $totalAfterDiscount + $item->tax_amount;
        });
    }
}