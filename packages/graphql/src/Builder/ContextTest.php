<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use LastDragon_ru\LaraASP\GraphQL\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Context::class)]
final class ContextTest extends TestCase {
    public function testContext(): void {
        $context = new Context();
        $class   = (new readonly class('origin') {
            public function __construct(
                public string $value,
            ) {
                // empty
            }
        })::class;

        self::assertFalse($context->has($class));
        self::assertNull($context->get($class));

        $overridden = $context->override([$class => new $class('overridden')]);

        self::assertNotSame($context, $overridden);
        self::assertFalse($context->has($class));
        self::assertNull($context->get($class));
        self::assertTrue($overridden->has($class));
        self::assertNotNull($overridden->get($class));
        self::assertEquals('overridden', $overridden->get($class)->value ?? null);
    }
}
