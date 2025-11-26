<?php

return [
    'medical_document_deadline_days' => 3,

    'thresholds' => [
        'teacher_absentee_max' => 1,
        'student_absentee_max' => 3,
        'department_load_ratio' => 1.5,
        'fraud_score_manual' => 4,
        'approval_probability_auto' => 0.8,
        'approval_probability_manual' => 0.5,
    ],

    'weights' => [
        'bias' => -1.0,
        'credit_ok' => 1.5,
        'credit_pct' => 1.0,   // % of credits left
        'recent_count' => -0.6,
        'doc_present' => 1.0,
        'conflict_count' => -1.2,
        'days' => -0.05,
        'type_priority' => 0.8,
        'late_night' => -0.5,   // 22:00-06:00 application
        'last_minute' => -0.8,   // <24 h notice
        'weekend_bridge' => -0.7,   // Fri-Sun, Sat-Mon …
        'fraud_score' => -0.3,   // residual fraud points
        'blackout' => -2.0,
        'max_absentees' => -0.4,
        'peer_conflicts' => -0.6,
        'early_application' => 0.4,   // ≥7 days ahead
    ],
    
    'decision_thresholds' => [
        'auto_approve' => 0.80,   // probability ≥ this → approved
        'manual_review' => 0.50,  // probability ≥ this → manual
    ],
];
