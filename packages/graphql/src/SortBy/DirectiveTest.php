<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use LastDragon_ru\LaraASP\GraphQL\Testing\TestCase;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\GraphQL\SortBy\Directive
 */
class DirectiveTest extends TestCase {
    use WithTestData;

    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::manipulateArgDefinition
     *
     * @dataProvider dataProviderManipulateArgDefinition
     */
    public function testManipulateArgDefinition(string $expected, string $graphql): void {
        $expected = $this->getTestData()->content($expected);
        $actual   = $this->getSchema($this->getTestData()->content($graphql));

        $this->assertEquals($expected, $actual);
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
