<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\ParameterizableInstruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Preprocessor;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Preprocess::class)]
final class PreprocessTest extends TestCase {
    public function testGetInstructionParameters(): void {
        $preprocessor = Mockery::mock(Preprocessor::class);
        $command      = new class($preprocessor) extends Preprocess {
            /**
             * @inheritDoc
             */
            #[Override]
            public function getInstructionParameters(string $instruction): array {
                return parent::getInstructionParameters($instruction);
            }
        };

        self::assertEquals(
            [
                'publicPropertyWithoutDefaultValue: int'            => 'Description.',
                'publicPropertyWithDefaultValue: float = 123.0'     => '_No description provided_.',
                'publicPromotedPropertyWithoutDefaultValue: int'    => 'Description.',
                'publicPromotedPropertyWithDefaultValue: int = 321' => '_No description provided_.',
            ],
            $command->getInstructionParameters(
                PreprocessTest__ParameterizableInstruction::class,
            ),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements ParameterizableInstruction<PreprocessTest__ParameterizableInstructionParameters>
 */
class PreprocessTest__ParameterizableInstruction implements ParameterizableInstruction {
    #[Override]
    public static function getName(): string {
        return 'test:parameterizable-instruction';
    }

    #[Override]
    public static function getDescription(): string {
        return 'Description description description description description.';
    }

    #[Override]
    public static function getTargetDescription(): ?string {
        return 'Target target target target target.';
    }

    #[Override]
    public static function getParameters(): string {
        return PreprocessTest__ParameterizableInstructionParameters::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getParametersDescription(): array {
        return [];
    }

    #[Override]
    public function process(string $path, string $target, Serializable $parameters): string {
        return $path;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class PreprocessTest__ParameterizableInstructionParameters implements Serializable {
    public static bool $publicStaticProperty = true;

    /**
     * Description.
     */
    public int   $publicPropertyWithoutDefaultValue;
    public float $publicPropertyWithDefaultValue = 123;

    public function __construct(
        /**
         * Description.
         */
        public int $publicPromotedPropertyWithoutDefaultValue,
        public int $publicPromotedPropertyWithDefaultValue = 321,
        protected bool $protectedProperty = true,
        protected bool $privateProperty = true,
    ) {
        $this->publicPropertyWithoutDefaultValue = 0;
    }
}
