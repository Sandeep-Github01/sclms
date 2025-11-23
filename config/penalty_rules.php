<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Penalty Point Rules
    |--------------------------------------------------------------------------
    */

    'rules' => [

        // ------------------
        // Low Severity
        // ------------------
        'late_document' => 1,
        'minor_inconsistency' => 1,
        'last_minute_leave' => 2,
        'frequent_same_day' => 2,
        'multiple_leaves_short_period' => 3,

        // ------------------
        // Medium Severity
        // ------------------
        'unapproved_absence' => 3,
        'repeated_missing_docs' => 4,
        'invalid_document' => 5,
        'moderate_false_reason' => 5,
        'pattern_abuse' => 6,
        'excessive_medical_no_docs' => 7,

        // ------------------
        // High Severity
        // ------------------
        'forged_document' => 10,
        'backdated_leave' => 9,
        'severe_pattern_abuse' => 8,
        'combined_suspicion' => 12,

        // ------------------
        // Critical Violations
        // ------------------
        'proven_fraud' => 20,
        'confirmed_leave_abuse' => 15,
        'multiple_abuses' => 25,
        'admin_flagged_abuse' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Penalty Thresholds
    |--------------------------------------------------------------------------
    */

    'thresholds' => [
        5  => 'warning',
        10 => 'force_manual_review',
        15 => 'reduce_leave_credit',
        20 => 'block_non_critical_leaves',
    ]
];
