<?php
namespace App\Policies;

use App\Models\TravelOrder;
use App\Models\User;

class TravelOrderPolicy
{
    public function view(User $user, TravelOrder $order)
    {
        return $user->id === $order->user_id;
    }

    public function updateStatus(User $user, TravelOrder $order)
    {
        return $user->id !== $order->user_id;
    }

    public function cancel(User $user, TravelOrder $order)
    {
        return $user->id === $order->user_id;
    }
}