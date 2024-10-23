<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Config;

use LastDragon_ru\LaraASP\Core\Application\Configuration\Configuration;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @see https://symfony.com/doc/current/components/serializer.html
 */
class Config extends Configuration {
    public function __construct(
        /**
         * Default format.
         */
        public string $default = JsonEncoder::FORMAT,
        /**
         * Additional encoders and their context options. By default, only
         * {@see JsonEncoder} available.
         *
         * @var array<class-string<EncoderInterface|DecoderInterface>, array<string, mixed>>
         */
        public array $encoders = [],
        /**
         * Additional normalizers/denormalizers and their context options. The `null`
         * value can be used to remove the built-in normalizer/denormalizer.
         *
         * @var array<class-string<NormalizerInterface|DenormalizerInterface>, array<string, mixed>|null>
         */
        public array $normalizers = [],
        /**
         * Additional context options.
         *
         * @var array<string, mixed>
         */
        public array $context = [],
    ) {
        parent::__construct();
    }
}
