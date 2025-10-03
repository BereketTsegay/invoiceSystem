<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
class InvoicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('invoice.view');
    }

    public function view(User $user, Invoice $invoice)
    {
        return $user->hasPermissionTo('invoice.view');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('invoice.create');
    }

    public function update(User $user, Invoice $invoice)
    {
        return $user->hasPermissionTo('invoice.edit');
    }

    public function delete(User $user, Invoice $invoice)
    {
        return $user->hasPermissionTo('invoice.delete');
    }

    public function send(User $user, Invoice $invoice)
    {
        return $user->hasPermissionTo('invoice.send');
    }
}
