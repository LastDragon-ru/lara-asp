<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use Illuminate\Container\Container;
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
        Container::getInstance()->make(DirectiveLocator::class)
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
        Container::getInstance()->make(DirectiveLocator::class)
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
        Container::getInstance()->make(DirectiveLocator::class)
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
        self::assertEquals(
            <<<'STRING'
            The schema is not valid.

            Directive "@a" argument "a" of type "Int!" is required but not provided.
            Failed asserting that false is true.
            STRING,
            $message,
        );
    }
}
