<?php

namespace App\Mail;

use App\Models\Application;
use App\Enums\ApplicationStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable for application status notifications
 * 
 * This class handles the formatting and sending of application
 * status change emails to applicants.
 * 
 * Supports different subjects and content based on status:
 * - HIRED: Congratulations message
 * - REJECTED: Regret message with encouragement
 */
class ApplicationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param Application $application
     */
    public function __construct(
        public Application $application
    ) {
        // Eager load relationships
        $this->application->load(['user', 'vacancy']);
    }

    /**
     * Get the message envelope.
     * 
     * Defines email metadata like subject, from, etc.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        $subject = $this->getSubjectByStatus();

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     * 
     * Defines which view to use and what data to pass to it.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.application-status',
            with: [
                'userName' => $this->application->user->name,
                'vacancyTitle' => $this->application->vacancy->title,
                'vacancyLocation' => $this->application->vacancy->location,
                'status' => $this->application->status->value,
                'statusLabel' => $this->getStatusLabel(),
                'isHired' => $this->application->status === ApplicationStatus::HIRED,
                'isRejected' => $this->application->status === ApplicationStatus::REJECTED,
            ]
        );
    }

    /**
     * Get email subject based on application status
     *
     * @return string
     */
    private function getSubjectByStatus(): string
    {
        return match ($this->application->status) {
            ApplicationStatus::HIRED => '🎉 Congratulations! You Have Been Hired',
            ApplicationStatus::REJECTED => 'Application Status Update',
            default => 'Your Application Status Has Changed'
        };
    }

    /**
     * Get human-readable status label
     *
     * @return string
     */
    private function getStatusLabel(): string
    {
        return match ($this->application->status) {
            ApplicationStatus::APPLIED => 'Application Submitted',
            ApplicationStatus::REVIEWED => 'Under Review',
            ApplicationStatus::INTERVIEW => 'Interview Stage',
            ApplicationStatus::HIRED => 'Hired',
            ApplicationStatus::REJECTED => 'Not Selected',
            default => ucfirst($this->application->status->value)
        };
    }
}
