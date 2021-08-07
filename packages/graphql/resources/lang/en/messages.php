<?php declare(strict_types = 1);

// @phpcs:disable Generic.Files.LineLength.TooLong

return [
    'search_by' => [
        'errors' => [
            'too_many_properties' => 'Only one property allowed, found: `:properties`.',
            'too_many_operators'  => 'Only one comparison operator allowed, found: `:operators`.',
            'unsupported_option'  => 'Operator `:operator` cannot be used with `:option`.',
            'unknown_operator'    => 'Operator `:operator` not found.',
            'empty_condition'     => 'Search condition cannot be empty.',
        ],
    ],
];
