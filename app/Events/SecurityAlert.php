<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SecurityAlert implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public string $type,
        public string $message,
        public array $data = []
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('security'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'security.alert';
    }

    public function broadcastWith(): array
    {
        return [
            'user_name' => $this->user->name,
            'type' => $this->type,
            'message' => $this->message,
            'data' => $this->data,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
