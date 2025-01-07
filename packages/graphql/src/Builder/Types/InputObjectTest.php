<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Types;

use Exception;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Context\HandlerContextImplicit;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context as ContextContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use LastDragon_ru\LaraASP\GraphQL\Config\Config;
use LastDragon_ru\LaraASP\GraphQL\PackageConfig;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Directives\TestDirective;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Schema\Directives\RenameDirective;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(InputObject::class)]
final class InputObjectTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param list<class-string>|null $allowed
     */
    #[DataProvider('dataProviderIsFieldDirectiveAllowed')]
    public function testIsFieldDirectiveAllowed(
        bool $expected,
        ?array $allowed,
        Directive $directive,
        ContextContract $context,
    ): void {
        if ($allowed !== null) {
            $this->setConfiguration(PackageConfig::class, static function (Config $config) use ($allowed): void {
                $config->builder->allowedDirectives = $allowed;
            });
        }

        $manipulator = Mockery::mock(Manipulator::class);
        $field       = Mockery::mock(ObjectFieldSource::class);
        $input       = new InputObjectTest__InputObject(
            $this->app()->make(PackageConfig::class),
        );

        self::assertSame($expected, $input->isFieldDirectiveAllowed($manipulator, $field, $context, $directive));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{bool, ?list<class-string>, Directive}>
     */
    public static function dataProviderIsFieldDirectiveAllowed(): array {
        return [
            Operator::class                            => [
                true,
                [
                    // empty
                ],
                new class () extends InputObjectTest__OperatorImpl implements InputObjectTest__Operator {
                    // empty
                },
                (new Context())->override([
                    HandlerContextImplicit::class => new HandlerContextImplicit(true),
                ]),
            ],
            RenameDirective::class                     => [
                true,
                null,
                new RenameDirective(),
                (new Context())->override([
                    HandlerContextImplicit::class => new HandlerContextImplicit(true),
                ]),
            ],
            'Not allowed'                              => [
                false,
                [
                    // empty
                ],
                new TestDirective(),
                (new Context())->override([
                    HandlerContextImplicit::class => new HandlerContextImplicit(true),
                ]),
            ],
            'Allowed'                                  => [
                true,
                [
                    Directive::class,
                ],
                new TestDirective(),
                (new Context())->override([
                    HandlerContextImplicit::class => new HandlerContextImplicit(true),
                ]),
            ],
            'Not allowed (explicit)'                   => [
                true,
                [
                    // empty
                ],
                new TestDirective(),
                (new Context())->override([
                    HandlerContextImplicit::class => new HandlerContextImplicit(false),
                ]),
            ],
            'Operator of another directive (explicit)' => [
                false,
                [
                    // empty
                ],
                new class () extends InputObjectTest__OperatorImpl {
                    // empty
                },
                (new Context())->override([
                    HandlerContextImplicit::class => new HandlerContextImplicit(false),
                ]),
            ],
        ];
    }
    //</editor-fold>
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class InputObjectTest__InputObject extends InputObject {
    #[Override]
    protected function getDescription(
        Manipulator $manipulator,
        InterfaceSource|InputSource|ObjectSource $source,
        ContextContract $context,
    ): string {
        throw new Exception('Should not be called');
    }

    #[Override]
    public function getTypeName(TypeSource $source, ContextContract $context): string {
        throw new Exception('Should not be called');
    }

    #[Override]
    protected function getFieldMarkerOperator(): string {
        return InputObjectTest__Operator::class;
    }

    #[Override]
    public function isFieldDirectiveAllowed(
        Manipulator $manipulator,
        ObjectFieldSource|InputFieldSource|InterfaceFieldSource $field,
        ContextContract $context,
        Directive $directive,
    ): bool {
        return parent::isFieldDirectiveAllowed(
            $manipulator,
            $field,
            $context,
            $directive,
        );
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
interface InputObjectTest__Operator extends Operator {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class InputObjectTest__OperatorImpl implements Operator {
    #[Override]
    public static function definition(): string {
        throw new Exception('Should not be called');
    }

    #[Override]
    public static function getName(): string {
        throw new Exception('Should not be called');
    }

    #[Override]
    public function isAvailable(
        TypeProvider $provider,
        TypeSource $source,
        ContextContract $context,
    ): bool {
        throw new Exception('Should not be called');
    }

    #[Override]
    public function getFieldType(
        TypeProvider $provider,
        TypeSource $source,
        ContextContract $context,
    ): ?string {
        throw new Exception('Should not be called');
    }

    #[Override]
    public function getFieldDescription(): ?string {
        throw new Exception('Should not be called');
    }

    #[Override]
    public function call(
        Handler $handler,
        object $builder,
        Field $field,
        Argument $argument,
        ContextContract $context,
    ): object {
        throw new Exception('Should not be called');
    }
}
