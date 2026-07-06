<?php

namespace App\Services;

class ProcurementModeService
{
    const MODES = [
        'Public Bidding',
        'Small Value Procurement',
        'Shopping',
        'Direct Contracting',
    ];

    /**
     * Returns the recommended procurement mode based on RA 9184 IRR thresholds (Goods).
     * Direct Contracting is never auto-recommended — it requires manual selection.
     */
    public static function recommend(float $amount): string
    {
        if ($amount > 1_000_000) return 'Public Bidding';
        if ($amount > 200_000)   return 'Small Value Procurement';
        return 'Shopping';
    }

    public static function rationale(float $amount): string
    {
        if ($amount > 1_000_000) return 'ABC exceeds ₱1,000,000 — Public Bidding required under RA 9184.';
        if ($amount > 200_000)   return 'ABC is ₱200,001–₱1,000,000 — Small Value Procurement applies.';
        return 'ABC is ₱200,000 or below — Shopping procedure applies.';
    }
}
