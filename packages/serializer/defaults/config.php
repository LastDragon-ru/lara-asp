<?php declare(strict_types = 1);

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
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
 *          default: string|null,
 *          encoders: array<
 *              class-string<EncoderInterface|DecoderInterface>,
 *              array<string, mixed>
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
     * Default format. The `null` means "built-in default" (=json).
     */
    'default'     => null,

    /**
     * Additional encoders and their context options. By default, only
     * {@see \Symfony\Component\Serializer\Encoder\JsonEncoder} available.
     */
    'encoders'    => [
        // empty
    ],

    /**
     * Additional normalizers/denormalizers and their context options. The `null`
     * value can be used to remove the built-in normalizer/denormalizer.
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
