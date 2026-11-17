<?php
/**
 * Isolated smoke tests for the pure reviewer-decision aggregation rule.
 * This script does not require the Laravel vendor directory. The tiny mb_strtolower
 * polyfill is only for this ASCII-only demo fixture when ext-mbstring is unavailable.
 */
namespace App\Support {
    if (! function_exists(__NAMESPACE__.'\\mb_strtolower')) {
        function mb_strtolower($value): string
        {
            return strtolower((string) $value);
        }
    }
}

namespace {
    require dirname(__DIR__).'/app/Support/ReviewerPrototypeData.php';

    use App\Support\ReviewerPrototypeData;

    $base = [
        'institution' => 'Universitas Lampung',
        'assigned_reviewer_ids' => ['reviewer_a', 'reviewer_b'],
        'assigned_reviewers' => [
            ['id' => 'reviewer_a', 'institution' => 'Universitas Sriwijaya'],
            ['id' => 'reviewer_b', 'institution' => 'Institut Teknologi Sumatera'],
        ],
    ];

    $cases = [
        'two independent approvals unlock payout' => [
            array_merge($base, ['reviews' => [
                'reviewer_a' => ['decision' => 'approved'],
                'reviewer_b' => ['decision' => 'approved'],
            ]]),
            'approved', true,
        ],
        'one approval waits for second reviewer' => [
            array_merge($base, ['reviews' => ['reviewer_a' => ['decision' => 'approved']]]),
            'waiting_second', false,
        ],
        'one-person panel cannot unlock payout' => [
            array_merge($base, [
                'assigned_reviewer_ids' => ['reviewer_a'],
                'assigned_reviewers' => [['id' => 'reviewer_a', 'institution' => 'Universitas Sriwijaya']],
                'reviews' => ['reviewer_a' => ['decision' => 'approved']],
            ]),
            'unassigned', false,
        ],
        'duplicate reviewer identifier does not count as two reviewers' => [
            array_merge($base, [
                'assigned_reviewer_ids' => ['reviewer_a', 'reviewer_a'],
                'reviews' => ['reviewer_a' => ['decision' => 'approved']],
            ]),
            'unassigned', false,
        ],
        'duplicate institution invalidates panel' => [
            array_merge($base, [
                'assigned_reviewers' => [
                    ['id' => 'reviewer_a', 'institution' => 'Universitas Sriwijaya'],
                    ['id' => 'reviewer_b', 'institution' => 'Universitas Sriwijaya'],
                ],
                'reviews' => [
                    'reviewer_a' => ['decision' => 'approved'],
                    'reviewer_b' => ['decision' => 'approved'],
                ],
            ]),
            'unassigned', false,
        ],
        'researcher institution conflict invalidates panel' => [
            array_merge($base, [
                'assigned_reviewers' => [
                    ['id' => 'reviewer_a', 'institution' => 'Universitas Lampung'],
                    ['id' => 'reviewer_b', 'institution' => 'Institut Teknologi Sumatera'],
                ],
                'reviews' => [
                    'reviewer_a' => ['decision' => 'approved'],
                    'reviewer_b' => ['decision' => 'approved'],
                ],
            ]),
            'unassigned', false,
        ],
        'minor revision blocks payout' => [
            array_merge($base, ['reviews' => [
                'reviewer_a' => ['decision' => 'approved'],
                'reviewer_b' => ['decision' => 'revision_minor'],
            ]]),
            'revision', false,
        ],
        'rejection blocks payout' => [
            array_merge($base, ['reviews' => [
                'reviewer_a' => ['decision' => 'approved'],
                'reviewer_b' => ['decision' => 'rejected'],
            ]]),
            'rejected', false,
        ],
        'conflict blocks entire panel' => [
            array_merge($base, ['reviews' => [
                'reviewer_a' => ['conflict_reported' => true],
            ]]),
            'conflict', false,
        ],
    ];

    $failures = [];
    foreach ($cases as $name => [$assignment, $expectedKey, $expectedPayout]) {
        $actual = ReviewerPrototypeData::aggregate($assignment);
        if (($actual['key'] ?? null) !== $expectedKey || ($actual['payout_ready'] ?? null) !== $expectedPayout) {
            $failures[] = [
                'case' => $name,
                'expected' => [$expectedKey, $expectedPayout],
                'actual' => [$actual['key'] ?? null, $actual['payout_ready'] ?? null],
            ];
        }
    }

    if ($failures !== []) {
        fwrite(STDERR, json_encode($failures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL);
        exit(1);
    }

    echo 'PASS: '.count($cases).' reviewer aggregation scenarios'.PHP_EOL;
}
