<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'secondary_email',
        'phone',
        'secondary_phone',
        'address',
        'street_address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_number',
        'company_name',
        'website',
        'notes',
        'business_type',
        'industry',
        'employee_count',
        'currency',
        'credit_limit',
        'payment_terms',
        'status',
        'priority',
        'source',
        'last_contacted_at',
        'first_contact_at',
    ];

    protected $casts = [
        'employee_count' => 'integer',
        'credit_limit' => 'decimal:2',
        'last_contacted_at' => 'datetime',
        'first_contact_at' => 'datetime',
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
     * Get the contacts for the client.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    /**
     * Get the notes for the client.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(ClientNote::class);
    }

    /**
     * Get the primary contact for the client.
     */
    public function primaryContact()
    {
        return $this->contacts()->where('is_primary', true)->first();
    }

    /**
     * Scope a query to only include active clients.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive clients.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope a query to only include lead clients.
     */
    public function scopeLeads($query)
    {
        return $query->where('status', 'lead');
    }

    /**
     * Scope a query to only include clients with invoices.
     */
    public function scopeWithInvoices($query)
    {
        return $query->has('invoices');
    }

    /**
     * Scope a query to only include clients without invoices.
     */
    public function scopeWithoutInvoices($query)
    {
        return $query->doesntHave('invoices');
    }

    /**
     * Scope a query to search clients by multiple fields.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('contact_person', 'like', "%{$search}%")
              ->orWhere('tax_number', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%")
              ->orWhere('state', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query by industry.
     */
    public function scopeIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    /**
     * Scope a query by country.
     */
    public function scopeCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope a query by priority.
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
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
     * Get the client's overdue amount.
     */
    public function getOverdueAmountAttribute()
    {
        return $this->invoices()->where('status', 'overdue')->sum('total_amount');
    }

    /**
     * Check if client has outstanding invoices.
     */
    public function getHasOutstandingInvoicesAttribute()
    {
        return $this->invoices()->whereIn('status', ['draft', 'sent', 'overdue'])->exists();
    }

    /**
     * Check if client has overdue invoices.
     */
    public function getHasOverdueInvoicesAttribute()
    {
        return $this->invoices()->where('status', 'overdue')->exists();
    }

    /**
     * Get the client's last invoice date.
     */
    public function getLastInvoiceDateAttribute()
    {
        $lastInvoice = $this->invoices()->latest()->first();
        return $lastInvoice ? $lastInvoice->issue_date : null;
    }

    /**
     * Get the client's average invoice amount.
     */
    public function getAverageInvoiceAmountAttribute()
    {
        return $this->invoices()->avg('total_amount');
    }

    /**
     * Get the client's payment performance (percentage of paid invoices).
     */
    public function getPaymentPerformanceAttribute()
    {
        $totalInvoices = $this->invoices()->count();
        $paidInvoices = $this->invoices()->where('status', 'paid')->count();

        return $totalInvoices > 0 ? ($paidInvoices / $totalInvoices) * 100 : 0;
    }

    /**
     * Get the client's full address.
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->street_address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ]);

        return implode(', ', $parts);
    }

    /**
     * Check if client is near credit limit.
     */
    public function getIsNearCreditLimitAttribute()
    {
        if (!$this->credit_limit) return false;

        $usedCredit = $this->outstanding_balance;
        $usagePercentage = ($usedCredit / $this->credit_limit) * 100;

        return $usagePercentage >= 80; // 80% or more of credit limit used
    }

    /**
     * Mark client as contacted.
     */
    public function markAsContacted()
    {
        $this->update(['last_contacted_at' => now()]);
    }

    /**
     * Scope a query to only include clients suitable for dropdowns.
     */
    public function scopeForDropdown($query)
    {
        return $query->select([
            'id',
            'name',
            'email',
            'company_name',
            'phone',
            'payment_terms',
            'credit_limit',
            'currency'
        ])->active()->orderBy('name');
    }
}
