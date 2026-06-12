<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $token  The raw (unhashed) password reset token.
     */
    public function __construct(protected string $token)
    {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // The frontend URL where the user sets their password.
        $url = config('app.frontend_url') . '/set-password'
        . '?token=' . $this->token
        . '&email=' . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Welcome to ITI Portal — Set Your Password')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your account has been created on the ITI Attendance Portal.')
            ->line('Please click the button below to set your password and activate your account.')
            ->action('Set Your Password', $url)
            ->line('This link will expire in ' . config('auth.passwords.activation.expire') . ' minutes.')
            ->line('If you did not expect this email, no action is required.');
    }
}
