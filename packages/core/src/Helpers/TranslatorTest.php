<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Helpers;

use Illuminate\Contracts\Translation\Translator;
use LastDragon_ru\LaraASP\Core\Helpers\Translator as PackageTranslator;
use LastDragon_ru\LaraASP\Core\Package;
use LastDragon_ru\LaraASP\Core\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Testing\Utils\WithTranslations;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 *
 * @phpstan-import-type TranslationsFactory from WithTranslations
 */
#[CoversClass(Translator::class)]
final class TranslatorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param list<string>|string  $key
     * @param TranslationsFactory  $translations
     * @param array<string, mixed> $replace
     */
    #[DataProvider('dataProviderChoice')]
    public function testChoice(
        string $expected,
        mixed $translations,
        array|string $key,
        int $number,
        array $replace,
        ?string $locale,
    ): void {
        $this->setTranslations($translations);

        $implementation = $this->app()->make(Translator::class);
        $translator     = new class($implementation) extends PackageTranslator {
            #[Override]
            protected function getName(): string {
                return Package::Name;
            }
        };

        self::assertEquals($expected, $translator->choice($key, $number, $replace, $locale));
    }

    /**
     * @param list<string>|string  $key
     * @param TranslationsFactory  $translations
     * @param array<string, mixed> $replace
     */
    #[DataProvider('dataProviderGet')]
    public function testGet(
        string $expected,
        mixed $translations,
        array|string $key,
        array $replace,
        ?string $locale,
    ): void {
        $this->setTranslations($translations);

        $implementation = $this->app()->make(Translator::class);
        $translator     = new class($implementation) extends PackageTranslator {
            #[Override]
            protected function getName(): string {
                return Package::Name;
            }
        };

        self::assertEquals($expected, $translator->get($key, $replace, $locale));
    }
    //</editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string,array{string, TranslationsFactory, list<string>|string, array<string,string>, ?string}>
     */
    public static function dataProviderGet(): array {
        return [
            'no translation'                     => [
                'default',
                null,
                ['should.not.be.translated', 'default'],
                [
                    'value' => 'text',
                ],
                null,
            ],
            'translation exists (no default)'    => [
                'translated text',
                static function (TestCase $test, string $currentLocale, string $fallbackLocale): array {
                    return [
                        $currentLocale => [
                            Package::Name.'::messages.should.be.translated' => 'translated :value',
                        ],
                    ];
                },
                'should.be.translated',
                [
                    'value' => 'text',
                ],
                null,
            ],
            'translation exists'                 => [
                'translated',
                static function (TestCase $test, string $currentLocale, string $fallbackLocale): array {
                    return [
                        $currentLocale => [
                            Package::Name.'::messages.should.be.translated' => 'translated',
                        ],
                    ];
                },
                ['should.not.be.translated', 'should.be.translated', 'default'],
                [
                    'value' => 'text',
                ],
                null,
            ],
            'translation exists (custom locale)' => [
                'translated text',
                static function (TestCase $test, string $currentLocale, string $fallbackLocale): array {
                    return [
                        'unk' => [
                            Package::Name.'::messages.should.be.translated' => 'translated :value',
                        ],
                    ];
                },
                'should.be.translated',
                [
                    'value' => 'text',
                ],
                'unk',
            ],
        ];
    }

    /**
     * @return array<string,array{string,TranslationsFactory,list<string>|string,int,array<string,string>,?string}>
     */
    public static function dataProviderChoice(): array {
        return [
            'no translation'                     => [
                'default',
                null,
                ['should.not.be.translated', 'default'],
                2,
                [
                    'value' => 'text',
                ],
                null,
            ],
            'translation exists (no default)'    => [
                'translated text',
                static function (TestCase $test, string $currentLocale, string $fallbackLocale): array {
                    return [
                        $currentLocale => [
                            Package::Name.'::messages.should.be.translated' => '{1} one |[2,*] translated :value',
                        ],
                    ];
                },
                'should.be.translated',
                2,
                [
                    'value' => 'text',
                ],
                null,
            ],
            'translation exists'                 => [
                'translated text',
                static function (TestCase $test, string $currentLocale, string $fallbackLocale): array {
                    return [
                        $currentLocale => [
                            Package::Name.'::messages.should.be.translated' => '{1} one |[2,*] translated :value',
                        ],
                    ];
                },
                ['should.not.be.translated', 'should.be.translated', 'default'],
                2,
                [
                    'value' => 'text',
                ],
                null,
            ],
            'translation exists (custom locale)' => [
                'translated text',
                static function (TestCase $test, string $currentLocale, string $fallbackLocale): array {
                    return [
                        'unk' => [
                            Package::Name.'::messages.should.be.translated' => '{1} one |[2,*] translated :value',
                        ],
                    ];
                },
                'should.be.translated',
                2,
                [
                    'value' => 'text',
                ],
                'unk',
            ],
        ];
    }
    // </editor-fold>
}
