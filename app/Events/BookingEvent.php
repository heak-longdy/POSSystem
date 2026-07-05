<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

//member
class BookingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $status;
    public $message;
    public $data;
    public $garage_id;
    public function __construct($status, $message, $data, $garage_id)
    {
        $this->status = $status;
        $this->data = $data;
        $this->garage_id = $garage_id;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return ["booking-garage-$this->garage_id-channel"];
    }

    public function broadcastAs()
    {
        return "booking-garage-$this->garage_id-event";
    }
}
