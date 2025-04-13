<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\TravelOrder;

class TravelOrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    public function __construct(TravelOrder $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Travel Order Status Update")
            ->line("Your travel order to {$this->order->destination} has been {$this->order->status}.")
            ->line("Dates: {$this->order->start_date->format('Y-m-d')} to {$this->order->end_date->format('Y-m-d')}")
            ->line("Status: " . ucfirst($this->order->status))
            ->action('View Order', url('/orders/'.$this->order->id))
            ->line('Thank you for using our service!');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'destination' => $this->order->destination,
            'status' => $this->order->status,
            'message' => "Your travel order to {$this->order->destination} has been {$this->order->status}.",
        ];
    }
}