<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Types;

use Exception;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Mockery;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Schema\Directives\RenameDirective;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function config;

/**
 * @internal
 */
#[CoversClass(InputObject::class)]
final class InputObjectTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @dataProvider dataProviderIsFieldDirectiveAllowed
     *
     * @param list<class-string>|null $allowed
     */
    public function testIsFieldDirectiveAllowed(bool $expected, ?array $allowed, Directive $directive): void {
        if ($allowed !== null) {
            config([
                Package::Name.'.builder.allowed_directives' => $allowed,
            ]);
        }

        $manipulator = Mockery::mock(Manipulator::class);
        $input       = new InputObjectTest__InputObject();

        self::assertEquals($expected, $input->isFieldDirectiveAllowed($manipulator, $directive));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{bool, ?list<class-string>, Directive}>
     */
    public static function dataProviderIsFieldDirectiveAllowed(): array {
        return [
            Operator::class        => [
                true,
                [
                    // empty
                ],
                new class () implements Operator {
                    #[Override]
                    public static function definition(): string {
                        throw new Exception('Should not be called');
                    }

                    #[Override]
                    public static function getName(): string {
                        throw new Exception('Should not be called');
                    }

                    #[Override]
                    public function isBuilderSupported(string $builder): bool {
                        throw new Exception('Should not be called');
                    }

                    #[Override]
                    public function getFieldType(
                        TypeProvider $provider,
                        TypeSource $source,
                    ): string {
                        throw new Exception('Should not be called');
                    }

                    #[Override]
                    public function getFieldDescription(): string {
                        throw new Exception('Should not be called');
                    }

                    #[Override]
                    public function call(
                        Handler $handler,
                        object $builder,
                        Property $property,
                        Argument $argument,
                    ): object {
                        throw new Exception('Should not be called');
                    }
                },
            ],
            RenameDirective::class => [
                true,
                null,
                new RenameDirective(),
            ],
            'Not allowed'          => [
                false,
                [
                    // empty
                ],
                new class () implements Directive {
                    #[Override]
                    public static function definition(): string {
                        throw new Exception('Should not be called');
                    }
                },
            ],
            'Allowed'              => [
                true,
                [
                    Directive::class,
                ],
                new class () implements Directive {
                    #[Override]
                    public static function definition(): string {
                        throw new Exception('Should not be called');
                    }
                },
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
    protected function getScope(): string {
        throw new Exception('Should not be called');
    }

    #[Override]
    protected function getDescription(
        Manipulator $manipulator,
        InterfaceSource|InputSource|ObjectSource $source,
    ): string {
        throw new Exception('Should not be called');
    }

    #[Override]
    public function getTypeName(Manipulator $manipulator, BuilderInfo $builder, TypeSource $source): string {
        throw new Exception('Should not be called');
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getFieldOperator(
        Manipulator $manipulator,
        ObjectFieldSource|InputFieldSource|InterfaceFieldSource $field,
    ): ?array {
        throw new Exception('Should not be called');
    }

    #[Override]
    public function isFieldDirectiveAllowed(Manipulator $manipulator, Directive $directive): bool {
        return parent::isFieldDirectiveAllowed($manipulator, $directive);
    }
}
