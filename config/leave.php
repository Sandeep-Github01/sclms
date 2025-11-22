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

    $weights = [
        'bias' => -1.0,
        'credit_ok' => 1.5,
        'recent_count' => -0.6,
        'doc_present' => 1.0,
        'conflict_count' => -1.2,
        'days' => -0.05,
        'type_priority' => 0.8,
    ],
];
