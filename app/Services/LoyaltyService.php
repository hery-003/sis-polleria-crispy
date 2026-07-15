<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Order;

class LoyaltyService
{
    public const POINTS_PER_AMOUNT = 10;

    public function calculatePoints(float $amount): int
    {
        return floor($amount / self::POINTS_PER_AMOUNT);
    }

    public function awardPoints(Order $order): int
    {
        if (! $order->client_id) {
            return 0;
        }

        $points = $this->calculatePoints($order->total_amount);
        if ($points > 0) {
            $order->client->increment('points', $points);
        }

        return $points;
    }

    public function deductPoints(Order $order): int
    {
        if (! $order->client_id) {
            return 0;
        }

        $pointsToDeduct = $this->calculatePoints($order->total_amount);
        if ($pointsToDeduct > 0) {
            $order->client->decrement('points', $pointsToDeduct);
        }

        return $pointsToDeduct;
    }
}
