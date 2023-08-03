<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Directives;

use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\BuilderUnknown;
use LastDragon_ru\LaraASP\GraphQL\Exceptions\ArgumentAlreadyDefined;
use LastDragon_ru\LaraASP\GraphQL\Stream\Definitions\StreamDirective;
use LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\FailedToCreateStreamFieldIsNotList;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Directive::class)]
class DirectiveTest extends TestCase {
    public function testManipulateFieldDefinition(): void {
        $directives = $this->app->make(DirectiveLocator::class);

        $directives->setResolved('stream', StreamDirective::class);

        $this->useGraphQLSchema(self::getTestData()->file('~schema.graphql'));
        $this->assertGraphQLSchemaEquals(
            self::getTestData()->file('~expected.graphql'),
        );
    }

    public function testManipulateFieldDefinitionBuilderUnknown(): void {
        self::expectException(BuilderUnknown::class);
        self::expectExceptionMessage('Impossible to determine builder type for `type Query { field }`.');

        $directives = $this->app->make(DirectiveLocator::class);
        $directive  = new class() extends StreamDirective {
            public function getBuilderInfo(TypeSource $source): ?BuilderInfo {
                return null;
            }
        };

        $directives->setResolved('stream', $directive::class);

        $this->useGraphQLSchema(
            <<<'GraphQL'
            type Query {
                field: [Test] @stream(searchable: false, sortable: false)
            }

            type Test {
                id: ID!
            }
            GraphQL,
        );
    }

    public function testManipulateFieldDefinitionFieldIsNotList(): void {
        self::expectException(FailedToCreateStreamFieldIsNotList::class);
        self::expectExceptionMessage('Impossible to create stream for `type Test { field }` because it is not a list.');

        $directives = $this->app->make(DirectiveLocator::class);

        $directives->setResolved('stream', StreamDirective::class);

        $this->useGraphQLSchema(
            <<<'GraphQL'
            type Query {
                field: Test
            }

            type Test {
                field: Int @stream(searchable: false, sortable: false)
            }
            GraphQL,
        );
    }

    public function testManipulateFieldDefinitionArgumentAlreadyDefined(): void {
        self::expectException(ArgumentAlreadyDefined::class);
        self::expectExceptionMessage('Argument `type Test { field(where) }` already defined.');

        $directives = $this->app->make(DirectiveLocator::class);

        $directives->setResolved('stream', StreamDirective::class);

        $this->useGraphQLSchema(
            <<<'GraphQL'
            type Query {
                field: Test
            }

            type Test {
                field(where: Int): [Test] @stream
            }
            GraphQL,
        );
    }
}
