<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CampaignDatabaseNotification extends Notification
{
    use Queueable;

    public function __construct(private array $payload)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->payload['title'] ?? 'Notificacion',
            'body' => $this->payload['body'] ?? '',
            'icon' => $this->payload['icon'] ?? 'info',
            'url' => $this->payload['url'] ?? null,
            'campaign_id' => $this->payload['campaign_id'] ?? null,
            'campaign_name' => $this->payload['campaign_name'] ?? null,
            'priority' => $this->payload['priority'] ?? 'normal',
        ];
    }
}
