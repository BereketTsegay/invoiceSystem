<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class UserInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $tempPassword, public string $role)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $registrationUrl = URL::temporarySignedRoute(
            'registration.complete',
            Carbon::now()->addDays(7),
            ['email' => $notifiable->email]
        );

        return (new MailMessage)
            ->subject('You\'re Invited to Join the Invoice Management System')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been invited by the super administrator to join our Invoice Management System.')
            ->line('Your temporary password: **' . $this->tempPassword . '**')
            ->line('Assigned Role: **' . ucfirst($this->role) . '**')
            ->line('Please click the button below to complete your registration and set up your account.')
            ->action('Complete Registration', $registrationUrl)
            ->line('This invitation link will expire in 7 days.')
            ->line('If you did not expect this invitation, please ignore this email.');
    }

    public function toArray($notifiable)
    {
        return [
            'role' => $this->role,
            'invited_at' => now(),
        ];
    }
}