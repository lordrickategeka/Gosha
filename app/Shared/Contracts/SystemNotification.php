<?php

namespace App\Shared\Contracts;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base class for all in-system notifications.
 * Extend this in any domain to create a pluggable notification.
 *
 * Subclasses must implement:
 *   getType()     – machine-readable slug, e.g. 'low_stock'
 *   getDomain()   – owning domain, e.g. 'inventory'
 *   getTitle()    – short human title
 *   getMessage()  – one-sentence description
 *   getSeverity() – 'info' | 'warning' | 'error' | 'success'
 *   getUrl()      – optional deep-link, null if none
 *   toMail()      – mail representation
 *   extraData()   – domain-specific extra fields merged into toArray()
 */
abstract class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    abstract public function getType(): string;
    abstract public function getDomain(): string;
    abstract public function getTitle(): string;
    abstract public function getMessage(): string;
    abstract public function getSeverity(): string;
    abstract public function getUrl(): ?string;
    abstract public function toMail(object $notifiable): MailMessage;

    /** Override to add domain-specific fields to the stored data payload. */
    public function extraData(): array
    {
        return [];
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type'     => $this->getType(),
            'domain'   => $this->getDomain(),
            'title'    => $this->getTitle(),
            'message'  => $this->getMessage(),
            'severity' => $this->getSeverity(),
            'url'      => $this->getUrl(),
        ], $this->extraData());
    }

    public function databaseType(object $notifiable): string
    {
        return $this->getDomain() . '.' . $this->getType();
    }
}
