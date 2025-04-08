<?php

namespace App\Events;

use App\Models\DriverTracking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $driverLocation;
    public $orderId;

    /**
     * Create a new event instance.
     */
    public function __construct(DriverTracking $driverTracking)
    {
        $this->driverLocation = [
            'latitude' => $driverTracking->latitude,
            'longitude' => $driverTracking->longitude,
        ];

        $this->orderId = $driverTracking->order_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Broadcasting on a private channel, where the orderId is part of the channel name
        return [
            new Channel('driver-location.' . $this->orderId),
        ];
    }

    /**
     * Data to broadcast with the event.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'driver_location' => $this->driverLocation,
        ];
    }
}
