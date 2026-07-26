<?php

declare(strict_types=1);

namespace App\Services;

class DistanceCalculator
{
    /**
     * Distância em metros entre duas coordenadas (Haversine).
     */
    public function haversine(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2,
        float $earthRadiusMeters = 6371000.0,
    ): float {
        $latFrom = deg2rad($lat1);
        $lngFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lngTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;

        return 2 * $earthRadiusMeters * asin(sqrt($a));
    }
}
