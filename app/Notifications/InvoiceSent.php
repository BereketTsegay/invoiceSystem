<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InvoiceSent extends Notification
{
    use Queueable;

    public function __construct(public Invoice $invoice)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Invoice Sent: ' . $this->invoice->invoice_number)
            ->line('Your invoice has been sent to the client.')
            ->line('Invoice Number: ' . $this->invoice->invoice_number)
            ->line('Amount: $' . number_format($this->invoice->total_amount, 2))
            ->action('View Invoice', url('/invoices/' . $this->invoice->id))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'message' => 'Invoice ' . $this->invoice->invoice_number . ' has been sent to client.',
        ];
    }
}