<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Preprocessor;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * @internal
 */
#[CoversClass(Preprocess::class)]
final class PreprocessTest extends TestCase {
    public function testGetProcessedHelpInstructions(): void {
        $preprocessor = Mockery::mock(Preprocessor::class);
        $preprocessor->shouldAllowMockingProtectedMethods();
        $preprocessor
            ->shouldReceive('getInstructions')
            ->once()
            ->andReturn([
                PreprocessTest__Instruction::class,
                PreprocessTest__InstructionNoParameters::class,
                PreprocessTest__InstructionNotSerializable::class,
            ]);

        $command = new class($preprocessor) extends Preprocess {
            #[Override]
            public function getProcessedHelpInstructions(): string {
                return parent::getProcessedHelpInstructions();
            }
        };

        self::assertEquals(
            <<<'MARKDOWN'
            ### `[test:instruction]: <target> <parameters>`

            * `<target>` - Target target target target target.

              Target target target target target target target target target
              target target target target target target target target target.
            * `<parameters>` - additional parameters
              * `publicPropertyWithoutDefaultValue: int` - Description.
              * `publicPropertyWithDefaultValue: float = 123.0` - _No description provided_.
              * `publicPromotedPropertyWithoutDefaultValue: int` - Description.
              * `publicPromotedPropertyWithDefaultValue: int = 321` - Summary.

                Description description description description description
                description description description description.

            Summary summary summary.

            Description description description description description description
            description description description description description description
            description.

            ### `[test:instruction-no-parameters]: <target>`

            * `<target>` - Target target target target target.

              Target target target target target target target target target
              target target target target target target target target target.

            ### `[test:instruction-not-serializable]: <target> <parameters>`

            * `<target>` - Target target target target target.

              Target target target target target target target target target
              target target target target target target target target target.
            * `<parameters>` - additional parameters
            MARKDOWN,
            $command->getProcessedHelpInstructions(),
        );
    }

    public function testGetProcessedHelpInstructionResolver(): void {
        $preprocessor = Mockery::mock(Preprocessor::class);
        $command      = new class($preprocessor) extends Preprocess {
            #[Override]
            public function getProcessedHelpInstructionResolver(string $instruction, int $padding): string {
                return parent::getProcessedHelpInstructionResolver($instruction, $padding);
            }
        };

        self::assertEquals(
            <<<'MARKDOWN'
                Target target target target target.

                Target target target target target target target target target
                target target target target target target target target target.
            MARKDOWN,
            $command->getProcessedHelpInstructionResolver(
                PreprocessTest__Instruction::class,
                4,
            ),
        );
    }

    public function testGetProcessedHelpInstructionParameters(): void {
        $preprocessor = Mockery::mock(Preprocessor::class);
        $command      = new class($preprocessor) extends Preprocess {
            #[Override]
            public function getProcessedHelpInstructionParameters(string $instruction, int $padding): ?string {
                return parent::getProcessedHelpInstructionParameters($instruction, $padding);
            }
        };

        self::assertEquals(
            <<<'MARKDOWN'
                * `publicPropertyWithoutDefaultValue: int` - Description.
                * `publicPropertyWithDefaultValue: float = 123.0` - _No description provided_.
                * `publicPromotedPropertyWithoutDefaultValue: int` - Description.
                * `publicPromotedPropertyWithDefaultValue: int = 321` - Summary.

                    Description description description description description
                    description description description description.
            MARKDOWN,
            $command->getProcessedHelpInstructionParameters(
                PreprocessTest__Instruction::class,
                4,
            ),
        );
    }

    public function testGetProcessedHelpInstructionParametersNoParameters(): void {
        $preprocessor = Mockery::mock(Preprocessor::class);
        $command      = new class($preprocessor) extends Preprocess {
            #[Override]
            public function getProcessedHelpInstructionParameters(string $instruction, int $padding): ?string {
                return parent::getProcessedHelpInstructionParameters($instruction, $padding);
            }
        };

        self::assertNull(
            $command->getProcessedHelpInstructionParameters(
                PreprocessTest__InstructionNoParameters::class,
                4,
            ),
        );
    }

    public function testGetProcessedHelpInstructionParametersNotSerializable(): void {
        $preprocessor = Mockery::mock(Preprocessor::class);
        $command      = new class($preprocessor) extends Preprocess {
            #[Override]
            public function getProcessedHelpInstructionParameters(string $instruction, int $padding): ?string {
                return parent::getProcessedHelpInstructionParameters($instruction, $padding);
            }
        };

        self::assertEquals(
            '',
            $command->getProcessedHelpInstructionParameters(
                PreprocessTest__InstructionNotSerializable::class,
                4,
            ),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * Summary summary summary.
 *
 * Description description description description description description
 * description description description description description description
 * description.
 *
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements Instruction<string, PreprocessTest__Parameters>
 */
class PreprocessTest__Instruction implements Instruction {
    #[Override]
    public static function getName(): string {
        return 'test:instruction';
    }

    #[Override]
    public static function getResolver(): string {
        return PreprocessTest__Target::class;
    }

    #[Override]
    public static function getParameters(): ?string {
        return PreprocessTest__Parameters::class;
    }

    #[Override]
    public function process(Context $context, mixed $target, mixed $parameters): string {
        return $target;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements Instruction<string, null>
 */
class PreprocessTest__InstructionNoParameters implements Instruction {
    #[Override]
    public static function getName(): string {
        return 'test:instruction-no-parameters';
    }

    #[Override]
    public static function getResolver(): string {
        return PreprocessTest__Target::class;
    }

    #[Override]
    public static function getParameters(): ?string {
        return null;
    }

    #[Override]
    public function process(Context $context, mixed $target, mixed $parameters): string {
        return $target;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements Instruction<string, stdClass>
 */
class PreprocessTest__InstructionNotSerializable implements Instruction {
    #[Override]
    public static function getName(): string {
        return 'test:instruction-not-serializable';
    }

    #[Override]
    public static function getResolver(): string {
        return PreprocessTest__Target::class;
    }

    #[Override]
    public static function getParameters(): ?string {
        return stdClass::class;
    }

    #[Override]
    public function process(Context $context, mixed $target, mixed $parameters): string {
        return $target;
    }
}

/**
 * Target target target target target.
 *
 * Target target target target target target target target target
 * target target target target target target target target target.
 *
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements Resolver<null, string>
 */
class PreprocessTest__Target implements Resolver {
    #[Override]
    public function __invoke(Context $context, mixed $parameters): mixed {
        return $context->target;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class PreprocessTest__Parameters implements Serializable {
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
        /**
         * Summary.
         *
         * Description description description description description
         * description description description description.
         */
        public int $publicPromotedPropertyWithDefaultValue = 321,
        protected bool $protectedProperty = true,
        protected bool $privateProperty = true,
    ) {
        $this->publicPropertyWithoutDefaultValue = 0;
    }
}
