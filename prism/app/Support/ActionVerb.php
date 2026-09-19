<?php

namespace App\Support;

class ActionVerb
{
    /**
     * Review/history-log action values have been written in both base form
     * ('endorse', 'return', 'approve') and past tense ('endorsed', 'returned',
     * 'submitted') by different code paths over time — trail/log displays
     * should always read as something that already happened, so this
     * normalizes either form to its past-tense label wherever an action gets
     * shown to a user (Proposal Review/Archive history, My PPMPs timeline,
     * Chancellor's activity feed, etc).
     */
    private const PAST_TENSE = [
        'submit'    => 'Submitted',
        'submitted' => 'Submitted',
        'endorse'   => 'Endorsed',
        'endorsed'  => 'Endorsed',
        'return'    => 'Returned',
        'returned'  => 'Returned',
        'approve'   => 'Approved',
        'approved'  => 'Approved',
        'reject'    => 'Rejected',
        'rejected'  => 'Rejected',
        'forward'   => 'Forwarded',
        'forwarded' => 'Forwarded',
        'sign'      => 'Signed',
        'signed'    => 'Signed',
        'create'    => 'Created',
        'created'   => 'Created',
        'update'    => 'Updated',
        'updated'   => 'Updated',
        'cancel'    => 'Cancelled',
        'cancelled' => 'Cancelled',
        'deny'      => 'Denied',
        'denied'    => 'Denied',
    ];

    /** Past-tense display label for a raw action/status value, whichever tense it was stored in. */
    public static function label(?string $action): string
    {
        $action = (string) $action;
        $key    = strtolower(trim($action));

        return self::PAST_TENSE[$key] ?? ucfirst(str_replace('_', ' ', $action));
    }
}
