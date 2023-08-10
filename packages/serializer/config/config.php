<?php declare(strict_types = 1);

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * -----------------------------------------------------------------------------
 * Serializer Settings
 * -----------------------------------------------------------------------------
 *
 * @see https://symfony.com/doc/current/components/serializer.html
 *
 * @var array{
 *          default: string,
 *          encoders: array<
 *              class-string<EncoderInterface|DecoderInterface>,
 *              array<string, mixed>|null
 *          >,
 *          normalizers: array<
 *              class-string<NormalizerInterface|DenormalizerInterface>,
 *              array<string, mixed>|null
 *          >,
 *          context: array<string, mixed>,
 *      } $settings
 */
$settings = [
    /**
     * Default format.
     */
    'default'     => JsonEncoder::FORMAT,

    /**
     * Additional encoders and their context options. By default, only {@see JsonEncoder} available.
     */
    'encoders'    => [
        // empty
    ],

    /**
     * Additional normalizers and their context options.
     */
    'normalizers' => [
        // empty
    ],

    /**
     * Additional context options.
     */
    'context'     => [
        // empty
    ],
];

return $settings;
