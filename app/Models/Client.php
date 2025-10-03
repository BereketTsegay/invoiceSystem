<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'tax_number',
        'company_name',
        'website',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the invoices for the client.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Scope a query to only include active clients (with invoices).
     */
    public function scopeActive($query)
    {
        return $query->has('invoices');
    }

    /**
     * Scope a query to only include inactive clients (without invoices).
     */
    public function scopeInactive($query)
    {
        return $query->doesntHave('invoices');
    }

    /**
     * Scope a query to search clients by name, email, or phone.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%");
        });
    }

    /**
     * Get the client's total revenue.
     */
    public function getTotalRevenueAttribute()
    {
        return $this->invoices()->sum('total_amount');
    }

    /**
     * Get the client's paid revenue.
     */
    public function getPaidRevenueAttribute()
    {
        return $this->invoices()->where('status', 'paid')->sum('total_amount');
    }

    /**
     * Get the client's outstanding balance.
     */
    public function getOutstandingBalanceAttribute()
    {
        return $this->invoices()->whereIn('status', ['draft', 'sent', 'overdue'])->sum('total_amount');
    }

    /**
     * Check if client has outstanding invoices.
     */
    public function getHasOutstandingInvoicesAttribute()
    {
        return $this->invoices()->whereIn('status', ['draft', 'sent', 'overdue'])->exists();
    }

    /**
     * Get the client's last invoice date.
     */
    public function getLastInvoiceDateAttribute()
    {
        $lastInvoice = $this->invoices()->latest()->first();
        return $lastInvoice ? $lastInvoice->issue_date : null;
    }
}