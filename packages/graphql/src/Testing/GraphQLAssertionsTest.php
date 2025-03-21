<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use LastDragon_ru\LaraASP\GraphQL\Testing\Package\Directives\TestDirective;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @internal
 */
#[CoversClass(GraphQLAssertions::class)]
final class GraphQLAssertionsTest extends TestCase {
    public function testAssertGraphQLExportableEquals(): void {
        $this->app()->make(DirectiveLocator::class)
            ->setResolved('a', TestDirective::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            directive @a(b: B) on OBJECT

            type Query {
                a: A
            }

            type A @a {
                id: ID!
            }

            input B {
                b: String!
            }
            GRAPHQL,
        );

        $type     = $this->getGraphQLSchema()->getType('A');
        $expected = <<<'GRAPHQL'
            type A
            @a
            {
                id: ID!
            }

            directive @a(
                b: B
            )
            on
                | OBJECT

            input B {
                b: String!
            }

            GRAPHQL;

        self::assertNotNull($type);

        $this->assertGraphQLExportableEquals(
            $expected,
            $type,
        );
    }

    public function testAssertGraphQLPrintableEquals(): void {
        $this->app()->make(DirectiveLocator::class)
            ->setResolved('a', TestDirective::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            directive @a(b: B) on OBJECT

            type Query {
                a: A
            }

            type A @a {
                id: ID!
            }

            input B {
                b: String!
            }
            GRAPHQL,
        );

        $type     = $this->getGraphQLSchema()->getType('A');
        $expected = <<<'GRAPHQL'
            type A
            @a
            {
                id: ID!
            }
            GRAPHQL;

        self::assertNotNull($type);

        $this->assertGraphQLPrintableEquals(
            $expected,
            $type,
        );
    }

    public function testAssertGraphQLSchemaValid(): void {
        // Prepare
        $this->app()->make(DirectiveLocator::class)
            ->setResolved('a', TestDirective::class)
            ->setResolved('test', TestDirective::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            directive @a(a: Int!) on OBJECT

            type Query @a {
                a: String @test
            }
            GRAPHQL,
        );

        // Test
        $valid   = true;
        $message = null;

        try {
            $this->assertGraphQLSchemaValid();
        } catch (ExpectationFailedException $exception) {
            $valid   = false;
            $message = $exception->getMessage();
        }

        self::assertFalse($valid);
        self::assertSame(
            <<<'STRING'
            The schema is not valid.

            Directive "@a" argument "a" of type "Int!" is required but not provided.
            Failed asserting that false is true.
            STRING,
            $message,
        );
    }

    public function testAssertGraphQLSchemaNoBreakingChanges(): void {
        // Prepare
        $this->app()->make(DirectiveLocator::class)
            ->setResolved('a', TestDirective::class)
            ->setResolved('test', TestDirective::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            directive @a(a: Boolean) on OBJECT

            type Query @a {
                a: String @test
            }
            GRAPHQL,
        );

        // Test
        $valid   = true;
        $message = null;

        try {
            $this->assertGraphQLSchemaNoBreakingChanges(
                <<<'GRAPHQL'
                directive @a(a: Int!) on OBJECT
                directive @test on FIELD_DEFINITION

                type Query @a(a: "value") {
                    a: Int! @test
                    b: String
                }
                GRAPHQL,
            );
        } catch (ExpectationFailedException $exception) {
            $valid   = false;
            $message = $exception->getMessage();
        }

        self::assertFalse($valid);

        // todo(graphql-php): Strange breaking changes:
        //      - `@a(a)` type was changed but not detected
        //      - `Int was removed` false positive

        self::assertSame(
            <<<'STRING'
            The breaking changes found!

            FIELD_CHANGED_KIND:

            * Query.a changed type from Int! to String.

            FIELD_REMOVED:

            * Query.b was removed.

            TYPE_REMOVED:

            * Int was removed.

            Failed asserting that false is true.
            STRING,
            $message,
        );
    }

    public function testAssertGraphQLSchemaNoDangerousChanges(): void {
        // Prepare
        $this->app()->make(DirectiveLocator::class)
            ->setResolved('a', TestDirective::class)
            ->setResolved('test', TestDirective::class);

        $this->useGraphQLSchema(
            <<<'GRAPHQL'
            directive @a(a: Boolean = true) on OBJECT

            type Query @a {
                a(a: Int = 321): Int! @test
            }
            GRAPHQL,
        );

        // Test
        $valid   = true;
        $message = null;

        try {
            $this->assertGraphQLSchemaNoDangerousChanges(
                <<<'GRAPHQL'
                directive @a(a: Boolean = false) on OBJECT
                directive @test on FIELD_DEFINITION

                type Query @a(a: "value") {
                    a(a: Int = 123): Int! @test
                }
                GRAPHQL,
            );
        } catch (ExpectationFailedException $exception) {
            $valid   = false;
            $message = $exception->getMessage();
        }

        self::assertFalse($valid);

        // todo(graphql-php): Strange breaking changes:
        //      - `@a(a)` default value was changed but not detected

        self::assertSame(
            <<<'STRING'
            The dangerous changes found!

            ARG_DEFAULT_VALUE_CHANGE:

            * Query.a arg a has changed defaultValue

            Failed asserting that false is true.
            STRING,
            $message,
        );
    }
}
