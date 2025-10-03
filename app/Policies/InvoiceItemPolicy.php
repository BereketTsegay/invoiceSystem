<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoiceItemPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.view') && $invoice->user_id === $user->id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice, InvoiceItem $item): bool
    {
        return $user->can('invoice.view') && 
               $invoice->user_id === $user->id && 
               $item->invoice_id === $invoice->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.edit') && 
               $invoice->user_id === $user->id &&
               $invoice->isEditable();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice, InvoiceItem $item): bool
    {
        return $user->can('invoice.edit') && 
               $invoice->user_id === $user->id && 
               $item->invoice_id === $invoice->id &&
               $invoice->isEditable();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice, InvoiceItem $item): bool
    {
        return $user->can('invoice.edit') && 
               $invoice->user_id === $user->id && 
               $item->invoice_id === $invoice->id &&
               $invoice->isEditable();
    }

    /**
     * Determine whether the user can bulk update items.
     */
    public function bulkUpdate(User $user, Invoice $invoice): bool
    {
        return $user->can('invoice.edit') && 
               $invoice->user_id === $user->id &&
               $invoice->isEditable();
    }
}