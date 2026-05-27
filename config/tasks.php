<?php

return [
    'categories' => [
        'rapor-generator' => 'Smart Rapor Generator + Multi-Template',
        'ai-narasi' => 'AI Narasi Rapor (Anthropic API)',
        'early-warning' => 'AI Early Warning System',
        'payroll' => 'Payroll Guru & Karyawan',
        'student-analytics' => 'Grafik Perkembangan Siswa',
        'p5-assessment' => 'Penilaian P5 (Profil Pelajar Pancasila)',
        'rapor-distribusi' => 'PDF Generation & WhatsApp Distribusi Rapor',
        'kalender' => 'Kalender Akademik & Event',
    ],

    'statuses' => [
        'pending' => 'Not Started',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'blocked' => 'Blocked',
        'archived' => 'Archived',
    ],

    'priorities' => [
        1 => 'High',
        2 => 'Normal',
        3 => 'Low',
    ],

    'feature_info' => [
        'rapor-generator' => [
            'priority' => 1,
            'description' => 'Smart Rapor Generator with multi-template support',
        ],
        'ai-narasi' => [
            'priority' => 1,
            'description' => 'AI-powered narrative generation for report cards using Anthropic API',
        ],
        'early-warning' => [
            'priority' => 1,
            'description' => 'Early warning system to identify at-risk students using AI',
        ],
        'payroll' => [
            'priority' => 2,
            'description' => 'Comprehensive payroll management for teachers and staff',
        ],
        'student-analytics' => [
            'priority' => 2,
            'description' => 'Student progress analytics and visualizations',
        ],
        'p5-assessment' => [
            'priority' => 2,
            'description' => 'Assessment of Pancasila Student Profile competencies',
        ],
        'rapor-distribusi' => [
            'priority' => 3,
            'description' => 'PDF generation and WhatsApp distribution of report cards',
        ],
        'kalender' => [
            'priority' => 3,
            'description' => 'Academic calendar and event management',
        ],
    ],
];
