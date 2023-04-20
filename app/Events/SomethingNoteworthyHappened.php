<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SomethingNoteworthyHappened
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $who;

    public $what;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($who, $what)
    {
        $this->who = $who;
        $this->what = $what;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): array
    {
        return new PrivateChannel('channel-name');
    }
}
