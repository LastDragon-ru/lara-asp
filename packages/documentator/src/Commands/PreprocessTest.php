<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Commands;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Task;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Preprocess::class)]
final class PreprocessTest extends TestCase {
    public function testGetProcessedHelpTaskPreprocessInstructions(): void {
        $task = Mockery::mock(Task::class);
        $task->shouldAllowMockingProtectedMethods();
        $task
            ->shouldReceive('getInstructions')
            ->once()
            ->andReturn([
                PreprocessTest__Instruction::class,
                PreprocessTest__InstructionNoParameters::class,
            ]);

        $container = $this->app()->make(ContainerResolver::class);
        $command   = new class($container) extends Preprocess {
            #[Override]
            public function getProcessedHelpTaskPreprocessInstructions(Task $task, int $level): string {
                return parent::getProcessedHelpTaskPreprocessInstructions($task, $level);
            }
        };

        $command->setLaravel($this->app());

        self::assertSame(
            <<<'MARKDOWN'
            ### `[test:instruction]: <target> <parameters>`

            * `<target>` - Target target target target target.

              Target target target target target target target target target
              target target target target target target target target target.
            * `<parameters>` - additional parameters
              * `publicPromotedPropertyWithDefaultValue`: `int` = `321` - Summary.

                Description description description description description
                description description description description.
              * `publicPromotedPropertyWithoutDefaultValue`: `int` - Description.
              * `publicPropertyEnumWithDefaultValue`: `LastDragon_ru\LaraASP\Documentator\Commands\PreprocessTest__Enum` = `LastDragon_ru\LaraASP\Documentator\Commands\PreprocessTest__Enum::B` - _No description provided_.
              * `publicPropertyWithDefaultValue`: `float` = `123.0` - _No description provided_.
              * `publicPropertyWithoutDefaultValue`: `int` - Description.

            Summary summary summary.

            Description description description description description description
            description description description description description description
            description.

            ### `[test:instruction-no-parameters]: <target>`

            * `<target>` - Target target target target target.

              Target target target target target target target target target
              target target target target target target target target target.
            MARKDOWN,
            $command->getProcessedHelpTaskPreprocessInstructions($task, 3),
        );
    }

    public function testGetProcessedHelpTaskPreprocessInstructionTarget(): void {
        $container = $this->app()->make(ContainerResolver::class);
        $command   = new class($container) extends Preprocess {
            #[Override]
            public function getProcessedHelpTaskPreprocessInstructionTarget(
                string $instruction,
                string $target,
                int $padding,
            ): ?string {
                return parent::getProcessedHelpTaskPreprocessInstructionTarget($instruction, $target, $padding);
            }
        };

        $command->setLaravel($this->app());

        self::assertSame(
            <<<'MARKDOWN'
            Target target target target target.

                Target target target target target target target target target
                target target target target target target target target target.
            MARKDOWN,
            $command->getProcessedHelpTaskPreprocessInstructionTarget(
                PreprocessTest__Instruction::class,
                'target',
                4,
            ),
        );
    }

    public function testGetProcessedHelpTaskPreprocessParameters(): void {
        $container = $this->app()->make(ContainerResolver::class);
        $command   = new class($container) extends Preprocess {
            #[Override]
            public function getProcessedHelpTaskPreprocessParameters(
                string $instruction,
                string $target,
                int $padding,
            ): ?string {
                return parent::getProcessedHelpTaskPreprocessParameters($instruction, $target, $padding);
            }
        };

        $command->setLaravel($this->app());

        self::assertSame(
            <<<'MARKDOWN'
                * `publicPromotedPropertyWithDefaultValue`: `int` = `321` - Summary.

                    Description description description description description
                    description description description description.
                * `publicPromotedPropertyWithoutDefaultValue`: `int` - Description.
                * `publicPropertyEnumWithDefaultValue`: `LastDragon_ru\LaraASP\Documentator\Commands\PreprocessTest__Enum` = `LastDragon_ru\LaraASP\Documentator\Commands\PreprocessTest__Enum::B` - _No description provided_.
                * `publicPropertyWithDefaultValue`: `float` = `123.0` - _No description provided_.
                * `publicPropertyWithoutDefaultValue`: `int` - Description.
            MARKDOWN,
            $command->getProcessedHelpTaskPreprocessParameters(
                PreprocessTest__Instruction::class,
                'target',
                4,
            ),
        );
    }

    public function testGetProcessedHelpTaskPreprocessParametersNoParameters(): void {
        $container = $this->app()->make(ContainerResolver::class);
        $command   = new class($container) extends Preprocess {
            #[Override]
            public function getProcessedHelpTaskPreprocessParameters(
                string $instruction,
                string $target,
                int $padding,
            ): ?string {
                return parent::getProcessedHelpTaskPreprocessParameters($instruction, $target, $padding);
            }
        };

        self::assertNull(
            $command->getProcessedHelpTaskPreprocessParameters(
                PreprocessTest__InstructionNoParameters::class,
                'target',
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
 * @implements Instruction<PreprocessTest__Parameters>
 */
class PreprocessTest__Instruction implements Instruction {
    #[Override]
    public static function getName(): string {
        return 'test:instruction';
    }

    #[Override]
    public static function getPriority(): ?int {
        return null;
    }

    #[Override]
    public static function getParameters(): string {
        return PreprocessTest__Parameters::class;
    }

    #[Override]
    public function __invoke(Context $context, Parameters $parameters): string {
        return $parameters->target;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 *
 * @implements Instruction<PreprocessTest__ParametersEmpty>
 */
class PreprocessTest__InstructionNoParameters implements Instruction {
    #[Override]
    public static function getName(): string {
        return 'test:instruction-no-parameters';
    }

    #[Override]
    public static function getPriority(): ?int {
        return null;
    }

    #[Override]
    public static function getParameters(): string {
        return PreprocessTest__ParametersEmpty::class;
    }

    #[Override]
    public function __invoke(Context $context, Parameters $parameters): string {
        return $parameters->target;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class PreprocessTest__Parameters implements Parameters {
    public static bool $publicStaticProperty = true;

    /**
     * Description.
     */
    public int                  $publicPropertyWithoutDefaultValue;
    public float                $publicPropertyWithDefaultValue     = 123;
    public PreprocessTest__Enum $publicPropertyEnumWithDefaultValue = PreprocessTest__Enum::B;

    public function __construct(
        /**
         * Target target target target target.
         *
         * Target target target target target target target target target
         * target target target target target target target target target.
         */
        public readonly string $target,
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

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
readonly class PreprocessTest__ParametersEmpty implements Parameters {
    public function __construct(
        /**
         * Target target target target target.
         *
         * Target target target target target target target target target
         * target target target target target target target target target.
         */
        public string $target,
    ) {
        // empty
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
enum PreprocessTest__Enum {
    case A;
    case B;
}
