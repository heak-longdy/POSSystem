<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

//garage
class MemberBookingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $status;
    public $message;
    public $data;
    public $member_id;
    public $car_id;

    public function __construct($status, $message, $data, $member_id, $car_id)
    {
        $this->status = $status;
        $this->data = $data;
        $this->member_id = $member_id;
        $this->car_id = $car_id;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return ["member-booking-$this->member_id-channel-$this->car_id"];
    }

    public function broadcastAs()
    {
        return "member-booking-$this->member_id-event-$this->car_id";
    }
}
