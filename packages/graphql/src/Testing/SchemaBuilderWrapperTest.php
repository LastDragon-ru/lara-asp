<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use ReflectionClass;
use ReflectionMethod;

/**
 * @internal
 * @covers \LastDragon_ru\LaraASP\GraphQL\Testing\SchemaBuilderWrapper
 */
class SchemaBuilderWrapperTest extends TestCase {
    public function testWrappedSuccessfully(): void {
        $missed  = [];
        $origin  = new ReflectionClass(SchemaBuilder::class);
        $methods = $origin->getMethods(ReflectionMethod::IS_PUBLIC);
        $wrapper = new ReflectionClass(SchemaBuilderWrapper::class);

        foreach ($methods as $method) {
            if ($method->isConstructor()) {
                continue;
            }

            $wrapped = $wrapper->getMethod($method->name);

            if ($wrapped->getDeclaringClass()->getName() !== $wrapper->getName()) {
                $missed[] = $method->name;
            }
        }

        self::assertEquals([], $missed, 'Some methods are not wrapped.');
    }
}
