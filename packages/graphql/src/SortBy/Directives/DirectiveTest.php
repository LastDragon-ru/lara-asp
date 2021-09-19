<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Schema\TypeRegistry;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive
 */
class DirectiveTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::manipulateArgDefinition
     *
     * @dataProvider dataProviderManipulateArgDefinition
     */
    public function testManipulateArgDefinition(string $expected, string $graphql): void {
        $this->assertGraphQLSchemaEquals(
            $this->getTestData()->file($expected),
            $this->getTestData()->file($graphql),
        );
    }

    /**
     * @covers ::manipulateArgDefinition
     */
    public function testManipulateArgDefinitionTypeRegistry(): void {
        $a = new InputObjectType([
            'name'   => 'A',
            'fields' => [
                [
                    'name' => 'name',
                    'type' => Type::string(),
                ],
                [
                    'name' => 'flag',
                    'type' => Type::boolean(),
                ],
            ],
        ]);
        $b = new InputObjectType([
            'name'   => 'B',
            'fields' => [
                [
                    'name' => 'name',
                    'type' => Type::string(),
                ],
                [
                    'name' => 'child',
                    'type' => $a,
                ],
            ],
        ]);

        $registry = $this->app->make(TypeRegistry::class);
        $registry->register($a);
        $registry->register($b);

        $this->assertGraphQLSchemaEquals(
            $this->getTestData()->file('~registry-expected.graphql'),
            $this->getTestData()->file('~registry.graphql'),
        );
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<mixed>
     */
    public function dataProviderManipulateArgDefinition(): array {
        return [
            'full' => ['~full-expected.graphql', '~full.graphql'],
        ];
    }
    // </editor-fold>
}
