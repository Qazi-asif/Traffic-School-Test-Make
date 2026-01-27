<?php

namespace App\Notifications;

use App\Models\StateTransmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RepeatedTransmissionFailure extends Notification implements ShouldQueue
{
    use Queueable;

    protected StateTransmission $transmission;

    /**
     * Create a new notification instance.
     */
    public function __construct(StateTransmission $transmission)
    {
        $this->transmission = $transmission;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $transmission = $this->transmission;
        $enrollment = $transmission->enrollment;
        $user = $enrollment->user;

        return (new MailMessage)
            ->error()
            ->subject("State Transmission Failed Multiple Times - ID #{$transmission->id}")
            ->greeting('Transmission Failure Alert')
            ->line("A state transmission has failed {$transmission->retry_count} times and requires attention.")
            ->line('')
            ->line('**Transmission Details:**')
            ->line("- Transmission ID: #{$transmission->id}")
            ->line("- State: {$transmission->state}")
            ->line("- Status: {$transmission->status}")
            ->line("- Retry Count: {$transmission->retry_count}")
            ->line('')
            ->line('**Student Information:**')
            ->line("- Name: {$user->first_name} {$user->last_name}")
            ->line("- Email: {$user->email}")
            ->line("- Enrollment ID: #{$enrollment->id}")
            ->line("- Course: {$enrollment->course->name}")
            ->line('')
            ->line('**Error Details:**')
            ->line("- Error Code: {$transmission->response_code}")
            ->line("- Error Message: {$transmission->response_message}")
            ->line('')
            ->action('View Transmission', url("/admin/fl-transmissions/{$transmission->id}"))
            ->line('Please review and resolve this issue as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'transmission_id' => $this->transmission->id,
            'state' => $this->transmission->state,
            'retry_count' => $this->transmission->retry_count,
            'error_code' => $this->transmission->response_code,
            'error_message' => $this->transmission->response_message,
            'enrollment_id' => $this->transmission->enrollment_id,
            'student_email' => $this->transmission->enrollment->user->email ?? null,
        ];
    }
}
