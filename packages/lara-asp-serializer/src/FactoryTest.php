<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Serializer\Config\Config;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use LastDragon_ru\LaraASP\Serializer\Normalizers\DateTimeNormalizer;
use LastDragon_ru\LaraASP\Serializer\Normalizers\EnumNormalizer;
use LastDragon_ru\LaraASP\Serializer\Normalizers\SerializableNormalizer;
use LastDragon_ru\LaraASP\Serializer\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeZoneNormalizer;

/**
 * @internal
 */
#[CoversClass(Factory::class)]
final class FactoryTest extends TestCase {
    public function testCreate(): void {
        $this->setConfiguration(PackageConfig::class, static function (Config $config): void {
            $config->default     = 'format from config';
            $config->encoders    = [
                XmlEncoder::class  => [],
                JsonEncoder::class => [
                    'context option from config' => 'encoder',
                    'encoder option from config' => 'encoder',
                    'encoder option'             => 'encoder',
                ],
            ];
            $config->normalizers = [
                SerializableNormalizer::class => null,
                DateTimeNormalizer::class     => [
                    'context option from config'    => 'normalizer',
                    'normalizer option from config' => 'normalizer',
                    'normalizer option'             => 'normalizer',
                ],
                DataUriNormalizer::class      => [],
            ];
            $config->context     = [
                'context option from config' => 'context',
                'context option'             => 'context',
            ];
        });

        $config    = $this->app()->make(PackageConfig::class);
        $container = $this->app()->make(ContainerResolver::class);
        $factory   = Mockery::mock(Factory::class, [$container, $config])
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $factory
            ->shouldReceive('make')
            ->once()
            ->andReturnUsing(
                static function (array $encoders, array $normalizers, array $context, string $format): Serializer {
                    self::assertSame('format from config', $format);
                    self::assertEquals(
                        [
                            'encoder option from config'      => 'encoder',
                            'encoder option'                  => 'call',
                            'normalizer option from config'   => 'normalizer',
                            'normalizer option'               => 'call',
                            'context option from config'      => 'context',
                            'context option'                  => 'call',
                            'csv encoder option'              => 'call',
                            'json_encode_options'             => 4_197_698,
                            'json_decode_options'             => 4_194_304,
                            DateTimeNormalizer::ContextFormat => 'Y-m-d\TH:i:s.vP',
                        ],
                        $context,
                    );
                    self::assertEquals(
                        [
                            CsvEncoder::class,
                            JsonEncoder::class,
                            XmlEncoder::class,
                        ],
                        $encoders,
                    );
                    self::assertEquals(
                        [
                            DateTimeNormalizer::class,
                            DataUriNormalizer::class,
                            ArrayDenormalizer::class,
                            DateIntervalNormalizer::class,
                            EnumNormalizer::class,
                        ],
                        $normalizers,
                    );

                    return Mockery::mock(Serializer::class);
                },
            );

        $factory->create(
            [
                CsvEncoder::class  => [
                    'csv encoder option' => 'call',
                ],
                JsonEncoder::class => [
                    'encoder option' => 'call',
                ],
            ],
            [
                DateTimeZoneNormalizer::class => null,
                DateTimeNormalizer::class     => [
                    'normalizer option' => 'call',
                ],
            ],
            [
                'context option' => 'call',
            ],
            null,
        );
    }
}
